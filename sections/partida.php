<?php

require_once __DIR__ . "/../classes/GameRepository.php";

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
if ($userId <= 0) {
    header("Location: ../login.php");
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function issueGameRoomCsrfToken(string $cookieName): string
{
    $token = bin2hex(random_bytes(24));
    setcookie($cookieName, $token, [
        "expires" => time() + 14400,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Strict",
    ]);
    return $token;
}

$gameId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$csrfCookieName = "deeprol_game_csrf";
$csrfToken = isset($_COOKIE[$csrfCookieName])
    && preg_match("/^[a-f0-9]{48}$/", (string) $_COOKIE[$csrfCookieName])
        ? (string) $_COOKIE[$csrfCookieName]
        : issueGameRoomCsrfToken($csrfCookieName);

try {
    $repository = new GameRepository();
    $state = $repository->getState($gameId, $userId);
    $socketToken = $repository->issueSocketToken($gameId, $userId);
    $spellCatalog = $repository->spellCatalog();
    $monsterCatalog = $repository->monsterCatalog();
} catch (Throwable $exception) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Partida no disponible · DeepRol</title>
        <script src="../scripts/theme.js"></script>
        <link rel="stylesheet" href="../styles/games.css">
        <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    </head>
    <body>
        <main class="gameRoomError">
            <span aria-hidden="true">✦</span>
            <h1>Esta mesa no está disponible</h1>
            <p>No existe, está cerrada o todavía no formas parte de ella.</p>
            <a href="partidas.php">Volver a partidas</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$configuredSocketUrl = trim((string) getenv("DEEPROL_WS_PUBLIC_URL"));
if ($configuredSocketUrl !== "") {
    $socketUrl = $configuredSocketUrl;
} else {
    $socketScheme = (
        !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off"
    ) ? "wss" : "ws";
    $httpHost = (string) ($_SERVER["HTTP_HOST"] ?? "localhost");
    $socketHost = (string) parse_url("http://" . $httpHost, PHP_URL_HOST);
    if ($socketHost === "") {
        $socketHost = "localhost";
    }
    $socketPort = (int) (getenv("DEEPROL_WS_PORT") ?: 8081);
    $socketUrl = $socketScheme . "://" . $socketHost . ":" . $socketPort;
}

$bootstrap = [
    "game_id" => $gameId,
    "csrf" => $csrfToken,
    "api_url" => "../src/gameApi.php",
    "ws_url" => $socketUrl,
    "socket_token" => $socketToken,
    "state" => $state,
    "spells" => $spellCatalog,
    "monsters" => $monsterCatalog,
];
$jsonFlags = JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title><?= e($state["game"]["name"]) ?> · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/games.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script id="gameBootstrap" type="application/json"><?= json_encode($bootstrap, $jsonFlags) ?></script>
    <script defer src="../scripts/game.js"></script>
</head>
<body>
    <main class="gameRoom" data-game-room>
        <header class="gameRoomHeader">
            <div class="roomIdentity">
                <a class="roomBack" href="partidas.php" aria-label="Volver a partidas">←</a>
                <div>
                    <span class="gamesEyebrow">Mesa compartida</span>
                    <h1 data-game-name><?= e($state["game"]["name"]) ?></h1>
                </div>
            </div>
            <div class="roomHeaderActions">
                <?php if ($state["viewer"]["role"] === "dm"): ?>
                    <button class="roomInvite" type="button" data-copy-invite>
                        <span>Código de invitación</span>
                        <strong data-invite-code><?= e($state["game"]["invite_code"]) ?></strong>
                        <i aria-hidden="true">Copiar</i>
                    </button>
                <?php endif; ?>
                <span class="socketStatus isConnecting" data-socket-status>
                    <i></i>
                    <span>Conectando</span>
                </span>
                <?php if ($state["viewer"]["role"] === "dm"): ?>
                    <button class="primaryGameButton compact" type="button" data-open-dm-tools>
                        Herramientas del DM
                    </button>
                <?php endif; ?>
            </div>
        </header>

        <div class="roomMessage" data-room-message hidden></div>

        <section class="partyRibbon" aria-label="Participantes de la partida">
            <div class="partyRibbonTitle">
                <span>Grupo</span>
                <strong data-member-count><?= count($state["members"]) ?> participantes</strong>
            </div>
            <div class="partyMembers" data-party-members></div>
        </section>

        <section class="turnBanner" data-turn-banner>
            <div class="turnRound">
                <span>Ronda</span>
                <strong data-round>—</strong>
            </div>
            <div class="currentTurn">
                <span data-turn-kicker>Encuentro</span>
                <h2 data-current-turn>Prepara el primer combate</h2>
                <p data-turn-detail>El Dungeon Master puede incorporar personajes, NPC y criaturas.</p>
            </div>
            <div class="turnActions">
                <button class="secondaryGameButton compact" type="button" data-encounter-status hidden></button>
                <button class="primaryGameButton compact" type="button" data-next-turn hidden>
                    Siguiente turno →
                </button>
            </div>
        </section>

        <div class="gameRoomGrid">
            <section class="initiativePanel" aria-labelledby="initiativeTitle">
                <header class="panelHeading">
                    <div>
                        <span class="gamesEyebrow">Combate</span>
                        <h2 id="initiativeTitle">Orden de iniciativa</h2>
                    </div>
                    <span data-encounter-state>Sin encuentro</span>
                </header>
                <div class="initiativeList" data-initiative-list></div>
            </section>

            <aside class="activityPanel" aria-labelledby="activityTitle">
                <header class="panelHeading">
                    <div>
                        <span class="gamesEyebrow">Registro persistente</span>
                        <h2 id="activityTitle">Historial</h2>
                    </div>
                </header>
                <div class="historyFilters" role="group" aria-label="Filtrar historial">
                    <button class="isActive" type="button" data-history-filter="all">Todo</button>
                    <button type="button" data-history-filter="spells">Conjuros</button>
                    <button type="button" data-history-filter="resources">Recursos</button>
                </div>
                <ol class="activityFeed" data-activity-feed></ol>
                <div class="futureBoardNote">
                    <span aria-hidden="true">⌁</span>
                    <div>
                        <strong>Preparado para tablero</strong>
                        <p>La posición y los eventos espaciales ya tienen un lugar reservado, aunque aún no se muestran.</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <dialog id="combatantDialog" class="gameDialog combatantDialog">
        <div data-combatant-dialog-content></div>
    </dialog>

    <?php if ($state["viewer"]["role"] === "dm"): ?>
        <dialog id="dmToolsDialog" class="gameDialog dmToolsDialog">
            <div class="dmToolsShell">
                <header>
                    <div>
                        <span class="gamesEyebrow">Control de la mesa</span>
                        <h2>Herramientas del Dungeon Master</h2>
                    </div>
                    <button type="button" data-close-dialog aria-label="Cerrar">×</button>
                </header>

                <nav class="dmToolTabs" aria-label="Herramientas">
                    <button class="isActive" type="button" data-dm-tab="encounter">Encuentro</button>
                    <button type="button" data-dm-tab="npc">NPC</button>
                    <button type="button" data-dm-tab="spell">Conjuro propio</button>
                </nav>

                <section class="dmToolPanel isActive" data-dm-panel="encounter">
                    <form class="gameControlForm" data-command-form="encounter.create">
                        <h3>Nuevo encuentro</h3>
                        <label>
                            <span>Nombre</span>
                            <input name="name" maxlength="120" placeholder="Emboscada en el paso" required>
                        </label>
                        <button class="secondaryGameButton" type="submit">Crear encuentro</button>
                    </form>

                    <form class="gameControlForm" data-add-combatant-form>
                        <h3>Incorporar combatiente</h3>
                        <label>
                            <span>Origen</span>
                            <select name="entity_type">
                                <option value="character">Personajes del grupo</option>
                                <option value="npc">NPC de la partida</option>
                                <option value="monster">Enemigos del bestiario</option>
                            </select>
                        </label>
                        <label class="catalogSearchLabel" hidden>
                            <span>Filtrar bestiario</span>
                            <input type="search" data-monster-search placeholder="Nombre, tipo o VD">
                        </label>
                        <label>
                            <span>Combatiente</span>
                            <select name="entity_value" required></select>
                        </label>
                        <button class="primaryGameButton" type="submit">Añadir al orden</button>
                    </form>
                </section>

                <section class="dmToolPanel" data-dm-panel="npc">
                    <form class="gameControlForm" data-command-form="npc.create">
                        <h3>Crear NPC</h3>
                        <div class="formColumns">
                            <label>
                                <span>Nombre</span>
                                <input name="name" maxlength="120" required placeholder="Capitana Mireya">
                            </label>
                            <label>
                                <span>Clase de armadura</span>
                                <input type="number" name="armor_class" value="12" min="0" max="99" required>
                            </label>
                            <label>
                                <span>Vida máxima</span>
                                <input type="number" name="max_hp" value="10" min="1" max="9999" required>
                            </label>
                            <label>
                                <span>Mod. iniciativa</span>
                                <input type="number" name="initiative_modifier" value="0" min="-30" max="30" required>
                            </label>
                        </div>
                        <label>
                            <span>Notas</span>
                            <textarea name="notes" rows="4" maxlength="5000" placeholder="Motivación, voz, información que conoce…"></textarea>
                        </label>
                        <button class="primaryGameButton" type="submit">Guardar NPC</button>
                    </form>
                </section>

                <section class="dmToolPanel" data-dm-panel="spell">
                    <form class="gameControlForm" data-command-form="custom_spell.create">
                        <h3>Crear conjuro de la partida</h3>
                        <div class="formColumns">
                            <label>
                                <span>Nombre</span>
                                <input name="name" maxlength="120" required>
                            </label>
                            <label>
                                <span>Nivel</span>
                                <input type="number" name="spell_level" value="0" min="0" max="9" required>
                            </label>
                            <label>
                                <span>Escuela</span>
                                <select name="school">
                                    <option value="">Sin escuela</option>
                                    <option>Abjuración</option>
                                    <option>Adivinación</option>
                                    <option>Conjuración</option>
                                    <option>Encantamiento</option>
                                    <option>Evocación</option>
                                    <option>Ilusión</option>
                                    <option>Nigromancia</option>
                                    <option>Transmutación</option>
                                </select>
                            </label>
                            <label>
                                <span>Tiempo de lanzamiento</span>
                                <input name="casting_time" maxlength="80" placeholder="1 acción">
                            </label>
                            <label>
                                <span>Alcance</span>
                                <input name="range_text" maxlength="80" placeholder="18 metros">
                            </label>
                            <label>
                                <span>Duración</span>
                                <input name="duration" maxlength="100" placeholder="1 minuto">
                            </label>
                        </div>
                        <label>
                            <span>Descripción</span>
                            <textarea name="description" rows="5" maxlength="5000"></textarea>
                        </label>
                        <label class="checkField">
                            <input type="checkbox" name="concentration" value="1">
                            <span>Requiere concentración</span>
                        </label>
                        <button class="primaryGameButton" type="submit">Añadir al grimorio de la partida</button>
                    </form>
                </section>
            </div>
        </dialog>
    <?php endif; ?>
</body>
</html>
