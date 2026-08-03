<?php

require_once __DIR__ . "/../classes/GameRules.php";

function assertGameRules(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

for ($index = 0; $index < 100; $index++) {
    $code = GameRules::generateInviteCode();
    assertGameRules(
        preg_match("/^[A-Z0-9]{6}$/", $code) === 1,
        "El código de invitación no cumple el formato."
    );
}

assertGameRules(
    GameRules::normalizeInviteCode(" k7-m2 xp ") === "K7M2XP",
    "La normalización del código no elimina separadores."
);
assertGameRules(
    GameRules::isValidInviteCode("k7m2xp"),
    "Un código alfanumérico de seis caracteres debería ser válido."
);
assertGameRules(
    !GameRules::isValidInviteCode("ABC12"),
    "Se ha aceptado un código con longitud incorrecta."
);

$next = GameRules::nextTurn(2, 4, 3);
assertGameRules(
    $next === ["turn_index" => 0, "round" => 5],
    "El cambio de ronda no reinicia el turno."
);
$sameRound = GameRules::nextTurn(0, 4, 3);
assertGameRules(
    $sameRound === ["turn_index" => 1, "round" => 4],
    "El avance de turno ha alterado la ronda antes de tiempo."
);

assertGameRules(
    GameRules::canControlCombatant(
        ["role" => "player", "id_user" => 3],
        ["owner_user_id" => 3]
    ),
    "El jugador no puede controlar su propio combatiente."
);
assertGameRules(
    !GameRules::canControlCombatant(
        ["role" => "player", "id_user" => 3],
        ["owner_user_id" => 1]
    ),
    "Un jugador puede controlar un combatiente ajeno."
);
assertGameRules(
    GameRules::canControlCombatant(
        ["role" => "dm", "id_user" => 1],
        ["owner_user_id" => 3]
    ),
    "El Dungeon Master no puede controlar la mesa."
);

echo "GameRulesTest OK\n";
