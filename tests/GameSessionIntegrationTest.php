<?php

require_once __DIR__ . "/../classes/GameRepository.php";
require_once __DIR__ . "/../classes/GameCommandService.php";

function assertGameSession(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$repository = new GameRepository();
$commands = new GameCommandService($repository);
$gameId = 0;
$testCharacterIds = [];

try {
    $characterInsert = $repository->pdo()->prepare(
        "INSERT INTO chars (
            id_user,
            name,
            raza,
            subraza,
            nivel,
            clase,
            subclase,
            pdf_path,
            image_path,
            full_body_image_path
        ) VALUES (
            :user,
            :name,
            'Humano',
            '',
            3,
            'Guerrero',
            'Campeón',
            '',
            '',
            ''
        )"
    );
    foreach ([3, 5] as $temporaryUserId) {
        $characterInsert->execute([
            ":user" => $temporaryUserId,
            ":name" => "Combatiente temporal "
                . $temporaryUserId
                . " "
                . bin2hex(random_bytes(2)),
        ]);
        $testCharacterIds[$temporaryUserId] = (int) $repository->pdo()->lastInsertId();
    }

    $created = $repository->createGame(
        1,
        "Prueba WebSocket " . bin2hex(random_bytes(3)),
        "Partida temporal de integración."
    );
    $gameId = (int) $created["id_game"];
    assertGameSession($gameId > 0, "No se ha creado la partida.");
    assertGameSession(
        preg_match("/^[A-Z0-9]{6}$/", (string) $created["invite_code"]) === 1,
        "El código de invitación creado no es válido."
    );

    $joinedGameId = $repository->joinGame(
        3,
        (string) $created["invite_code"],
        $testCharacterIds[3]
    );
    assertGameSession($joinedGameId === $gameId, "El segundo usuario no se ha unido.");

    $encounterResult = $commands->handle(
        $gameId,
        1,
        "encounter.create",
        ["name" => "Encuentro temporal"]
    );
    assertGameSession(
        $encounterResult["event_type"] === "encounter.created",
        "No se ha registrado la creación del encuentro."
    );

    $commands->handle(
        $gameId,
        1,
        "combatant.add",
        ["entity_type" => "character", "entity_id" => 1]
    );
    $state = $repository->getState($gameId, 1);
    assertGameSession(
        count($state["combatants"]) === 2,
        "Los personajes vinculados antes de crear el encuentro no se añadieron solos."
    );
    $combatant = null;
    foreach ($state["combatants"] as $entry) {
        if ((int) $entry["entity_id"] === 1) {
            $combatant = $entry;
            break;
        }
    }
    assertGameSession((bool) $combatant, "No se añadió el personaje del Dungeon Master.");
    assertGameSession(
        count($combatant["resources"]) > 0,
        "El personaje lanzador no recibió sus espacios de conjuro."
    );

    $combatantId = (int) $combatant["id_combatant"];
    $commands->handle(
        $gameId,
        1,
        "combatant.initiative",
        ["combatant_id" => $combatantId, "initiative" => 17]
    );

    $repository->joinGame(
        5,
        (string) $created["invite_code"],
        $testCharacterIds[5]
    );
    $stateAfterLateJoin = $repository->getState($gameId, 1);
    $lateCharacterWasAdded = false;
    foreach ($stateAfterLateJoin["combatants"] as $entry) {
        if ((int) $entry["entity_id"] === $testCharacterIds[5]) {
            $lateCharacterWasAdded = true;
            break;
        }
    }
    assertGameSession(
        $lateCharacterWasAdded,
        "Un jugador unido durante el encuentro no se incorporó como combatiente."
    );
    $countBeforeRosterSync = count($stateAfterLateJoin["combatants"]);
    $commands->handle($gameId, 1, "encounter.sync_roster", []);
    $stateAfterRosterSync = $repository->getState($gameId, 1);
    assertGameSession(
        count($stateAfterRosterSync["combatants"]) === $countBeforeRosterSync,
        "La sincronizacion del grupo ha duplicado combatientes."
    );

    $commands->handle(
        $gameId,
        1,
        "combatant.hp",
        ["combatant_id" => $combatantId, "mode" => "damage", "amount" => 3]
    );
    $commands->handle(
        $gameId,
        1,
        "encounter.status",
        ["status" => "active"]
    );
    $commands->handle($gameId, 1, "turn.next", []);
    $commands->handle($gameId, 1, "turn.next", []);
    $commands->handle($gameId, 1, "turn.next", []);

    $commands->handle(
        $gameId,
        1,
        "custom_spell.create",
        [
            "name" => "Llama de integración",
            "description" => "Conjuro temporal.",
            "spell_level" => 1,
            "concentration" => true,
        ]
    );
    $commands->handle(
        $gameId,
        1,
        "npc.create",
        [
            "name" => "NPC temporal",
            "armor_class" => 13,
            "max_hp" => 9,
            "initiative_modifier" => 2,
        ]
    );

    $state = $repository->getState($gameId, 3);
    assertGameSession(count($state["members"]) === 3, "El estado no incluye al grupo.");
    assertGameSession(count($state["custom_spells"]) === 1, "Falta el conjuro propio.");
    assertGameSession(count($state["npcs"]) === 1, "Falta el NPC propio.");
    assertGameSession(
        (int) $state["encounter"]["round_number"] === 2,
        "El orden de turno no ha avanzado de ronda."
    );

    $playerWasRejected = false;
    try {
        $commands->handle(
            $gameId,
            3,
            "encounter.create",
            ["name" => "Acción no autorizada"]
        );
    } catch (RuntimeException $exception) {
        $playerWasRejected = true;
    }
    assertGameSession(
        $playerWasRejected,
        "Un jugador ha podido ejecutar una acción reservada al DM."
    );

    $socketToken = $repository->issueSocketToken($gameId, 3);
    $identity = $repository->authenticateSocketToken($socketToken);
    assertGameSession(
        is_array($identity) && (int) $identity["id_user"] === 3,
        "El token temporal del WebSocket no autentica al jugador."
    );

    echo "GameSessionIntegrationTest OK\n";
} finally {
    if ($gameId > 0) {
        $cleanup = $repository->pdo()->prepare(
            "DELETE FROM games WHERE id_game = :game"
        );
        $cleanup->execute([":game" => $gameId]);
    }
    foreach ($testCharacterIds as $characterId) {
        $cleanupCharacter = $repository->pdo()->prepare(
            "DELETE FROM chars WHERE id_char = :character"
        );
        $cleanupCharacter->execute([":character" => $characterId]);
    }
}
