<?php

require_once __DIR__ . "/GameRepository.php";
require_once __DIR__ . "/SpellSlotProgression.php";

final class GameCommandService
{
    /** @var GameRepository */
    private $repository;

    public function __construct(GameRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(
        int $gameId,
        int $userId,
        string $command,
        array $payload
    ): array {
        if ($gameId <= 0 || $userId <= 0) {
            throw new InvalidArgumentException("Identidad de partida no válida.");
        }

        switch ($command) {
            case "encounter.create":
                return $this->createEncounter($gameId, $userId, $payload);
            case "encounter.status":
                return $this->setEncounterStatus($gameId, $userId, $payload);
            case "encounter.sync_roster":
                return $this->syncEncounterRoster($gameId, $userId);
            case "turn.next":
                return $this->advanceTurn($gameId, $userId);
            case "turn.set":
                return $this->setTurn($gameId, $userId, $payload);
            case "combatant.add":
                return $this->addCombatant($gameId, $userId, $payload);
            case "combatant.remove":
                return $this->removeCombatant($gameId, $userId, $payload);
            case "combatant.initiative":
                return $this->setInitiative($gameId, $userId, $payload);
            case "combatant.hp":
                return $this->changeHitPoints($gameId, $userId, $payload);
            case "combatant.condition":
                return $this->changeCondition($gameId, $userId, $payload);
            case "combatant.concentration":
                return $this->setConcentration($gameId, $userId, $payload);
            case "resource.define":
                return $this->defineResource($gameId, $userId, $payload);
            case "resource.change":
                return $this->changeResource($gameId, $userId, $payload);
            case "spell.cast":
                return $this->castSpell($gameId, $userId, $payload);
            case "custom_spell.create":
                return $this->createCustomSpell($gameId, $userId, $payload);
            case "npc.create":
                return $this->createNpc($gameId, $userId, $payload);
            default:
                throw new InvalidArgumentException("La acción solicitada no existe.");
        }
    }

    private function createEncounter(int $gameId, int $userId, array $payload): array
    {
        $name = $this->text($payload["name"] ?? "", 120);
        if ($name === "") {
            $name = "Nuevo encuentro";
        }

        return $this->repository->mutate(
            $gameId,
            $userId,
            "encounter.created",
            function (PDO $pdo, array $game, array $membership) use ($name): array {
                $this->requireDm($membership);
                $statement = $pdo->prepare(
                    "INSERT INTO game_encounters (id_game, name)
                    VALUES (:game, :name)"
                );
                $statement->execute([
                    ":game" => (int) $game["id_game"],
                    ":name" => $name,
                ]);
                $encounterId = (int) $pdo->lastInsertId();
                $pdo->prepare(
                    "UPDATE games
                    SET current_encounter_id = :encounter
                    WHERE id_game = :game"
                )->execute([
                    ":encounter" => $encounterId,
                    ":game" => (int) $game["id_game"],
                ]);

                $autoAdded = $this->addLinkedCharacters(
                    $pdo,
                    (int) $game["id_game"],
                    $encounterId
                );

                return [
                    "encounter_id" => $encounterId,
                    "payload" => [
                        "encounter_name" => $name,
                        "auto_added_characters" => $autoAdded,
                    ],
                ];
            }
        );
    }

    private function syncEncounterRoster(int $gameId, int $userId): array
    {
        return $this->repository->mutate(
            $gameId,
            $userId,
            "encounter.roster_synced",
            function (PDO $pdo, array $game, array $membership): array {
                $this->requireDm($membership);
                $encounter = $this->currentEncounter($pdo, $game, true);
                if (!in_array((string) $encounter["status"], ["setup", "active"], true)) {
                    throw new RuntimeException("El encuentro finalizado no admite nuevos combatientes.");
                }
                $added = $this->addLinkedCharacters(
                    $pdo,
                    (int) $game["id_game"],
                    (int) $encounter["id_encounter"]
                );
                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "added_characters" => $added,
                        "added_count" => count($added),
                    ],
                ];
            }
        );
    }

    private function setEncounterStatus(int $gameId, int $userId, array $payload): array
    {
        $status = (string) ($payload["status"] ?? "");
        if (!in_array($status, ["setup", "active", "finished"], true)) {
            throw new InvalidArgumentException("El estado del encuentro no es válido.");
        }

        return $this->repository->mutate(
            $gameId,
            $userId,
            "encounter." . $status,
            function (PDO $pdo, array $game, array $membership) use ($status): array {
                $this->requireDm($membership);
                $encounter = $this->currentEncounter($pdo, $game, true);
                if ($status === "active") {
                    $count = $this->combatantCount($pdo, (int) $encounter["id_encounter"]);
                    if ($count <= 0) {
                        throw new RuntimeException("Añade al menos un combatiente antes de empezar.");
                    }
                }

                $pdo->prepare(
                    "UPDATE game_encounters
                    SET status = :status,
                        round_number = IF(:round_status = 'active', GREATEST(round_number, 1), round_number),
                        current_turn_index = IF(:turn_status = 'setup', 0, current_turn_index)
                    WHERE id_encounter = :encounter"
                )->execute([
                    ":status" => $status,
                    ":round_status" => $status,
                    ":turn_status" => $status,
                    ":encounter" => (int) $encounter["id_encounter"],
                ]);

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "encounter_name" => (string) $encounter["name"],
                        "status" => $status,
                    ],
                ];
            }
        );
    }

    private function advanceTurn(int $gameId, int $userId): array
    {
        return $this->repository->mutate(
            $gameId,
            $userId,
            "turn.advanced",
            function (PDO $pdo, array $game, array $membership): array {
                $this->requireDm($membership);
                $encounter = $this->currentEncounter($pdo, $game, true);
                if ((string) $encounter["status"] !== "active") {
                    throw new RuntimeException("El encuentro todavía no está activo.");
                }

                $count = $this->combatantCount($pdo, (int) $encounter["id_encounter"]);
                $next = GameRules::nextTurn(
                    (int) $encounter["current_turn_index"],
                    (int) $encounter["round_number"],
                    $count
                );
                $pdo->prepare(
                    "UPDATE game_encounters
                    SET current_turn_index = :turn_index,
                        round_number = :round_number
                    WHERE id_encounter = :encounter"
                )->execute([
                    ":turn_index" => $next["turn_index"],
                    ":round_number" => $next["round"],
                    ":encounter" => (int) $encounter["id_encounter"],
                ]);
                $current = $this->combatantAtIndex(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $next["turn_index"]
                );

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "round" => $next["round"],
                        "turn_index" => $next["turn_index"],
                        "combatant_id" => $current
                            ? (int) $current["id_combatant"]
                            : null,
                        "combatant_name" => $current["name"] ?? "",
                    ],
                ];
            }
        );
    }

    private function setTurn(int $gameId, int $userId, array $payload): array
    {
        $requestedIndex = max(0, (int) ($payload["turn_index"] ?? 0));
        return $this->repository->mutate(
            $gameId,
            $userId,
            "turn.selected",
            function (PDO $pdo, array $game, array $membership) use ($requestedIndex): array {
                $this->requireDm($membership);
                $encounter = $this->currentEncounter($pdo, $game, true);
                $count = $this->combatantCount($pdo, (int) $encounter["id_encounter"]);
                if ($count <= 0 || $requestedIndex >= $count) {
                    throw new InvalidArgumentException("Ese turno no existe.");
                }
                $pdo->prepare(
                    "UPDATE game_encounters
                    SET current_turn_index = :turn_index
                    WHERE id_encounter = :encounter"
                )->execute([
                    ":turn_index" => $requestedIndex,
                    ":encounter" => (int) $encounter["id_encounter"],
                ]);
                $current = $this->combatantAtIndex(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $requestedIndex
                );

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "turn_index" => $requestedIndex,
                        "combatant_id" => (int) ($current["id_combatant"] ?? 0),
                        "combatant_name" => (string) ($current["name"] ?? ""),
                    ],
                ];
            }
        );
    }

    private function addCombatant(int $gameId, int $userId, array $payload): array
    {
        $entityType = (string) ($payload["entity_type"] ?? "");
        if (!in_array($entityType, ["character", "npc", "monster"], true)) {
            throw new InvalidArgumentException("El tipo de combatiente no es válido.");
        }
        $entityId = (int) ($payload["entity_id"] ?? 0);
        $sourceKey = $this->text($payload["source_key"] ?? "", 120);

        return $this->repository->mutate(
            $gameId,
            $userId,
            "combatant.added",
            function (PDO $pdo, array $game, array $membership) use (
                $entityType,
                $entityId,
                $sourceKey
            ): array {
                $this->requireDm($membership);
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->resolveCombatant(
                    $pdo,
                    (int) $game["id_game"],
                    $entityType,
                    $entityId,
                    $sourceKey
                );

                if ($entityType !== "monster") {
                    $duplicate = $pdo->prepare(
                        "SELECT id_combatant
                        FROM game_combatants
                        WHERE id_encounter = :encounter
                            AND entity_type = :entity_type
                            AND entity_id = :entity_id
                            AND is_active = 1
                        LIMIT 1"
                    );
                    $duplicate->execute([
                        ":encounter" => (int) $encounter["id_encounter"],
                        ":entity_type" => $entityType,
                        ":entity_id" => $combatant["entity_id"],
                    ]);
                    if ($duplicate->fetch()) {
                        throw new RuntimeException("Ese combatiente ya participa en el encuentro.");
                    }
                }

                $statement = $pdo->prepare(
                    "INSERT INTO game_combatants (
                        id_encounter,
                        entity_type,
                        entity_id,
                        owner_user_id,
                        name,
                        armor_class,
                        max_hp,
                        current_hp,
                        initiative_modifier,
                        conditions_json,
                        resources_json,
                        metadata_json,
                        position_json
                    ) VALUES (
                        :encounter,
                        :entity_type,
                        :entity_id,
                        :owner_user_id,
                        :name,
                        :armor_class,
                        :max_hp,
                        :current_hp,
                        :initiative_modifier,
                        '[]',
                        :resources,
                        :metadata,
                        '{}'
                    )"
                );
                $statement->execute([
                    ":encounter" => (int) $encounter["id_encounter"],
                    ":entity_type" => $entityType,
                    ":entity_id" => $combatant["entity_id"],
                    ":owner_user_id" => $combatant["owner_user_id"],
                    ":name" => $combatant["name"],
                    ":armor_class" => $combatant["armor_class"],
                    ":max_hp" => $combatant["max_hp"],
                    ":current_hp" => $combatant["current_hp"],
                    ":initiative_modifier" => $combatant["initiative_modifier"],
                    ":resources" => json_encode(
                        $combatant["resources"],
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                    ":metadata" => json_encode(
                        $combatant["metadata"],
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                ]);

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => (int) $pdo->lastInsertId(),
                        "combatant_name" => $combatant["name"],
                        "entity_type" => $entityType,
                    ],
                ];
            }
        );
    }

    private function removeCombatant(int $gameId, int $userId, array $payload): array
    {
        $combatantId = (int) ($payload["combatant_id"] ?? 0);
        return $this->repository->mutate(
            $gameId,
            $userId,
            "combatant.removed",
            function (PDO $pdo, array $game, array $membership) use ($combatantId): array {
                $this->requireDm($membership);
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->combatant(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $combatantId,
                    true
                );
                $pdo->prepare(
                    "UPDATE game_combatants
                    SET is_active = 0
                    WHERE id_combatant = :combatant"
                )->execute([":combatant" => $combatantId]);
                $remaining = $this->combatantCount($pdo, (int) $encounter["id_encounter"]);
                if ((int) $encounter["current_turn_index"] >= $remaining) {
                    $pdo->prepare(
                        "UPDATE game_encounters
                        SET current_turn_index = 0
                        WHERE id_encounter = :encounter"
                    )->execute([":encounter" => (int) $encounter["id_encounter"]]);
                }

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => $combatantId,
                        "combatant_name" => (string) $combatant["name"],
                    ],
                ];
            }
        );
    }

    private function setInitiative(int $gameId, int $userId, array $payload): array
    {
        $combatantId = (int) ($payload["combatant_id"] ?? 0);
        $initiative = max(-99, min(999, (float) ($payload["initiative"] ?? 0)));
        return $this->repository->mutate(
            $gameId,
            $userId,
            "combatant.initiative_set",
            function (PDO $pdo, array $game, array $membership) use (
                $combatantId,
                $initiative
            ): array {
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->combatant(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $combatantId,
                    true
                );
                $this->requireControl($membership, $combatant);
                $pdo->prepare(
                    "UPDATE game_combatants
                    SET initiative = :initiative
                    WHERE id_combatant = :combatant"
                )->execute([
                    ":initiative" => $initiative,
                    ":combatant" => $combatantId,
                ]);

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => $combatantId,
                        "combatant_name" => (string) $combatant["name"],
                        "initiative" => $initiative,
                    ],
                ];
            }
        );
    }

    private function changeHitPoints(int $gameId, int $userId, array $payload): array
    {
        $combatantId = (int) ($payload["combatant_id"] ?? 0);
        $mode = (string) ($payload["mode"] ?? "damage");
        if (!in_array($mode, ["damage", "heal", "set", "temp"], true)) {
            throw new InvalidArgumentException("La operación de vida no es válida.");
        }
        $amount = GameRules::clampInt($payload["amount"] ?? 0, 0, 9999);

        return $this->repository->mutate(
            $gameId,
            $userId,
            "combatant.hp_changed",
            function (PDO $pdo, array $game, array $membership) use (
                $combatantId,
                $mode,
                $amount
            ): array {
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->combatant(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $combatantId,
                    true
                );
                $this->requireControl($membership, $combatant);
                $current = (int) $combatant["current_hp"];
                $maximum = max(1, (int) $combatant["max_hp"]);
                $temporary = (int) $combatant["temp_hp"];

                if ($mode === "damage") {
                    $remainingDamage = $amount;
                    if ($temporary > 0) {
                        $absorbed = min($temporary, $remainingDamage);
                        $temporary -= $absorbed;
                        $remainingDamage -= $absorbed;
                    }
                    $current = max(0, $current - $remainingDamage);
                } elseif ($mode === "heal") {
                    $current = min($maximum, $current + $amount);
                } elseif ($mode === "set") {
                    $current = min($maximum, $amount);
                } else {
                    $temporary = $amount;
                }

                $pdo->prepare(
                    "UPDATE game_combatants
                    SET current_hp = :current_hp,
                        temp_hp = :temp_hp
                    WHERE id_combatant = :combatant"
                )->execute([
                    ":current_hp" => $current,
                    ":temp_hp" => $temporary,
                    ":combatant" => $combatantId,
                ]);

                $eventType = "combatant.hp_set";
                if ($mode === "damage") {
                    $eventType = "combatant.damaged";
                } elseif ($mode === "heal") {
                    $eventType = "combatant.healed";
                } elseif ($mode === "temp") {
                    $eventType = "combatant.temp_hp_set";
                }

                return [
                    "event_type" => $eventType,
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => $combatantId,
                        "combatant_name" => (string) $combatant["name"],
                        "amount" => $amount,
                        "current_hp" => $current,
                        "temp_hp" => $temporary,
                    ],
                ];
            }
        );
    }

    private function changeCondition(int $gameId, int $userId, array $payload): array
    {
        $combatantId = (int) ($payload["combatant_id"] ?? 0);
        $action = (string) ($payload["action"] ?? "add");
        $condition = $this->text($payload["condition"] ?? "", 40);
        if (!in_array($action, ["add", "remove"], true) || $condition === "") {
            throw new InvalidArgumentException("Indica un estado válido.");
        }

        return $this->repository->mutate(
            $gameId,
            $userId,
            "combatant.condition_" . ($action === "add" ? "added" : "removed"),
            function (PDO $pdo, array $game, array $membership) use (
                $combatantId,
                $action,
                $condition
            ): array {
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->combatant(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $combatantId,
                    true
                );
                $this->requireControl($membership, $combatant);
                $conditions = GameRules::decodeJsonList($combatant["conditions_json"]);
                if ($action === "add" && !in_array($condition, $conditions, true)) {
                    $conditions[] = $condition;
                } elseif ($action === "remove") {
                    $conditions = array_values(array_filter(
                        $conditions,
                        static function ($existing) use ($condition): bool {
                            return (string) $existing !== $condition;
                        }
                    ));
                }
                $pdo->prepare(
                    "UPDATE game_combatants
                    SET conditions_json = :conditions
                    WHERE id_combatant = :combatant"
                )->execute([
                    ":conditions" => json_encode($conditions, JSON_UNESCAPED_UNICODE),
                    ":combatant" => $combatantId,
                ]);

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => $combatantId,
                        "combatant_name" => (string) $combatant["name"],
                        "condition" => $condition,
                    ],
                ];
            }
        );
    }

    private function setConcentration(int $gameId, int $userId, array $payload): array
    {
        $combatantId = (int) ($payload["combatant_id"] ?? 0);
        $spellName = $this->text($payload["spell_name"] ?? "", 160);
        return $this->repository->mutate(
            $gameId,
            $userId,
            $spellName === "" ? "concentration.ended" : "concentration.started",
            function (PDO $pdo, array $game, array $membership) use (
                $combatantId,
                $spellName
            ): array {
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->combatant(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $combatantId,
                    true
                );
                $this->requireControl($membership, $combatant);
                $pdo->prepare(
                    "UPDATE game_combatants
                    SET concentrating_on = :spell
                    WHERE id_combatant = :combatant"
                )->execute([
                    ":spell" => $spellName,
                    ":combatant" => $combatantId,
                ]);

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => $combatantId,
                        "combatant_name" => (string) $combatant["name"],
                        "spell_name" => $spellName,
                    ],
                ];
            }
        );
    }

    private function defineResource(int $gameId, int $userId, array $payload): array
    {
        $combatantId = (int) ($payload["combatant_id"] ?? 0);
        $name = $this->text($payload["name"] ?? "", 60);
        $kind = (string) ($payload["kind"] ?? "resource");
        if (!in_array($kind, ["spell_slot", "item", "class_resource", "resource"], true)) {
            $kind = "resource";
        }
        $maximum = GameRules::clampInt($payload["maximum"] ?? 1, 1, 999);
        if ($name === "") {
            throw new InvalidArgumentException("El recurso necesita un nombre.");
        }

        return $this->repository->mutate(
            $gameId,
            $userId,
            "resource.defined",
            function (PDO $pdo, array $game, array $membership) use (
                $combatantId,
                $name,
                $kind,
                $maximum
            ): array {
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->combatant(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $combatantId,
                    true
                );
                $this->requireControl($membership, $combatant);
                $resources = GameRules::decodeJsonList($combatant["resources_json"]);
                $resource = [
                    "id" => bin2hex(random_bytes(5)),
                    "name" => $name,
                    "kind" => $kind,
                    "current" => $maximum,
                    "maximum" => $maximum,
                ];
                $resources[] = $resource;
                $this->saveResources($pdo, $combatantId, $resources);

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => $combatantId,
                        "combatant_name" => (string) $combatant["name"],
                        "resource" => $resource,
                    ],
                ];
            }
        );
    }

    private function changeResource(int $gameId, int $userId, array $payload): array
    {
        $combatantId = (int) ($payload["combatant_id"] ?? 0);
        $resourceId = $this->text($payload["resource_id"] ?? "", 40);
        $delta = GameRules::clampInt($payload["delta"] ?? 0, -999, 999);
        if ($resourceId === "" || $delta === 0) {
            throw new InvalidArgumentException("Indica el recurso y la cantidad.");
        }

        return $this->repository->mutate(
            $gameId,
            $userId,
            $delta < 0 ? "resource.spent" : "resource.restored",
            function (PDO $pdo, array $game, array $membership) use (
                $combatantId,
                $resourceId,
                $delta
            ): array {
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->combatant(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $combatantId,
                    true
                );
                $this->requireControl($membership, $combatant);
                $resources = GameRules::decodeJsonList($combatant["resources_json"]);
                $changed = null;
                foreach ($resources as &$resource) {
                    if ((string) ($resource["id"] ?? "") !== $resourceId) {
                        continue;
                    }
                    $maximum = max(1, (int) ($resource["maximum"] ?? 1));
                    $current = (int) ($resource["current"] ?? 0);
                    $resource["current"] = max(0, min($maximum, $current + $delta));
                    $changed = $resource;
                    break;
                }
                unset($resource);
                if (!$changed) {
                    throw new RuntimeException("El recurso ya no existe.");
                }
                $this->saveResources($pdo, $combatantId, $resources);

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => $combatantId,
                        "combatant_name" => (string) $combatant["name"],
                        "resource" => $changed,
                        "delta" => $delta,
                    ],
                ];
            }
        );
    }

    private function castSpell(int $gameId, int $userId, array $payload): array
    {
        $combatantId = (int) ($payload["combatant_id"] ?? 0);
        $spellKind = (string) ($payload["spell_kind"] ?? "manual");
        $spellId = (int) ($payload["spell_id"] ?? 0);
        $manualName = $this->text($payload["spell_name"] ?? "", 120);
        $slotLevel = GameRules::clampInt($payload["slot_level"] ?? 0, 0, 9);
        $resourceId = $this->text($payload["resource_id"] ?? "", 40);
        $requestsConcentration = !empty($payload["concentration"]);

        return $this->repository->mutate(
            $gameId,
            $userId,
            "spell.cast",
            function (PDO $pdo, array $game, array $membership) use (
                $combatantId,
                $spellKind,
                $spellId,
                $manualName,
                $slotLevel,
                $resourceId,
                $requestsConcentration
            ): array {
                $encounter = $this->currentEncounter($pdo, $game, true);
                $combatant = $this->combatant(
                    $pdo,
                    (int) $encounter["id_encounter"],
                    $combatantId,
                    true
                );
                $this->requireControl($membership, $combatant);
                $spell = $this->resolveSpell(
                    $pdo,
                    (int) $game["id_game"],
                    $spellKind,
                    $spellId,
                    $manualName
                );
                $resources = GameRules::decodeJsonList($combatant["resources_json"]);
                $spentResource = null;
                if ($resourceId !== "") {
                    foreach ($resources as &$resource) {
                        if ((string) ($resource["id"] ?? "") !== $resourceId) {
                            continue;
                        }
                        $current = (int) ($resource["current"] ?? 0);
                        if ($current <= 0) {
                            throw new RuntimeException("No quedan usos de ese recurso.");
                        }
                        $resource["current"] = $current - 1;
                        $spentResource = $resource;
                        break;
                    }
                    unset($resource);
                    if (!$spentResource) {
                        throw new RuntimeException("El recurso seleccionado ya no existe.");
                    }
                    $this->saveResources($pdo, $combatantId, $resources);
                }

                $concentration = $requestsConcentration || !empty($spell["concentration"]);
                if ($concentration) {
                    $pdo->prepare(
                        "UPDATE game_combatants
                        SET concentrating_on = :spell
                        WHERE id_combatant = :combatant"
                    )->execute([
                        ":spell" => $spell["name"],
                        ":combatant" => $combatantId,
                    ]);
                }

                return [
                    "encounter_id" => (int) $encounter["id_encounter"],
                    "payload" => [
                        "combatant_id" => $combatantId,
                        "combatant_name" => (string) $combatant["name"],
                        "spell_name" => $spell["name"],
                        "spell_kind" => $spellKind,
                        "spell_id" => $spellId ?: null,
                        "slot_level" => $slotLevel,
                        "concentration" => $concentration,
                        "resource" => $spentResource,
                    ],
                ];
            }
        );
    }

    private function createCustomSpell(int $gameId, int $userId, array $payload): array
    {
        $name = $this->text($payload["name"] ?? "", 120);
        $description = $this->text($payload["description"] ?? "", 5000);
        $level = GameRules::clampInt($payload["spell_level"] ?? 0, 0, 9);
        if ($name === "") {
            throw new InvalidArgumentException("El conjuro necesita un nombre.");
        }

        $fields = [
            "school" => $this->text($payload["school"] ?? "", 40),
            "casting_time" => $this->text($payload["casting_time"] ?? "", 80),
            "range_text" => $this->text($payload["range_text"] ?? "", 80),
            "duration" => $this->text($payload["duration"] ?? "", 100),
            "concentration" => !empty($payload["concentration"]) ? 1 : 0,
        ];

        return $this->repository->mutate(
            $gameId,
            $userId,
            "custom_spell.created",
            function (PDO $pdo, array $game, array $membership) use (
                $name,
                $description,
                $level,
                $fields
            ): array {
                $this->requireDm($membership);
                $statement = $pdo->prepare(
                    "INSERT INTO game_custom_spells (
                        id_game,
                        created_by,
                        name,
                        description,
                        spell_level,
                        school,
                        casting_time,
                        range_text,
                        duration,
                        concentration,
                        tags_json
                    ) VALUES (
                        :game,
                        :user,
                        :name,
                        :description,
                        :spell_level,
                        :school,
                        :casting_time,
                        :range_text,
                        :duration,
                        :concentration,
                        '[]'
                    )"
                );
                $statement->execute([
                    ":game" => (int) $game["id_game"],
                    ":user" => (int) $membership["id_user"],
                    ":name" => $name,
                    ":description" => $description,
                    ":spell_level" => $level,
                    ":school" => $fields["school"],
                    ":casting_time" => $fields["casting_time"],
                    ":range_text" => $fields["range_text"],
                    ":duration" => $fields["duration"],
                    ":concentration" => $fields["concentration"],
                ]);

                return [
                    "encounter_id" => null,
                    "payload" => [
                        "spell_id" => (int) $pdo->lastInsertId(),
                        "spell_name" => $name,
                        "spell_level" => $level,
                    ],
                ];
            }
        );
    }

    private function createNpc(int $gameId, int $userId, array $payload): array
    {
        $name = $this->text($payload["name"] ?? "", 120);
        $armorClass = GameRules::clampInt($payload["armor_class"] ?? 10, 0, 99);
        $maxHp = GameRules::clampInt($payload["max_hp"] ?? 1, 1, 9999);
        $initiative = GameRules::clampInt(
            $payload["initiative_modifier"] ?? 0,
            -30,
            30
        );
        $notes = $this->text($payload["notes"] ?? "", 5000);
        if ($name === "") {
            throw new InvalidArgumentException("El NPC necesita un nombre.");
        }

        return $this->repository->mutate(
            $gameId,
            $userId,
            "npc.created",
            function (PDO $pdo, array $game, array $membership) use (
                $name,
                $armorClass,
                $maxHp,
                $initiative,
                $notes
            ): array {
                $this->requireDm($membership);
                $statement = $pdo->prepare(
                    "INSERT INTO game_npcs (
                        id_game,
                        created_by,
                        name,
                        armor_class,
                        max_hp,
                        current_hp,
                        initiative_modifier,
                        notes,
                        metadata_json
                    ) VALUES (
                        :game,
                        :user,
                        :name,
                        :armor_class,
                        :max_hp,
                        :current_hp,
                        :initiative_modifier,
                        :notes,
                        '{}'
                    )"
                );
                $statement->execute([
                    ":game" => (int) $game["id_game"],
                    ":user" => (int) $membership["id_user"],
                    ":name" => $name,
                    ":armor_class" => $armorClass,
                    ":max_hp" => $maxHp,
                    ":current_hp" => $maxHp,
                    ":initiative_modifier" => $initiative,
                    ":notes" => $notes,
                ]);

                return [
                    "encounter_id" => null,
                    "payload" => [
                        "npc_id" => (int) $pdo->lastInsertId(),
                        "npc_name" => $name,
                    ],
                ];
            }
        );
    }

    private function resolveCombatant(
        PDO $pdo,
        int $gameId,
        string $entityType,
        int $entityId,
        string $sourceKey
    ): array {
        if ($entityType === "character") {
            $statement = $pdo->prepare(
                "SELECT c.*, gm.id_user AS member_user_id
                FROM game_members gm
                INNER JOIN chars c
                    ON c.id_user = gm.id_user
                    AND (
                        c.id_char = gm.id_char
                        OR gm.role = 'dm'
                    )
                WHERE gm.id_game = :game
                    AND c.id_char = :character
                LIMIT 1"
            );
            $statement->execute([
                ":game" => $gameId,
                ":character" => $entityId,
            ]);
            $character = $statement->fetch();
            if (!$character) {
                throw new RuntimeException("El personaje no pertenece a la partida.");
            }
            $stats = $this->repository->characterSheetStats($character);
            return [
                "entity_id" => (int) $character["id_char"],
                "owner_user_id" => (int) $character["member_user_id"],
                "name" => (string) $character["name"],
                "armor_class" => $stats["armor_class"],
                "max_hp" => $stats["max_hp"],
                "current_hp" => min($stats["current_hp"], $stats["max_hp"]),
                "initiative_modifier" => $stats["initiative_modifier"],
                "resources" => $this->characterSpellSlotResources(
                    $pdo,
                    (int) $character["id_char"],
                    $character
                ),
                "metadata" => [
                    "class" => (string) ($character["clase"] ?? ""),
                    "level" => (int) ($character["nivel"] ?? 1),
                    "board_token" => null,
                ],
            ];
        }

        if ($entityType === "npc") {
            $statement = $pdo->prepare(
                "SELECT * FROM game_npcs
                WHERE id_game = :game AND id_game_npc = :npc
                LIMIT 1"
            );
            $statement->execute([":game" => $gameId, ":npc" => $entityId]);
            $npc = $statement->fetch();
            if (!$npc) {
                throw new RuntimeException("El NPC no pertenece a la partida.");
            }
            return [
                "entity_id" => (int) $npc["id_game_npc"],
                "owner_user_id" => null,
                "name" => (string) $npc["name"],
                "armor_class" => (int) $npc["armor_class"],
                "max_hp" => (int) $npc["max_hp"],
                "current_hp" => min(
                    (int) $npc["current_hp"],
                    (int) $npc["max_hp"]
                ),
                "initiative_modifier" => (int) $npc["initiative_modifier"],
                "resources" => [],
                "metadata" => [
                    "notes" => (string) $npc["notes"],
                    "board_token" => null,
                ],
            ];
        }

        foreach (CompendiumRepository::monsters() as $monster) {
            if ((string) ($monster["index"] ?? "") !== $sourceKey) {
                continue;
            }
            $dexterity = (int) ($monster["abilities"]["dex"] ?? 10);
            return [
                "entity_id" => null,
                "owner_user_id" => null,
                "name" => BestiaryLocalizer::name($monster),
                "armor_class" => (int) ($monster["armorClass"] ?? 10),
                "max_hp" => max(1, (int) ($monster["hitPoints"] ?? 1)),
                "current_hp" => max(1, (int) ($monster["hitPoints"] ?? 1)),
                "initiative_modifier" => (int) floor(($dexterity - 10) / 2),
                "resources" => [],
                "metadata" => [
                    "source_key" => $sourceKey,
                    "original_name" => (string) ($monster["name"] ?? ""),
                    "challenge" => $monster["challengeRating"] ?? 0,
                    "type" => (string) ($monster["type"] ?? ""),
                    "board_token" => null,
                ],
            ];
        }

        throw new RuntimeException("La criatura ya no está disponible en el bestiario.");
    }

    private function characterSpellSlotResources(
        PDO $pdo,
        int $characterId,
        array $character
    ): array {
        $statement = $pdo->prepare(
            "SELECT
                class_name,
                subclass_name,
                class_level AS level
            FROM character_class_levels
            WHERE id_char = :character
            ORDER BY is_primary DESC, sort_order"
        );
        $statement->execute([":character" => $characterId]);
        $classes = $statement->fetchAll();
        if (!$classes) {
            $classes = [[
                "class_name" => (string) ($character["clase"] ?? ""),
                "subclass_name" => (string) ($character["subclase"] ?? ""),
                "level" => (int) ($character["nivel"] ?? 1),
            ]];
        }

        $progression = SpellSlotProgression::forClasses($classes);
        $resources = [];
        foreach (($progression["groups"] ?? []) as $group) {
            $groupLabel = (string) ($group["label"] ?? "Conjuros");
            foreach (($group["slots"] ?? []) as $level => $maximum) {
                $resources[] = [
                    "id" => "slot-" . count($resources) . "-" . (int) $level,
                    "name" => $groupLabel . " · nivel " . (int) $level,
                    "kind" => "spell_slot",
                    "current" => (int) $maximum,
                    "maximum" => (int) $maximum,
                    "spell_level" => (int) $level,
                ];
            }
        }
        return $resources;
    }

    private function resolveSpell(
        PDO $pdo,
        int $gameId,
        string $spellKind,
        int $spellId,
        string $manualName
    ): array {
        if ($spellKind === "custom") {
            $statement = $pdo->prepare(
                "SELECT name, concentration
                FROM game_custom_spells
                WHERE id_game = :game AND id_game_spell = :spell
                LIMIT 1"
            );
            $statement->execute([":game" => $gameId, ":spell" => $spellId]);
            $spell = $statement->fetch();
            if ($spell) {
                return $spell;
            }
        } elseif ($spellKind === "catalog") {
            $statement = $pdo->prepare(
                "SELECT name, concentracion AS concentration
                FROM conjuros
                WHERE id_spell = :spell
                LIMIT 1"
            );
            $statement->execute([":spell" => $spellId]);
            $spell = $statement->fetch();
            if ($spell) {
                $value = mb_strtolower(trim((string) $spell["concentration"]));
                $spell["concentration"] = in_array(
                    $value,
                    ["1", "true", "yes", "si", "sí"],
                    true
                );
                return $spell;
            }
        } elseif ($manualName !== "") {
            return ["name" => $manualName, "concentration" => false];
        }

        throw new RuntimeException("Selecciona un conjuro válido.");
    }

    private function currentEncounter(PDO $pdo, array $game, bool $forUpdate): array
    {
        $encounterId = (int) ($game["current_encounter_id"] ?? 0);
        if ($encounterId <= 0) {
            throw new RuntimeException("Crea un encuentro antes de continuar.");
        }
        $sql = "SELECT * FROM game_encounters
            WHERE id_encounter = :encounter AND id_game = :game";
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        $statement = $pdo->prepare($sql);
        $statement->execute([
            ":encounter" => $encounterId,
            ":game" => (int) $game["id_game"],
        ]);
        $encounter = $statement->fetch();
        if (!$encounter) {
            throw new RuntimeException("El encuentro actual ya no existe.");
        }
        return $encounter;
    }

    private function addLinkedCharacters(
        PDO $pdo,
        int $gameId,
        int $encounterId
    ): array {
        $members = $pdo->prepare(
            "SELECT id_char
            FROM game_members
            WHERE id_game = :game AND id_char IS NOT NULL"
        );
        $members->execute([":game" => $gameId]);
        $added = [];
        foreach ($members->fetchAll(PDO::FETCH_COLUMN) as $characterId) {
            $alreadyPresent = $pdo->prepare(
                "SELECT id_combatant
                FROM game_combatants
                WHERE id_encounter = :encounter
                    AND entity_type = 'character'
                    AND entity_id = :character
                    AND is_active = 1
                LIMIT 1"
            );
            $alreadyPresent->execute([
                ":encounter" => $encounterId,
                ":character" => (int) $characterId,
            ]);
            if ($alreadyPresent->fetch()) {
                continue;
            }
            $combatant = $this->repository->addCharacterCombatant(
                $gameId,
                $encounterId,
                (int) $characterId
            );
            if ($combatant) {
                $added[] = [
                    "id_combatant" => (int) $combatant["id_combatant"],
                    "name" => (string) $combatant["name"],
                ];
            }
        }
        return $added;
    }

    private function combatant(
        PDO $pdo,
        int $encounterId,
        int $combatantId,
        bool $forUpdate
    ): array {
        $sql = "SELECT * FROM game_combatants
            WHERE id_encounter = :encounter
                AND id_combatant = :combatant
                AND is_active = 1";
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        $statement = $pdo->prepare($sql);
        $statement->execute([
            ":encounter" => $encounterId,
            ":combatant" => $combatantId,
        ]);
        $combatant = $statement->fetch();
        if (!$combatant) {
            throw new RuntimeException("El combatiente ya no está disponible.");
        }
        return $combatant;
    }

    private function combatantCount(PDO $pdo, int $encounterId): int
    {
        $statement = $pdo->prepare(
            "SELECT COUNT(*)
            FROM game_combatants
            WHERE id_encounter = :encounter AND is_active = 1"
        );
        $statement->execute([":encounter" => $encounterId]);
        return (int) $statement->fetchColumn();
    }

    private function combatantAtIndex(
        PDO $pdo,
        int $encounterId,
        int $turnIndex
    ): ?array {
        $statement = $pdo->prepare(
            "SELECT id_combatant, name
            FROM game_combatants
            WHERE id_encounter = :encounter AND is_active = 1
            ORDER BY (initiative IS NULL), initiative DESC, initiative_modifier DESC, id_combatant
            LIMIT :turn_index, 1"
        );
        $statement->bindValue(":encounter", $encounterId, PDO::PARAM_INT);
        $statement->bindValue(":turn_index", $turnIndex, PDO::PARAM_INT);
        $statement->execute();
        $combatant = $statement->fetch();
        return $combatant ?: null;
    }

    private function saveResources(PDO $pdo, int $combatantId, array $resources): void
    {
        $pdo->prepare(
            "UPDATE game_combatants
            SET resources_json = :resources
            WHERE id_combatant = :combatant"
        )->execute([
            ":resources" => json_encode(
                array_values($resources),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            ":combatant" => $combatantId,
        ]);
    }

    private function requireDm(array $membership): void
    {
        if ((string) ($membership["role"] ?? "") !== "dm") {
            throw new RuntimeException("Esta acción está reservada al Dungeon Master.");
        }
    }

    private function requireControl(array $membership, array $combatant): void
    {
        if (!GameRules::canControlCombatant($membership, $combatant)) {
            throw new RuntimeException("Solo puedes modificar tu propio personaje.");
        }
    }

    private function text($value, int $maximumLength): string
    {
        $text = trim((string) $value);
        $text = preg_replace("/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/u", "", $text) ?? "";
        return mb_substr($text, 0, $maximumLength);
    }
}
