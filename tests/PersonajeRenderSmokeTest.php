<?php

$_COOKIE["logged"] = 1;
$_GET["id"] = 1;
$_SERVER["HTTPS"] = "off";

ob_start();
chdir(__DIR__ . "/../sections");
include "personaje.php";
$html = (string) ob_get_clean();

foreach (
    [
        'id="characterUpdateModal"',
        'id="characterFieldsForm"',
        'id="characterPdfForm"',
        "Actualizar Draelith",
        "notes.php?framed=true&amp;character_id=1",
    ] as $expected
) {
    if (strpos($html, $expected) === false) {
        throw new RuntimeException("No aparece {$expected} en la ficha renderizada.");
    }
}

echo "PersonajeRenderSmokeTest OK\n";
