<?php

require_once(__DIR__ . "/../classes/SpellSlotProgression.php");

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message
            . "\nEsperado: " . var_export($expected, true)
            . "\nRecibido: " . var_export($actual, true)
        );
    }
}

assertSameValue(
    [1 => 4, 2 => 3, 3 => 3, 4 => 1],
    SpellSlotProgression::forCharacter("Druida", 7)["slots"],
    "La progresión de druida de nivel 7 no coincide."
);

assertSameValue(
    [1 => 4, 2 => 2],
    SpellSlotProgression::forCharacter("Paladín", 5)["slots"],
    "La progresión de paladín de nivel 5 no coincide."
);

$warlockLevelEleven = SpellSlotProgression::forCharacter("Brujo", 11);
assertSameValue(
    [5 => 3],
    $warlockLevelEleven["slots"],
    "La magia de pacto de brujo de nivel 11 no coincide."
);
assertSameValue(
    [6],
    $warlockLevelEleven["arcanum"],
    "El arcano místico de brujo de nivel 11 no coincide."
);

assertSameValue(
    [],
    SpellSlotProgression::forCharacter("Guerrero", 10)["slots"],
    "Un guerrero sin subclase no debe recibir espacios."
);

assertSameValue(
    [],
    SpellSlotProgression::forCharacter("Explorador", 1)["slots"],
    "Un explorador de nivel 1 todavía no tiene espacios."
);

assertSameValue(
    [1 => 2],
    SpellSlotProgression::forCharacter("Artífice", 1)["slots"],
    "Un artífice obtiene espacios de conjuro desde el nivel 1."
);

assertSameValue(
    [1 => 4, 2 => 3],
    SpellSlotProgression::forCharacter("Guerrero", 10, "Caballero Arcano")["slots"],
    "La progresión de Caballero Arcano de nivel 10 no coincide."
);

assertSameValue(
    [1 => 4, 2 => 2],
    SpellSlotProgression::forCharacter("Pícaro", 9, "Tramposo Arcano")["slots"],
    "La progresión de Tramposo Arcano de nivel 9 no coincide."
);

$multiclassCaster = SpellSlotProgression::forClasses([
    ["class_name" => "Mago", "level" => 3],
    ["class_name" => "Paladin", "level" => 4],
]);
assertSameValue(
    [1 => 4, 2 => 3, 3 => 2],
    $multiclassCaster["slots"],
    "Los espacios compartidos de Mago 3 / Paladín 4 no coinciden."
);

$pactMulticlass = SpellSlotProgression::forClasses([
    ["class_name" => "Hechicero", "level" => 4],
    ["class_name" => "Brujo", "level" => 3],
]);
assertSameValue(
    2,
    count($pactMulticlass["groups"]),
    "La multiclase con brujo debe separar espacios estándar y de pacto."
);
assertSameValue(
    [2 => 2],
    $pactMulticlass["groups"][1]["slots"],
    "Los espacios de pacto no se han conservado por separado."
);

echo "SpellSlotProgressionTest OK\n";
