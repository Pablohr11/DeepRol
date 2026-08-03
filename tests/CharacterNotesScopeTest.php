<?php

require_once __DIR__ . "/../classes/DbConnector.php";

function assertCharacterNotes(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$db = DbConector::singleton();
$character = $db->getCharForUser(1, 1);
assertCharacterNotes((bool) $character, "Falta el personaje de prueba.");

$notes = $db->getNotes(1, 1);
assertCharacterNotes(
    array_keys($notes) === [1],
    "La consulta incluye anotaciones de otro personaje."
);
$otherCharacterNotes = $db->getNotes(1, 5);

foreach ($notes as $characterId => $characterNotes) {
    assertCharacterNotes(
        (int) $characterId === 1,
        "Se ha devuelto un grupo de anotaciones ajeno al personaje."
    );
    foreach ($characterNotes as $note) {
        assertCharacterNotes(
            (int) ($note["ID_User"] ?? 0) === 1,
            "Se ha devuelto una anotación perteneciente a otro usuario."
        );
    }
}

$_COOKIE["logged"] = 1;
$_GET["character_id"] = 1;
$_GET["framed"] = "true";

ob_start();
chdir(__DIR__ . "/../sections");
include "notes.php";
$html = (string) ob_get_clean();

if (!empty($notes[1][0]["Nombre"])) {
    assertCharacterNotes(
        strpos($html, htmlspecialchars((string) $notes[1][0]["Nombre"], ENT_QUOTES, "UTF-8")) !== false,
        "No se muestran las anotaciones del personaje solicitado."
    );
}
if (!empty($otherCharacterNotes[5][0]["Nombre"])) {
    assertCharacterNotes(
        strpos($html, htmlspecialchars((string) $otherCharacterNotes[5][0]["Nombre"], ENT_QUOTES, "UTF-8")) === false,
        "La pestaña muestra una anotación de otro personaje."
    );
}
assertCharacterNotes(
    strpos($html, "newNote.php?framed=true&amp;character_id=1") !== false,
    "La creación de anotaciones no conserva el personaje actual."
);

echo "CharacterNotesScopeTest OK\n";
