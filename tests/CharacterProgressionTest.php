<?php

require_once __DIR__ . "/../classes/CharacterProgression.php";

function assertProgression($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message
            . "\nEsperado: " . var_export($expected, true)
            . "\nRecibido: " . var_export($actual, true)
        );
    }
}

$classes = [
    [
        "class_name" => "Druida",
        "class_label" => "Druida",
        "subclass_name" => "Círculo de la Luna",
        "level" => 5,
    ],
    [
        "class_name" => "Guerrero",
        "class_label" => "Guerrero",
        "subclass_name" => "Maestro de Batalla",
        "level" => 4,
    ],
];

assertProgression(9, CharacterProgression::totalLevel($classes), "El nivel total multiclase es incorrecto.");
assertProgression(4, CharacterProgression::proficiencyBonus(9), "El bono de competencia es incorrecto.");
assertProgression(
    "5d8 + 4d10",
    CharacterProgression::hitDiceSummary($classes),
    "Los dados de golpe por clase son incorrectos."
);
assertProgression(
    2,
    CharacterProgression::abilityScoreImprovementCount($classes),
    "No se han contabilizado las mejoras de característica por clase."
);

$fields = CharacterProgression::deriveSheetFields(
    [
        "STR" => "16",
        "DEX" => "12",
        "CON" => "14",
        "INT" => "10",
        "WIS" => "18",
        "CHA" => "8",
        "_otherProficiencies" => "Armaduras ligeras",
    ],
    $classes,
    ["int", "wis"],
    ["perception" => 2, "athletics" => 1],
    ["Común", "Élfico", "común"]
);

assertProgression("+8", $fields["ST Wisdom"], "La salvación no se recalculó con el nivel total.");
assertProgression("+12", $fields["Perception "], "La pericia no se recalculó con el nivel total.");
assertProgression("+7", $fields["Athletics"], "La habilidad competente no se recalculó.");
assertProgression("22", $fields["Passive"], "La percepción pasiva es incorrecta.");
assertProgression(
    ["Común", "Élfico"],
    $fields["_languages"],
    "Los idiomas no se han normalizado."
);
assertProgression(
    "Armaduras ligeras\n\nIdiomas: Común, Élfico",
    $fields["ProficienciesLang"],
    "Los idiomas no se han incorporado a la ficha."
);

echo "CharacterProgressionTest OK\n";
