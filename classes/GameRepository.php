<?php

require_once __DIR__ . "/GameRules.php";
require_once __DIR__ . "/CompendiumRepository.php";
require_once __DIR__ . "/BestiaryLocalizer.php";
require_once __DIR__ . "/SpellSlotProgression.php";

final class GameRepository
{
    private const DB_DSN = "mysql:host=localhost;dbname=deeprol;charset=utf8mb4";
    private const DB_USER = "root";
    private const DB_PASSWORD = "";

    /** @var PDO */
    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: new PDO(
            self::DB_DSN,
            self::DB_USER,
            self::DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function createGame(
        int $ownerUserId,
        string $name,
        string $description = ""
    ): array {
        $name = trim($name);
        if ($ownerUserId <= 0 || $name === "") {
            throw new InvalidArgumentException("La partida necesita un nombre.");
        }
        if (mb_strlen($name) > 120 || mb_strlen($description) > 1000) {
            throw new InvalidArgumentException("El nombre o la descripción son demasiado largos.");
        }

        $this->pdo->beginTransaction();
        try {
            $user = $this->fetchUser($ownerUserId);
            if (!$user) {
                throw new RuntimeException("El usuario no existe.");
            }

            $gameId = 0;
            $inviteCode = "";
            for ($attempt = 0; $attempt < 20; $attempt++) {
                $inviteCode = GameRules::generateInviteCode();
                try {
                    $statement = $this->pdo->prepare(
                        "INSERT INTO games
                            (owner_user_id, name, description, invite_code, settings_json)
                        VALUES
                            (:owner, :name, :description, :invite, :settings)"
                    );
                    $statement->execute([
                        ":owner" => $ownerUserId,
                        ":name" => $name,
                        ":description" => trim($description),
                        ":invite" => $inviteCode,
                        ":settings" => json_encode([
                            "board" => [
                                "enabled" => false,
                                "schema_version" => 1,
                            ],
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                    $gameId = (int) $this->pdo->lastInsertId();
                    break;
                } catch (PDOException $exception) {
                    if ((string) $exception->getCode() !== "23000") {
                        throw $exception;
                    }
                }
            }

            if ($gameId <= 0) {
                throw new RuntimeException("No se pudo generar un código de invitación único.");
            }

            $member = $this->pdo->prepare(
                "INSERT INTO game_members
                    (id_game, id_user, role, display_name)
                VALUES
                    (:game, :user, 'dm', :display_name)"
            );
            $member->execute([
                ":game" => $gameId,
                ":user" => $ownerUserId,
                ":display_name" => (string) $user["username"],
            ]);

            $this->pdo->prepare(
                "UPDATE games SET state_version = 1 WHERE id_game = :game"
            )->execute([":game" => $gameId]);
            $this->insertEvent(
                $gameId,
                null,
                $ownerUserId,
                "game.created",
                [
                    "game_name" => $name,
                    "actor_name" => (string) $user["username"],
                ],
                1
            );
            $this->pdo->commit();

            return [
                "id_game" => $gameId,
                "invite_code" => $inviteCode,
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function joinGame(int $userId, string $rawCode, ?int $characterId): int
    {
        $code = GameRules::normalizeInviteCode($rawCode);
        if (!GameRules::isValidInviteCode($code)) {
            throw new InvalidArgumentException("El código debe tener seis caracteres.");
        }

        $this->pdo->beginTransaction();
        try {
            $gameStatement = $this->pdo->prepare(
                "SELECT * FROM games WHERE invite_code = :code FOR UPDATE"
            );
            $gameStatement->execute([":code" => $code]);
            $game = $gameStatement->fetch();
            if (!$game || (string) $game["status"] !== "active") {
                throw new RuntimeException("La partida no existe o ya está cerrada.");
            }

            $user = $this->fetchUser($userId);
            if (!$user) {
                throw new RuntimeException("El usuario no existe.");
            }

            if ($characterId !== null && $characterId > 0) {
                $character = $this->fetchOwnedCharacter($characterId, $userId);
                if (!$character) {
                    throw new RuntimeException("Ese personaje no pertenece al jugador.");
                }
            } else {
                $characterId = null;
            }

            $existing = $this->fetchMembership((int) $game["id_game"], $userId);
            if ($existing && (string) $existing["role"] === "dm") {
                $characterId = $existing["id_char"] !== null
                    ? (int) $existing["id_char"]
                    : $characterId;
            }

            $statement = $this->pdo->prepare(
                "INSERT INTO game_members
                    (id_game, id_user, role, id_char, display_name, last_seen_at)
                VALUES
                    (:game, :user, 'player', :character, :display_name, NOW())
                ON DUPLICATE KEY UPDATE
                    id_char = IF(role = 'dm', id_char, VALUES(id_char)),
                    display_name = VALUES(display_name),
                    last_seen_at = NOW()"
            );
            try {
                $statement->execute([
                    ":game" => (int) $game["id_game"],
                    ":user" => $userId,
                    ":character" => $characterId,
                    ":display_name" => (string) $user["username"],
                ]);
            } catch (PDOException $exception) {
                if ((string) $exception->getCode() === "23000") {
                    throw new RuntimeException("Ese personaje ya está vinculado a otro jugador de la partida.");
                }
                throw $exception;
            }

            $autoAddedCombatant = null;
            $currentEncounterId = (int) ($game["current_encounter_id"] ?? 0);
            if ($characterId !== null && $currentEncounterId > 0) {
                $encounterStatement = $this->pdo->prepare(
                    "SELECT status
                    FROM game_encounters
                    WHERE id_game = :game AND id_encounter = :encounter
                    LIMIT 1"
                );
                $encounterStatement->execute([
                    ":game" => (int) $game["id_game"],
                    ":encounter" => $currentEncounterId,
                ]);
                $encounterStatus = (string) $encounterStatement->fetchColumn();
                if (in_array($encounterStatus, ["setup", "active"], true)) {
                    if (!$existing || (string) $existing["role"] !== "dm") {
                        $this->pdo->prepare(
                            "UPDATE game_combatants
                            SET is_active = 0
                            WHERE id_encounter = :encounter
                                AND entity_type = 'character'
                                AND owner_user_id = :user
                                AND entity_id <> :character"
                        )->execute([
                            ":encounter" => $currentEncounterId,
                            ":user" => $userId,
                            ":character" => $characterId,
                        ]);
                    }
                    $autoAddedCombatant = $this->addCharacterCombatant(
                        (int) $game["id_game"],
                        $currentEncounterId,
                        $characterId
                    );
                }
            }

            $version = (int) $game["state_version"] + 1;
            $this->pdo->prepare(
                "UPDATE games SET state_version = :version WHERE id_game = :game"
            )->execute([
                ":version" => $version,
                ":game" => (int) $game["id_game"],
            ]);
            $this->insertEvent(
                (int) $game["id_game"],
                null,
                $userId,
                $existing ? "member.updated" : "member.joined",
                [
                    "actor_name" => (string) $user["username"],
                    "character_id" => $characterId,
                    "combatant_id" => $autoAddedCombatant
                        ? (int) $autoAddedCombatant["id_combatant"]
                        : null,
                ],
                $version
            );
            $this->pdo->commit();
            return (int) $game["id_game"];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function listGamesForUser(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                g.id_game,
                g.name,
                g.description,
                g.invite_code,
                g.status,
                g.updated_at,
                gm.role,
                gm.id_char,
                c.name AS character_name,
                COUNT(DISTINCT members.id_game_member) AS member_count,
                ge.name AS encounter_name,
                ge.status AS encounter_status,
                ge.round_number
            FROM game_members gm
            INNER JOIN games g ON g.id_game = gm.id_game
            LEFT JOIN chars c ON c.id_char = gm.id_char
            LEFT JOIN game_members members ON members.id_game = g.id_game
            LEFT JOIN game_encounters ge ON ge.id_encounter = g.current_encounter_id
            WHERE gm.id_user = :user
            GROUP BY
                g.id_game, g.name, g.description, g.invite_code, g.status,
                g.updated_at, gm.role, gm.id_char, c.name, ge.name,
                ge.status, ge.round_number
            ORDER BY g.updated_at DESC, g.id_game DESC"
        );
        $statement->execute([":user" => $userId]);
        return $statement->fetchAll();
    }

    public function charactersForUser(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id_char, name, clase, nivel, image_path
            FROM chars
            WHERE id_user = :user
            ORDER BY name"
        );
        $statement->execute([":user" => $userId]);
        return $statement->fetchAll();
    }

    public function fetchMembership(int $gameId, int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT gm.*, u.username, c.name AS character_name
            FROM game_members gm
            INNER JOIN usuario u ON u.ID_usuario = gm.id_user
            LEFT JOIN chars c ON c.id_char = gm.id_char
            WHERE gm.id_game = :game AND gm.id_user = :user
            LIMIT 1"
        );
        $statement->execute([":game" => $gameId, ":user" => $userId]);
        $membership = $statement->fetch();
        return $membership ?: null;
    }

    public function issueSocketToken(int $gameId, int $userId): string
    {
        if (!$this->fetchMembership($gameId, $userId)) {
            throw new RuntimeException("No perteneces a esta partida.");
        }

        $rawToken = bin2hex(random_bytes(32));
        $hash = hash("sha256", $rawToken);
        $this->pdo->prepare(
            "DELETE FROM game_socket_tokens
            WHERE expires_at < NOW()
                OR (id_game = :game AND id_user = :user)"
        )->execute([":game" => $gameId, ":user" => $userId]);
        $this->pdo->prepare(
            "INSERT INTO game_socket_tokens
                (token_hash, id_game, id_user, expires_at)
            VALUES
                (:hash, :game, :user, DATE_ADD(NOW(), INTERVAL 4 HOUR))"
        )->execute([
            ":hash" => $hash,
            ":game" => $gameId,
            ":user" => $userId,
        ]);

        return $rawToken;
    }

    public function authenticateSocketToken(string $rawToken): ?array
    {
        if (!preg_match("/^[a-f0-9]{64}$/", $rawToken)) {
            return null;
        }

        $statement = $this->pdo->prepare(
            "SELECT
                gst.id_game,
                gst.id_user,
                gm.role,
                gm.id_char,
                gm.display_name,
                u.username
            FROM game_socket_tokens gst
            INNER JOIN game_members gm
                ON gm.id_game = gst.id_game AND gm.id_user = gst.id_user
            INNER JOIN usuario u ON u.ID_usuario = gst.id_user
            INNER JOIN games g ON g.id_game = gst.id_game
            WHERE gst.token_hash = :hash
                AND gst.expires_at > NOW()
                AND g.status = 'active'
            LIMIT 1"
        );
        $statement->execute([":hash" => hash("sha256", $rawToken)]);
        $identity = $statement->fetch();
        if (!$identity) {
            return null;
        }

        $this->pdo->prepare(
            "UPDATE game_members SET last_seen_at = NOW()
            WHERE id_game = :game AND id_user = :user"
        )->execute([
            ":game" => (int) $identity["id_game"],
            ":user" => (int) $identity["id_user"],
        ]);

        return $identity;
    }

    public function getState(int $gameId, int $userId): array
    {
        $membership = $this->fetchMembership($gameId, $userId);
        if (!$membership) {
            throw new RuntimeException("No perteneces a esta partida.");
        }

        $gameStatement = $this->pdo->prepare(
            "SELECT g.*, u.username AS owner_name
            FROM games g
            INNER JOIN usuario u ON u.ID_usuario = g.owner_user_id
            WHERE g.id_game = :game
            LIMIT 1"
        );
        $gameStatement->execute([":game" => $gameId]);
        $game = $gameStatement->fetch();
        if (!$game) {
            throw new RuntimeException("La partida no existe.");
        }

        $membersStatement = $this->pdo->prepare(
            "SELECT
                gm.id_user,
                gm.role,
                gm.id_char,
                gm.display_name,
                gm.joined_at,
                gm.last_seen_at,
                u.username,
                c.name AS character_name,
                c.clase,
                c.nivel
            FROM game_members gm
            INNER JOIN usuario u ON u.ID_usuario = gm.id_user
            LEFT JOIN chars c ON c.id_char = gm.id_char
            WHERE gm.id_game = :game
            ORDER BY (gm.role = 'dm') DESC, gm.joined_at"
        );
        $membersStatement->execute([":game" => $gameId]);

        $availableCharactersStatement = $this->pdo->prepare(
            "SELECT DISTINCT
                c.id_char,
                c.id_user,
                c.name,
                c.clase,
                c.nivel,
                gm.display_name AS player_name
            FROM game_members gm
            INNER JOIN chars c
                ON c.id_user = gm.id_user
                AND (
                    c.id_char = gm.id_char
                    OR gm.role = 'dm'
                )
            WHERE gm.id_game = :game
            ORDER BY c.name"
        );
        $availableCharactersStatement->execute([":game" => $gameId]);

        $spellStatement = $this->pdo->prepare(
            "SELECT *
            FROM game_custom_spells
            WHERE id_game = :game
            ORDER BY spell_level, name"
        );
        $spellStatement->execute([":game" => $gameId]);

        $npcStatement = $this->pdo->prepare(
            "SELECT *
            FROM game_npcs
            WHERE id_game = :game
            ORDER BY name"
        );
        $npcStatement->execute([":game" => $gameId]);

        $encounter = null;
        $combatants = [];
        $encounterId = (int) ($game["current_encounter_id"] ?? 0);
        if ($encounterId > 0) {
            $encounterStatement = $this->pdo->prepare(
                "SELECT * FROM game_encounters
                WHERE id_encounter = :encounter AND id_game = :game
                LIMIT 1"
            );
            $encounterStatement->execute([
                ":encounter" => $encounterId,
                ":game" => $gameId,
            ]);
            $encounter = $encounterStatement->fetch() ?: null;
        }

        if ($encounter) {
            $combatantStatement = $this->pdo->prepare(
                "SELECT *
                FROM game_combatants
                WHERE id_encounter = :encounter AND is_active = 1
                ORDER BY (initiative IS NULL), initiative DESC, initiative_modifier DESC, id_combatant"
            );
            $combatantStatement->execute([
                ":encounter" => (int) $encounter["id_encounter"],
            ]);
            $combatants = array_map(
                [$this, "hydrateCombatant"],
                $combatantStatement->fetchAll()
            );
        }

        $eventStatement = $this->pdo->prepare(
            "SELECT *
            FROM (
                SELECT
                    ge.id_event,
                    ge.id_encounter,
                    ge.actor_user_id,
                    ge.event_type,
                    ge.payload_json,
                    ge.state_version,
                    ge.created_at,
                    u.username AS actor_name
                FROM game_events ge
                LEFT JOIN usuario u ON u.ID_usuario = ge.actor_user_id
                WHERE ge.id_game = :game
                ORDER BY ge.id_event DESC
                LIMIT 100
            ) recent_events
            ORDER BY id_event ASC"
        );
        $eventStatement->execute([":game" => $gameId]);
        $events = array_map(
            static function (array $event): array {
                $payload = json_decode((string) $event["payload_json"], true);
                $event["payload"] = is_array($payload) ? $payload : [];
                unset($event["payload_json"]);
                return $event;
            },
            $eventStatement->fetchAll()
        );

        $game["settings"] = $this->decodeObject($game["settings_json"] ?? "{}");
        unset($game["settings_json"]);

        return [
            "game" => $game,
            "viewer" => [
                "id_user" => (int) $membership["id_user"],
                "role" => (string) $membership["role"],
                "id_char" => $membership["id_char"] !== null
                    ? (int) $membership["id_char"]
                    : null,
                "display_name" => (string) (
                    $membership["display_name"] ?: $membership["username"]
                ),
            ],
            "members" => $membersStatement->fetchAll(),
            "available_characters" => $availableCharactersStatement->fetchAll(),
            "custom_spells" => array_map([$this, "hydrateSpell"], $spellStatement->fetchAll()),
            "npcs" => array_map([$this, "hydrateNpc"], $npcStatement->fetchAll()),
            "encounter" => $encounter,
            "combatants" => $combatants,
            "events" => $events,
            "latest_event_id" => $events
                ? (int) end($events)["id_event"]
                : 0,
        ];
    }

    public function mutate(
        int $gameId,
        int $userId,
        string $defaultEventType,
        callable $mutator
    ): array {
        $this->pdo->beginTransaction();
        try {
            $gameStatement = $this->pdo->prepare(
                "SELECT * FROM games WHERE id_game = :game FOR UPDATE"
            );
            $gameStatement->execute([":game" => $gameId]);
            $game = $gameStatement->fetch();
            if (!$game || (string) $game["status"] !== "active") {
                throw new RuntimeException("La partida no está disponible.");
            }

            $membership = $this->fetchMembership($gameId, $userId);
            if (!$membership) {
                throw new RuntimeException("No perteneces a esta partida.");
            }

            $result = $mutator($this->pdo, $game, $membership);
            if (!is_array($result)) {
                $result = [];
            }
            $eventType = (string) ($result["event_type"] ?? $defaultEventType);
            $eventPayload = is_array($result["payload"] ?? null)
                ? $result["payload"]
                : [];
            $encounterId = isset($result["encounter_id"])
                ? (int) $result["encounter_id"]
                : ((int) ($game["current_encounter_id"] ?? 0) ?: null);
            $version = (int) $game["state_version"] + 1;

            $this->pdo->prepare(
                "UPDATE games
                SET state_version = :version
                WHERE id_game = :game"
            )->execute([":version" => $version, ":game" => $gameId]);
            $eventId = $this->insertEvent(
                $gameId,
                $encounterId,
                $userId,
                $eventType,
                $eventPayload,
                $version
            );
            $this->pdo->commit();

            return [
                "event_id" => $eventId,
                "event_type" => $eventType,
                "state_version" => $version,
                "state" => $this->getState($gameId, $userId),
            ];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function eventsAfter(int $gameId, int $eventId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id_event, event_type, state_version
            FROM game_events
            WHERE id_game = :game AND id_event > :event
            ORDER BY id_event
            LIMIT 100"
        );
        $statement->bindValue(":game", $gameId, PDO::PARAM_INT);
        $statement->bindValue(":event", $eventId, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function spellCatalog(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                id_spell AS id,
                name,
                level,
                escuela AS school,
                concentracion AS concentration
            FROM conjuros
            ORDER BY name"
        );
        return $statement->fetchAll();
    }

    public function monsterCatalog(): array
    {
        return array_map(
            static function (array $monster): array {
                $dexterity = (int) ($monster["abilities"]["dex"] ?? 10);
                return [
                    "key" => (string) ($monster["index"] ?? ""),
                    "name" => BestiaryLocalizer::name($monster),
                    "original_name" => (string) ($monster["name"] ?? ""),
                    "armor_class" => (int) ($monster["armorClass"] ?? 10),
                    "max_hp" => max(1, (int) ($monster["hitPoints"] ?? 1)),
                    "initiative_modifier" => (int) floor(($dexterity - 10) / 2),
                    "challenge" => $monster["challengeRating"] ?? 0,
                    "type" => BestiaryLocalizer::type((string) ($monster["type"] ?? "")),
                ];
            },
            CompendiumRepository::monsters()
        );
    }

    public function fetchOwnedCharacter(int $characterId, int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM chars
            WHERE id_char = :character AND id_user = :user
            LIMIT 1"
        );
        $statement->execute([
            ":character" => $characterId,
            ":user" => $userId,
        ]);
        $character = $statement->fetch();
        return $character ?: null;
    }

    public function characterSheetStats(array $character): array
    {
        $characterName = basename((string) ($character["name"] ?? ""));
        $path = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . "resources"
            . DIRECTORY_SEPARATOR . "chars"
            . DIRECTORY_SEPARATOR . $characterName
            . DIRECTORY_SEPARATOR . "sheet.json";
        $sheet = [];
        if (is_readable($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            $sheet = is_array($decoded) ? $decoded : [];
        }

        return [
            "armor_class" => GameRules::clampInt(
                $sheet["AC"] ?? $sheet["ArmorClass"] ?? 10,
                0,
                99
            ),
            "max_hp" => GameRules::clampInt($sheet["HPMax"] ?? 1, 1, 9999),
            "current_hp" => GameRules::clampInt(
                $sheet["HPCurrent"] ?? $sheet["HPMax"] ?? 1,
                0,
                9999
            ),
            "initiative_modifier" => GameRules::clampInt(
                preg_replace("/[^0-9-]/", "", (string) ($sheet["Initiative"] ?? 0)),
                -30,
                30
            ),
        ];
    }

    public function addCharacterCombatant(
        int $gameId,
        int $encounterId,
        int $characterId
    ): ?array {
        $statement = $this->pdo->prepare(
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
            ":character" => $characterId,
        ]);
        $character = $statement->fetch();
        if (!$character) {
            return null;
        }

        $duplicate = $this->pdo->prepare(
            "SELECT *
            FROM game_combatants
            WHERE id_encounter = :encounter
                AND entity_type = 'character'
                AND entity_id = :character
                AND is_active = 1
            LIMIT 1"
        );
        $duplicate->execute([
            ":encounter" => $encounterId,
            ":character" => $characterId,
        ]);
        $existing = $duplicate->fetch();
        if ($existing) {
            return $existing;
        }

        $stats = $this->characterSheetStats($character);
        $insert = $this->pdo->prepare(
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
                'character',
                :character,
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
        $insert->execute([
            ":encounter" => $encounterId,
            ":character" => $characterId,
            ":owner_user_id" => (int) $character["member_user_id"],
            ":name" => (string) $character["name"],
            ":armor_class" => $stats["armor_class"],
            ":max_hp" => $stats["max_hp"],
            ":current_hp" => min($stats["current_hp"], $stats["max_hp"]),
            ":initiative_modifier" => $stats["initiative_modifier"],
            ":resources" => json_encode(
                $this->characterSpellSlotResources($character),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            ":metadata" => json_encode([
                "class" => (string) ($character["clase"] ?? ""),
                "level" => (int) ($character["nivel"] ?? 1),
                "board_token" => null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $created = $character;
        $created["id_combatant"] = (int) $this->pdo->lastInsertId();
        return $created;
    }

    private function fetchUser(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT ID_usuario, username
            FROM usuario
            WHERE ID_usuario = :user
            LIMIT 1"
        );
        $statement->execute([":user" => $userId]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    private function characterSpellSlotResources(array $character): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                class_name,
                subclass_name,
                class_level AS level
            FROM character_class_levels
            WHERE id_char = :character
            ORDER BY is_primary DESC, sort_order"
        );
        $statement->execute([":character" => (int) $character["id_char"]]);
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

    private function insertEvent(
        int $gameId,
        ?int $encounterId,
        ?int $actorUserId,
        string $eventType,
        array $payload,
        int $stateVersion
    ): int {
        $statement = $this->pdo->prepare(
            "INSERT INTO game_events
                (id_game, id_encounter, actor_user_id, event_type, payload_json, state_version)
            VALUES
                (:game, :encounter, :actor, :event_type, :payload, :version)"
        );
        $statement->execute([
            ":game" => $gameId,
            ":encounter" => $encounterId,
            ":actor" => $actorUserId,
            ":event_type" => $eventType,
            ":payload" => json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            ":version" => $stateVersion,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    private function hydrateCombatant(array $combatant): array
    {
        $combatant["conditions"] = GameRules::decodeJsonList(
            $combatant["conditions_json"] ?? "[]"
        );
        $combatant["resources"] = GameRules::decodeJsonList(
            $combatant["resources_json"] ?? "[]"
        );
        $combatant["metadata"] = $this->decodeObject(
            $combatant["metadata_json"] ?? "{}"
        );
        $combatant["position"] = $this->decodeObject(
            $combatant["position_json"] ?? "{}"
        );
        unset(
            $combatant["conditions_json"],
            $combatant["resources_json"],
            $combatant["metadata_json"],
            $combatant["position_json"]
        );
        return $combatant;
    }

    private function hydrateSpell(array $spell): array
    {
        $spell["tags"] = GameRules::decodeJsonList($spell["tags_json"] ?? "[]");
        unset($spell["tags_json"]);
        return $spell;
    }

    private function hydrateNpc(array $npc): array
    {
        $npc["metadata"] = $this->decodeObject($npc["metadata_json"] ?? "{}");
        unset($npc["metadata_json"]);
        return $npc;
    }

    private function decodeObject($json): array
    {
        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
