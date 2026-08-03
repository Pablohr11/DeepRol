(function () {
    "use strict";

    var root = document.querySelector("[data-game-room]");
    var bootstrapNode = document.getElementById("gameBootstrap");
    if (!root || !bootstrapNode) {
        return;
    }

    var config = JSON.parse(bootstrapNode.textContent);
    var state = config.state;
    var presence = [];
    var socket = null;
    var reconnectTimer = null;
    var reconnectAttempt = 0;
    var heartbeatTimer = null;
    var pending = {};
    var selectedCombatantId = null;
    var historyFilter = "all";
    var rosterSyncStarted = false;
    var roomMessage = document.querySelector("[data-room-message]");
    var socketStatus = document.querySelector("[data-socket-status]");
    var combatantDialog = document.getElementById("combatantDialog");
    var dmToolsDialog = document.getElementById("dmToolsDialog");

    function escapeHtml(value) {
        return String(value == null ? "" : value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function requestId() {
        return "r" + Date.now().toString(36)
            + Math.random().toString(36).slice(2, 9);
    }

    function numberValue(value, fallback) {
        var parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function showMessage(text, isError) {
        roomMessage.textContent = text;
        roomMessage.hidden = !text;
        roomMessage.classList.toggle("error", Boolean(isError));
        if (text && !isError) {
            window.setTimeout(function () {
                if (roomMessage.textContent === text) {
                    roomMessage.hidden = true;
                }
            }, 2600);
        }
    }

    function setSocketStatus(mode, text) {
        socketStatus.classList.remove("isOnline", "isOffline", "isConnecting");
        socketStatus.classList.add(mode);
        socketStatus.querySelector("span").textContent = text;
    }

    function canControl(combatant) {
        return state.viewer.role === "dm"
            || Number(combatant.owner_user_id) === Number(state.viewer.id_user);
    }

    function currentCombatant() {
        if (!state.encounter || !state.combatants.length) {
            return null;
        }
        var index = Math.min(
            numberValue(state.encounter.current_turn_index, 0),
            state.combatants.length - 1
        );
        return state.combatants[index] || null;
    }

    function renderState() {
        document.querySelector("[data-game-name]").textContent = state.game.name;
        renderParty();
        renderTurnBanner();
        renderCombatants();
        renderHistory();
        refreshEntityOptions();
        if (
            selectedCombatantId !== null
            && combatantDialog
            && combatantDialog.open
        ) {
            renderCombatantDialog(selectedCombatantId);
        }
    }

    function renderParty() {
        var onlineIds = presence.map(function (entry) {
            return Number(entry.id_user);
        });
        var html = state.members.map(function (member) {
            var online = onlineIds.indexOf(Number(member.id_user)) !== -1;
            var name = member.character_name || member.display_name || member.username;
            var role = member.role === "dm"
                ? "Dungeon Master"
                : (member.character_name
                    ? (member.clase || "Aventurero") + " " + (member.nivel || "")
                    : "Jugador");
            var initial = String(name || "?").charAt(0).toUpperCase();
            return "<article class=\"partyMember" + (online ? " isOnline" : "") + "\">"
                + "<span class=\"partyAvatar\">" + escapeHtml(initial) + "<i></i></span>"
                + "<span><strong>" + escapeHtml(name) + "</strong><small>"
                + escapeHtml(role) + "</small></span></article>";
        }).join("");
        document.querySelector("[data-party-members]").innerHTML = html;
        document.querySelector("[data-member-count]").textContent =
            state.members.length + (state.members.length === 1
                ? " participante"
                : " participantes");
    }

    function renderTurnBanner() {
        var round = document.querySelector("[data-round]");
        var title = document.querySelector("[data-current-turn]");
        var kicker = document.querySelector("[data-turn-kicker]");
        var detail = document.querySelector("[data-turn-detail]");
        var statusButton = document.querySelector("[data-encounter-status]");
        var nextButton = document.querySelector("[data-next-turn]");
        var encounterState = document.querySelector("[data-encounter-state]");
        var encounter = state.encounter;
        var active = currentCombatant();

        if (!encounter) {
            round.textContent = "—";
            kicker.textContent = "Encuentro";
            title.textContent = "Prepara el primer combate";
            detail.textContent = state.viewer.role === "dm"
                ? "Abre tus herramientas para crear un encuentro."
                : "El Dungeon Master está preparando la escena.";
            encounterState.textContent = "Sin encuentro";
            statusButton.hidden = true;
            nextButton.hidden = true;
            return;
        }

        round.textContent = encounter.round_number || 1;
        encounterState.textContent = encounter.name + " · "
            + statusLabel(encounter.status);
        kicker.textContent = encounter.status === "active"
            ? "Turno actual"
            : statusLabel(encounter.status);
        title.textContent = active
            ? active.name
            : (state.combatants.length
                ? "Iniciativas pendientes"
                : "Añade combatientes");
        detail.textContent = active
            ? hpText(active) + (active.concentrating_on
                ? " · Concentración: " + active.concentrating_on
                : "")
            : "El orden se calcula de mayor a menor iniciativa.";

        if (state.viewer.role === "dm") {
            statusButton.hidden = false;
            if (encounter.status === "setup") {
                statusButton.textContent = "Iniciar combate";
                statusButton.dataset.nextStatus = "active";
            } else if (encounter.status === "active") {
                statusButton.textContent = "Finalizar encuentro";
                statusButton.dataset.nextStatus = "finished";
            } else {
                statusButton.textContent = "Volver a preparación";
                statusButton.dataset.nextStatus = "setup";
            }
            nextButton.hidden = encounter.status !== "active"
                || state.combatants.length === 0;
        } else {
            statusButton.hidden = true;
            nextButton.hidden = true;
        }
    }

    function renderCombatants() {
        var list = document.querySelector("[data-initiative-list]");
        if (!state.encounter) {
            list.innerHTML = "<div class=\"emptyInitiative\"><span>✦</span>"
                + "<h3>No hay un encuentro preparado</h3>"
                + "<p>Los encuentros conservan el orden, los puntos de golpe y todos los gastos.</p></div>";
            return;
        }
        if (!state.combatants.length) {
            list.innerHTML = "<div class=\"emptyInitiative\"><span>＋</span>"
                + "<h3>El orden está vacío</h3><p>"
                + (state.viewer.role === "dm"
                    ? "Añade personajes, NPC o enemigos desde tus herramientas."
                    : "El Dungeon Master todavía no ha añadido combatientes.")
                + "</p></div>";
            return;
        }

        var currentIndex = numberValue(state.encounter.current_turn_index, 0);
        list.innerHTML = state.combatants.map(function (combatant, index) {
            var maximum = Math.max(1, numberValue(combatant.max_hp, 1));
            var current = Math.max(0, numberValue(combatant.current_hp, 0));
            var percent = Math.max(0, Math.min(100, current / maximum * 100));
            var conditions = (combatant.conditions || []).map(function (condition) {
                return "<span>" + escapeHtml(condition) + "</span>";
            }).join("");
            var resources = (combatant.resources || []).slice(0, 3).map(function (resource) {
                return "<span>" + escapeHtml(resource.name) + " "
                    + escapeHtml(resource.current) + "/"
                    + escapeHtml(resource.maximum) + "</span>";
            }).join("");
            var kind = combatant.entity_type === "character"
                ? "Personaje"
                : (combatant.entity_type === "monster" ? "Bestiario" : "NPC");
            var initiative = combatant.initiative === null
                ? "—"
                : Number(combatant.initiative).toLocaleString("es-ES", {
                    maximumFractionDigits: 2
                });
            var canManage = canControl(combatant);
            return "<article class=\"initiativeCard"
                + (index === currentIndex && state.encounter.status === "active"
                    ? " isCurrent"
                    : "")
                + (current <= 0 ? " isDown" : "")
                + "\" data-combatant-id=\"" + Number(combatant.id_combatant) + "\">"
                + "<button class=\"initiativeRank\" type=\"button\" "
                + (state.viewer.role === "dm" ? "data-set-turn=\"" + index + "\"" : "disabled")
                + "><span>" + (index + 1) + "</span><strong>" + escapeHtml(initiative)
                + "</strong><small>iniciativa</small></button>"
                + "<div class=\"combatantCore\"><div class=\"combatantTopline\"><div>"
                + "<span class=\"combatantKind\">" + escapeHtml(kind) + "</span>"
                + "<h3>" + escapeHtml(combatant.name) + "</h3></div>"
                + "<span class=\"armorBadge\">CA <strong>"
                + escapeHtml(combatant.armor_class) + "</strong></span></div>"
                + "<div class=\"hpLine\"><div><span>Vida</span><strong>"
                + current + " / " + maximum
                + (Number(combatant.temp_hp) > 0
                    ? " <em>+" + Number(combatant.temp_hp) + " temp.</em>"
                    : "")
                + "</strong></div><div class=\"hpTrack\"><i style=\"width:"
                + percent.toFixed(2) + "%\"></i></div></div>"
                + ((conditions || combatant.concentrating_on)
                    ? "<div class=\"combatantTags\">"
                        + (combatant.concentrating_on
                            ? "<span class=\"concentrationTag\">Concentración · "
                                + escapeHtml(combatant.concentrating_on) + "</span>"
                            : "")
                        + conditions + "</div>"
                    : "")
                + (resources
                    ? "<div class=\"resourcePreview\">" + resources + "</div>"
                    : "")
                + "</div><div class=\"combatantActions\">"
                + (canManage
                    ? "<button type=\"button\" data-manage-combatant=\""
                        + Number(combatant.id_combatant) + "\">Gestionar</button>"
                    : "<span>Controlado por el jugador</span>")
                + "</div></article>";
        }).join("");
    }

    function renderHistory() {
        var events = state.events.slice().reverse().filter(function (event) {
            if (historyFilter === "spells") {
                return event.event_type === "spell.cast"
                    || event.event_type.indexOf("custom_spell.") === 0;
            }
            if (historyFilter === "resources") {
                return event.event_type.indexOf("resource.") === 0;
            }
            return true;
        });
        var feed = document.querySelector("[data-activity-feed]");
        if (!events.length) {
            feed.innerHTML = "<li class=\"emptyHistory\">Todavía no hay acciones en este registro.</li>";
            return;
        }
        feed.innerHTML = events.map(function (event) {
            var description = eventDescription(event);
            var actor = event.actor_name || event.payload.actor_name || "Sistema";
            var date = new Date(String(event.created_at).replace(" ", "T"));
            var time = Number.isNaN(date.getTime())
                ? ""
                : date.toLocaleTimeString("es-ES", {
                    hour: "2-digit",
                    minute: "2-digit"
                });
            var icon = event.event_type === "spell.cast"
                ? "✦"
                : (event.event_type.indexOf("resource.") === 0 ? "◇" : "•");
            return "<li><span class=\"eventIcon\">" + icon + "</span><div><p>"
                + description + "</p><small>" + escapeHtml(actor)
                + (time ? " · " + escapeHtml(time) : "") + "</small></div></li>";
        }).join("");
    }

    function eventDescription(event) {
        var p = event.payload || {};
        var names = {
            "game.created": "creó la partida",
            "member.joined": "se unió a la mesa",
            "member.updated": "actualizó su personaje",
            "encounter.created": "preparó «" + (p.encounter_name || "un encuentro") + "»",
            "encounter.active": "inició el encuentro",
            "encounter.finished": "finalizó el encuentro",
            "encounter.setup": "devolvió el encuentro a preparación",
            "encounter.roster_synced": Number(p.added_count) > 0
                ? "incorporó " + Number(p.added_count) + " personajes del grupo"
                : "comprobó los personajes del grupo",
            "combatant.added": "incorporó a " + (p.combatant_name || "un combatiente"),
            "combatant.removed": "retiró a " + (p.combatant_name || "un combatiente"),
            "combatant.initiative_set": "fijó la iniciativa de "
                + (p.combatant_name || "un combatiente") + " en " + (p.initiative || 0),
            "combatant.damaged": (p.combatant_name || "Un combatiente")
                + " recibió " + (p.amount || 0) + " de daño",
            "combatant.healed": (p.combatant_name || "Un combatiente")
                + " recuperó " + (p.amount || 0) + " de vida",
            "combatant.hp_set": "ajustó la vida de " + (p.combatant_name || "un combatiente"),
            "combatant.temp_hp_set": (p.combatant_name || "Un combatiente")
                + " obtuvo " + (p.amount || 0) + " PG temporales",
            "combatant.condition_added": (p.combatant_name || "Un combatiente")
                + " recibió el estado " + (p.condition || ""),
            "combatant.condition_removed": (p.combatant_name || "Un combatiente")
                + " dejó atrás el estado " + (p.condition || ""),
            "concentration.started": (p.combatant_name || "Un combatiente")
                + " se concentra en " + (p.spell_name || "un efecto"),
            "concentration.ended": (p.combatant_name || "Un combatiente")
                + " perdió la concentración",
            "turn.advanced": "avanzó el turno a " + (p.combatant_name || "el siguiente combatiente"),
            "turn.selected": "seleccionó el turno de " + (p.combatant_name || "un combatiente"),
            "resource.defined": "añadió el recurso "
                + ((p.resource && p.resource.name) || ""),
            "resource.spent": "gastó "
                + ((p.resource && p.resource.name) || "un recurso"),
            "resource.restored": "recuperó "
                + ((p.resource && p.resource.name) || "un recurso"),
            "spell.cast": (p.combatant_name || "Un combatiente")
                + " lanzó " + (p.spell_name || "un conjuro")
                + (Number(p.slot_level) > 0 ? " a nivel " + p.slot_level : ""),
            "custom_spell.created": "añadió el conjuro personalizado "
                + (p.spell_name || ""),
            "npc.created": "creó al NPC " + (p.npc_name || "")
        };
        return escapeHtml(names[event.event_type] || event.event_type.replace(/[._]/g, " "));
    }

    function statusLabel(status) {
        return {
            setup: "En preparación",
            active: "En combate",
            finished: "Finalizado"
        }[status] || "Encuentro";
    }

    function hpText(combatant) {
        return combatant.current_hp + "/" + combatant.max_hp + " PG"
            + (Number(combatant.temp_hp) > 0
                ? " +" + combatant.temp_hp + " temporales"
                : "");
    }

    function refreshEntityOptions(filterText) {
        var form = document.querySelector("[data-add-combatant-form]");
        if (!form) {
            return;
        }
        var typeSelect = form.elements.entity_type;
        var valueSelect = form.elements.entity_value;
        var searchLabel = form.querySelector(".catalogSearchLabel");
        var type = typeSelect.value;
        var options = [];
        if (type === "character") {
            options = (state.available_characters || []).map(function (character) {
                return {
                    value: character.id_char,
                    label: character.name + " · " + (character.player_name || "Jugador")
                };
            });
        } else if (type === "npc") {
            options = state.npcs.map(function (npc) {
                return {
                    value: npc.id_game_npc,
                    label: npc.name + " · CA " + npc.armor_class + " · " + npc.max_hp + " PG"
                };
            });
        } else {
            var search = String(filterText || "").trim().toLocaleLowerCase("es");
            options = config.monsters.filter(function (monster) {
                if (!search) {
                    return true;
                }
                var haystack = [
                    monster.name,
                    monster.original_name,
                    monster.type,
                    "vd " + monster.challenge
                ].join(" ").toLocaleLowerCase("es");
                return haystack.indexOf(search) !== -1;
            }).slice(0, 250).map(function (monster) {
                return {
                    value: monster.key,
                    label: monster.name + " · VD " + monster.challenge
                        + " · " + monster.max_hp + " PG"
                };
            });
        }
        searchLabel.hidden = type !== "monster";
        valueSelect.innerHTML = options.length
            ? options.map(function (option) {
                return "<option value=\"" + escapeHtml(option.value) + "\">"
                    + escapeHtml(option.label) + "</option>";
            }).join("")
            : "<option value=\"\">No hay opciones disponibles</option>";
    }

    function renderCombatantDialog(combatantId) {
        var combatant = state.combatants.find(function (entry) {
            return Number(entry.id_combatant) === Number(combatantId);
        });
        if (!combatant || !canControl(combatant)) {
            selectedCombatantId = null;
            if (combatantDialog.open) {
                combatantDialog.close();
            }
            return;
        }
        selectedCombatantId = Number(combatant.id_combatant);
        var resources = combatant.resources || [];
        var resourceRows = resources.length
            ? resources.map(function (resource) {
                return "<li><div><strong>" + escapeHtml(resource.name)
                    + "</strong><span>" + escapeHtml(resource.kind) + "</span></div>"
                    + "<div class=\"resourceCounter\"><button type=\"button\" data-resource-change=\"-1\""
                    + " data-resource-id=\"" + escapeHtml(resource.id) + "\">−</button>"
                    + "<b>" + Number(resource.current) + " / " + Number(resource.maximum) + "</b>"
                    + "<button type=\"button\" data-resource-change=\"1\""
                    + " data-resource-id=\"" + escapeHtml(resource.id) + "\">＋</button></div></li>";
            }).join("")
            : "<li class=\"emptyResource\">Sin recursos registrados.</li>";
        var conditionRows = (combatant.conditions || []).map(function (condition) {
            return "<button type=\"button\" data-remove-condition=\""
                + escapeHtml(condition) + "\">" + escapeHtml(condition) + " ×</button>";
        }).join("");
        var spellOptions = "<option value=\"\">Selecciona un conjuro</option>";
        if (state.custom_spells.length) {
            spellOptions += "<optgroup label=\"Conjuros de la partida\">"
                + state.custom_spells.map(function (spell) {
                    return "<option value=\"custom:" + Number(spell.id_game_spell) + "\">"
                        + escapeHtml(spell.name) + " · nivel " + Number(spell.spell_level)
                        + "</option>";
                }).join("") + "</optgroup>";
        }
        spellOptions += "<optgroup label=\"Grimorio de DeepRol\">"
            + config.spells.map(function (spell) {
                return "<option value=\"catalog:" + Number(spell.id) + "\">"
                    + escapeHtml(spell.name) + " · " + escapeHtml(spell.level)
                    + "</option>";
            }).join("") + "</optgroup>";
        var resourceOptions = "<option value=\"\">No gastar recurso</option>"
            + resources.map(function (resource) {
                return "<option value=\"" + escapeHtml(resource.id) + "\">"
                    + escapeHtml(resource.name) + " · " + Number(resource.current)
                    + "/" + Number(resource.maximum) + "</option>";
            }).join("");

        combatantDialog.querySelector("[data-combatant-dialog-content]").innerHTML =
            "<header><div><span class=\"gamesEyebrow\">Gestión de combatiente</span><h2>"
            + escapeHtml(combatant.name) + "</h2><p>" + hpText(combatant)
            + " · CA " + escapeHtml(combatant.armor_class)
            + "</p></div><button type=\"button\" data-close-dialog aria-label=\"Cerrar\">×</button></header>"
            + "<div class=\"combatantManagerGrid\">"
            + "<section><h3>Vida e iniciativa</h3>"
            + "<form class=\"inlineActionForm\" data-combatant-form=\"hp\"><label><span>Cantidad</span>"
            + "<input type=\"number\" name=\"amount\" value=\"1\" min=\"0\" max=\"9999\" required></label>"
            + "<div><button type=\"submit\" data-hp-mode=\"damage\">Daño</button>"
            + "<button type=\"submit\" data-hp-mode=\"heal\">Curar</button>"
            + "<button type=\"submit\" data-hp-mode=\"temp\">PG temp.</button>"
            + "<button type=\"submit\" data-hp-mode=\"set\">Fijar vida</button></div></form>"
            + "<form class=\"inlineActionForm compactForm\" data-combatant-form=\"initiative\">"
            + "<label><span>Iniciativa</span><input type=\"number\" step=\"0.01\" name=\"initiative\""
            + " value=\"" + (combatant.initiative == null ? "" : escapeHtml(combatant.initiative))
            + "\" min=\"-99\" max=\"999\" required></label><button type=\"submit\">Guardar</button></form>"
            + "</section><section><h3>Estados y concentración</h3>"
            + "<div class=\"conditionList\">" + (conditionRows || "<span>Sin estados</span>") + "</div>"
            + "<form class=\"inlineActionForm compactForm\" data-combatant-form=\"condition\">"
            + "<label><span>Nuevo estado</span><input name=\"condition\" maxlength=\"40\""
            + " list=\"conditionOptions\" placeholder=\"Envenenado\" required></label>"
            + "<datalist id=\"conditionOptions\"><option>Agarrado</option><option>Aturdido</option>"
            + "<option>Cegado</option><option>Asustado</option><option>Derribado</option>"
            + "<option>Envenenado</option><option>Hechizado</option><option>Incapacitado</option>"
            + "<option>Invisible</option><option>Paralizado</option><option>Restringido</option></datalist>"
            + "<button type=\"submit\">Añadir</button></form>"
            + "<form class=\"inlineActionForm compactForm\" data-combatant-form=\"concentration\">"
            + "<label><span>Concentración</span><input name=\"spell_name\" maxlength=\"160\""
            + " value=\"" + escapeHtml(combatant.concentrating_on || "")
            + "\" placeholder=\"Nombre del conjuro\"></label><button type=\"submit\">Guardar</button>"
            + (combatant.concentrating_on
                ? "<button type=\"button\" data-end-concentration>Terminar</button>"
                : "")
            + "</form></section><section><h3>Objetos y recursos</h3>"
            + "<ul class=\"resourceManagerList\">" + resourceRows + "</ul>"
            + "<form class=\"resourceDefinitionForm\" data-combatant-form=\"resource\">"
            + "<input name=\"name\" maxlength=\"60\" placeholder=\"Ki, poción, inspiración…\" required>"
            + "<select name=\"kind\"><option value=\"resource\">Recurso</option>"
            + "<option value=\"item\">Objeto</option><option value=\"class_resource\">Clase</option>"
            + "<option value=\"spell_slot\">Espacio de conjuro</option></select>"
            + "<input type=\"number\" name=\"maximum\" value=\"1\" min=\"1\" max=\"999\" required>"
            + "<button type=\"submit\">Añadir</button></form></section>"
            + "<section><h3>Lanzar conjuro</h3>"
            + "<form class=\"spellCastForm\" data-combatant-form=\"spell\">"
            + "<label><span>Conjuro</span><select name=\"spell_selection\">" + spellOptions + "</select></label>"
            + "<label><span>O nombre libre</span><input name=\"spell_name\" maxlength=\"120\""
            + " placeholder=\"Efecto improvisado\"></label><div class=\"formColumns\">"
            + "<label><span>Nivel de lanzamiento</span><input type=\"number\" name=\"slot_level\""
            + " value=\"0\" min=\"0\" max=\"9\"></label>"
            + "<label><span>Gasto asociado</span><select name=\"resource_id\">"
            + resourceOptions + "</select></label></div>"
            + "<label class=\"checkField\"><input type=\"checkbox\" name=\"concentration\" value=\"1\">"
            + "<span>Inicia concentración</span></label>"
            + "<button class=\"primaryGameButton\" type=\"submit\">Registrar lanzamiento</button></form></section>"
            + "</div>"
            + (state.viewer.role === "dm"
                ? "<footer class=\"dangerZone\"><button type=\"button\" data-remove-combatant>"
                    + "Retirar del encuentro</button></footer>"
                : "");
    }

    function sendCommand(command, payload) {
        var id = requestId();
        if (socket && socket.readyState === WebSocket.OPEN) {
            return new Promise(function (resolve, reject) {
                var timer = window.setTimeout(function () {
                    delete pending[id];
                    reject(new Error("La mesa no ha confirmado la acción. Comprueba la conexión."));
                }, 10000);
                pending[id] = {
                    resolve: resolve,
                    reject: reject,
                    timer: timer
                };
                socket.send(JSON.stringify({
                    type: "command",
                    request_id: id,
                    command: command,
                    payload: payload
                }));
            });
        }

        return fetch(config.api_url, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": config.csrf
            },
            body: JSON.stringify({
                action: "command",
                csrf_token: config.csrf,
                game_id: config.game_id,
                command: command,
                payload: payload
            })
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.ok) {
                    throw new Error(data.error || "No se pudo completar la acción.");
                }
                state = data.result.state;
                renderState();
                return data.result;
            });
        });
    }

    function runCommand(command, payload, successMessage) {
        showMessage("", false);
        return sendCommand(command, payload).then(function (result) {
            if (successMessage) {
                showMessage(successMessage, false);
            }
            return result;
        }).catch(function (error) {
            showMessage(error.message, true);
            throw error;
        });
    }

    function connect() {
        window.clearTimeout(reconnectTimer);
        setSocketStatus("isConnecting", "Conectando");
        try {
            socket = new WebSocket(config.ws_url);
        } catch (error) {
            scheduleReconnect();
            return;
        }
        socket.addEventListener("open", function () {
            reconnectAttempt = 0;
            setSocketStatus("isOnline", "En directo");
            socket.send(JSON.stringify({
                type: "auth",
                request_id: requestId(),
                token: config.socket_token
            }));
            window.clearInterval(heartbeatTimer);
            heartbeatTimer = window.setInterval(function () {
                if (socket && socket.readyState === WebSocket.OPEN) {
                    socket.send(JSON.stringify({ type: "ping", request_id: requestId() }));
                }
            }, 25000);
        });
        socket.addEventListener("message", function (event) {
            var message;
            try {
                message = JSON.parse(event.data);
            } catch (error) {
                return;
            }
            if (message.type === "state" && message.state) {
                state = message.state;
                presence = message.presence || presence;
                renderState();
                resolvePending(message.request_id, message);
            } else if (message.type === "presence") {
                presence = message.presence || [];
                renderParty();
            } else if (message.type === "error") {
                if (!rejectPending(message.request_id, message.error)) {
                    showMessage(message.error || "Error de sincronización.", true);
                }
            }
        });
        socket.addEventListener("close", function () {
            setSocketStatus("isOffline", "Modo de respaldo");
            window.clearInterval(heartbeatTimer);
            Object.keys(pending).forEach(function (id) {
                rejectPending(id, "Se perdió la conexión antes de confirmar la acción.");
            });
            scheduleReconnect();
        });
        socket.addEventListener("error", function () {
            setSocketStatus("isOffline", "Modo de respaldo");
        });
    }

    function scheduleReconnect() {
        reconnectAttempt += 1;
        var delay = Math.min(10000, 800 * Math.pow(1.7, reconnectAttempt));
        reconnectTimer = window.setTimeout(connect, delay);
    }

    function resolvePending(id, value) {
        if (!id || !pending[id]) {
            return false;
        }
        window.clearTimeout(pending[id].timer);
        pending[id].resolve(value);
        delete pending[id];
        return true;
    }

    function rejectPending(id, message) {
        if (!id || !pending[id]) {
            return false;
        }
        window.clearTimeout(pending[id].timer);
        pending[id].reject(new Error(message || "No se pudo completar la acción."));
        delete pending[id];
        return true;
    }

    document.addEventListener("click", function (event) {
        var copyButton = event.target.closest("[data-copy-invite]");
        if (copyButton) {
            var code = document.querySelector("[data-invite-code]").textContent.trim();
            navigator.clipboard.writeText(code).then(function () {
                showMessage("Código " + code + " copiado.", false);
            }).catch(function () {
                showMessage("Código de invitación: " + code, false);
            });
            return;
        }
        if (event.target.closest("[data-open-dm-tools]") && dmToolsDialog) {
            dmToolsDialog.showModal();
            refreshEntityOptions();
            return;
        }
        var close = event.target.closest("[data-close-dialog]");
        if (close) {
            var dialog = close.closest("dialog");
            if (dialog) {
                dialog.close();
            }
            return;
        }
        var manage = event.target.closest("[data-manage-combatant]");
        if (manage) {
            renderCombatantDialog(Number(manage.dataset.manageCombatant));
            combatantDialog.showModal();
            return;
        }
        var turn = event.target.closest("[data-set-turn]");
        if (turn && state.viewer.role === "dm") {
            runCommand("turn.set", { turn_index: Number(turn.dataset.setTurn) });
            return;
        }
        if (event.target.closest("[data-next-turn]")) {
            runCommand("turn.next", {});
            return;
        }
        var status = event.target.closest("[data-encounter-status]");
        if (status) {
            runCommand("encounter.status", { status: status.dataset.nextStatus });
            return;
        }
        var historyButton = event.target.closest("[data-history-filter]");
        if (historyButton) {
            historyFilter = historyButton.dataset.historyFilter;
            document.querySelectorAll("[data-history-filter]").forEach(function (button) {
                button.classList.toggle("isActive", button === historyButton);
            });
            renderHistory();
            return;
        }
        var dmTab = event.target.closest("[data-dm-tab]");
        if (dmTab) {
            document.querySelectorAll("[data-dm-tab]").forEach(function (button) {
                button.classList.toggle("isActive", button === dmTab);
            });
            document.querySelectorAll("[data-dm-panel]").forEach(function (panel) {
                panel.classList.toggle("isActive", panel.dataset.dmPanel === dmTab.dataset.dmTab);
            });
        }
    });

    document.querySelectorAll("dialog").forEach(function (dialog) {
        dialog.addEventListener("click", function (event) {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll("[data-command-form]").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            event.preventDefault();
            if (!form.reportValidity()) {
                return;
            }
            var payload = {};
            new FormData(form).forEach(function (value, key) {
                payload[key] = value;
            });
            payload.concentration = form.elements.concentration
                ? form.elements.concentration.checked
                : false;
            var button = form.querySelector("[type='submit']");
            button.disabled = true;
            runCommand(form.dataset.commandForm, payload, "Cambio guardado.")
                .then(function () { form.reset(); })
                .finally(function () { button.disabled = false; });
        });
    });

    var addForm = document.querySelector("[data-add-combatant-form]");
    if (addForm) {
        addForm.elements.entity_type.addEventListener("change", function () {
            refreshEntityOptions();
        });
        addForm.querySelector("[data-monster-search]").addEventListener("input", function (event) {
            refreshEntityOptions(event.target.value);
        });
        addForm.addEventListener("submit", function (event) {
            event.preventDefault();
            if (!addForm.reportValidity()) {
                return;
            }
            var type = addForm.elements.entity_type.value;
            var value = addForm.elements.entity_value.value;
            var payload = { entity_type: type };
            if (type === "monster") {
                payload.source_key = value;
            } else {
                payload.entity_id = Number(value);
            }
            var button = addForm.querySelector("[type='submit']");
            button.disabled = true;
            runCommand("combatant.add", payload, "Combatiente incorporado.")
                .finally(function () { button.disabled = false; });
        });
    }

    if (combatantDialog) {
        combatantDialog.addEventListener("submit", function (event) {
            var form = event.target.closest("[data-combatant-form]");
            if (!form) {
                return;
            }
            event.preventDefault();
            if (!form.reportValidity()) {
                return;
            }
            var id = selectedCombatantId;
            var kind = form.dataset.combatantForm;
            var command;
            var payload = { combatant_id: id };
            if (kind === "hp") {
                command = "combatant.hp";
                payload.mode = event.submitter.dataset.hpMode;
                payload.amount = Number(form.elements.amount.value);
            } else if (kind === "initiative") {
                command = "combatant.initiative";
                payload.initiative = Number(form.elements.initiative.value);
            } else if (kind === "condition") {
                command = "combatant.condition";
                payload.action = "add";
                payload.condition = form.elements.condition.value;
            } else if (kind === "concentration") {
                command = "combatant.concentration";
                payload.spell_name = form.elements.spell_name.value;
            } else if (kind === "resource") {
                command = "resource.define";
                payload.name = form.elements.name.value;
                payload.kind = form.elements.kind.value;
                payload.maximum = Number(form.elements.maximum.value);
            } else if (kind === "spell") {
                command = "spell.cast";
                var selection = form.elements.spell_selection.value.split(":");
                payload.spell_kind = selection.length === 2 ? selection[0] : "manual";
                payload.spell_id = selection.length === 2 ? Number(selection[1]) : 0;
                payload.spell_name = form.elements.spell_name.value;
                payload.slot_level = Number(form.elements.slot_level.value);
                payload.resource_id = form.elements.resource_id.value;
                payload.concentration = form.elements.concentration.checked;
            }
            var submit = event.submitter;
            submit.disabled = true;
            runCommand(command, payload, kind === "spell" ? "Conjuro registrado." : "")
                .finally(function () { submit.disabled = false; });
        });

        combatantDialog.addEventListener("click", function (event) {
            var resourceButton = event.target.closest("[data-resource-change]");
            if (resourceButton) {
                runCommand("resource.change", {
                    combatant_id: selectedCombatantId,
                    resource_id: resourceButton.dataset.resourceId,
                    delta: Number(resourceButton.dataset.resourceChange)
                });
                return;
            }
            var conditionButton = event.target.closest("[data-remove-condition]");
            if (conditionButton) {
                runCommand("combatant.condition", {
                    combatant_id: selectedCombatantId,
                    action: "remove",
                    condition: conditionButton.dataset.removeCondition
                });
                return;
            }
            if (event.target.closest("[data-end-concentration]")) {
                runCommand("combatant.concentration", {
                    combatant_id: selectedCombatantId,
                    spell_name: ""
                });
                return;
            }
            if (event.target.closest("[data-remove-combatant]")) {
                runCommand("combatant.remove", {
                    combatant_id: selectedCombatantId
                }, "Combatiente retirado.").then(function () {
                    combatantDialog.close();
                    selectedCombatantId = null;
                });
            }
        });
    }

    renderState();
    if (
        state.viewer.role === "dm"
        && state.encounter
        && ["setup", "active"].indexOf(state.encounter.status) !== -1
        && state.members.some(function (member) {
            if (!Number(member.id_char)) {
                return false;
            }
            return !state.combatants.some(function (combatant) {
                return combatant.entity_type === "character"
                    && Number(combatant.entity_id) === Number(member.id_char);
            });
        })
        && !rosterSyncStarted
    ) {
        rosterSyncStarted = true;
        sendCommand("encounter.sync_roster", {}).catch(function (error) {
            showMessage(error.message, true);
        });
    }
    connect();
}());
