<?php

require_once __DIR__ . "/../classes/CharacterSheetUpdater.php";

function assertSheetUpdate(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$submitted = CharacterSheetUpdater::decodeSubmittedFields(json_encode([
    "STR" => "16",
    "DEX" => "12",
    "WIS" => "18",
    "AC" => "17",
    "Perception " => "+7",
    "Background" => "Ermitaño",
    "Campo inventado" => "No debe persistir",
]));

assertSheetUpdate(
    !isset($submitted["Campo inventado"]),
    "El actualizador ha aceptado un campo que no pertenece a la ficha."
);

$composed = CharacterSheetUpdater::compose([], $submitted, [
    "character_name" => "Draelith",
    "class_name" => "Druida",
    "class_label" => "Druida",
    "subclass_name" => "Círculo de la Luna",
    "race_name" => "Elfo",
    "race_label" => "Elfo",
    "subrace_name" => "Elfo de los bosques",
    "level" => 7,
]);

assertSheetUpdate(
    $composed["ClassLevel"] === "Druida · Círculo de la Luna/7",
    "No se ha compuesto correctamente la clase y el nivel."
);
assertSheetUpdate(
    $composed["Race "] === "Elfo · Elfo de los bosques",
    "No se ha compuesto correctamente la raza y subraza."
);
assertSheetUpdate($composed["STRmod"] === "+3", "El modificador de Fuerza es incorrecto.");
assertSheetUpdate($composed["DEXmod "] === "+1", "El modificador de Destreza es incorrecto.");
assertSheetUpdate($composed["ProfBonus"] === "+3", "El bono de competencia es incorrecto.");
assertSheetUpdate($composed["Passive"] === "17", "La percepción pasiva es incorrecta.");
assertSheetUpdate($composed["HD"] === "7d8", "Los dados de golpe del druida son incorrectos.");
assertSheetUpdate($composed["SpellSaveDC  2"] === "15", "La CD de conjuros es incorrecta.");

$invalidRangeRejected = false;
try {
    CharacterSheetUpdater::decodeSubmittedFields('{"STR":"31"}');
} catch (InvalidArgumentException $exception) {
    $invalidRangeRejected = true;
}
assertSheetUpdate($invalidRangeRejected, "Se ha aceptado una característica fuera de rango.");

$pdfFields = CharacterSheetUpdater::decodePdfFields(json_encode([
    "HPMax" => "52",
    "_pdfCheckboxes" => ["Check Box 11", "Check Box 11", "../../malicioso"],
]));
assertSheetUpdate($pdfFields["HPMax"] === "52", "No se ha importado la vida máxima del PDF.");
assertSheetUpdate(
    $pdfFields["_pdfCheckboxes"] === ["Check Box 11"],
    "Las casillas del PDF no se han filtrado correctamente."
);

echo "CharacterSheetUpdaterTest OK\n";
