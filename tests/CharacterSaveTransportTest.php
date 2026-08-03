<?php

function assertCharacterSaveTransport(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$endpoint = file_get_contents(__DIR__ . "/../src/updateCharacterSheet.php");
$client = file_get_contents(__DIR__ . "/../scripts/char.js");

assertCharacterSaveTransport(
    is_string($endpoint)
        && strpos($endpoint, "ob_clean();") !== false
        && strpos($endpoint, 'ini_set("display_errors", "0");') !== false,
    "El endpoint no protege la respuesta JSON frente a avisos de subida."
);
assertCharacterSaveTransport(
    is_string($client)
        && strpos($client, "const responseText = await response.text();") !== false
        && strpos($client, "responseText.indexOf('{\"ok\":')") !== false,
    "El cliente no puede recuperar una respuesta JSON precedida por avisos."
);
assertCharacterSaveTransport(
    strpos($client, "pdfFontSizeByField") !== false
        && strpos($client, "textField.setFontSize(pdfFontSizeByField[name]);") !== false,
    "La regeneración del PDF no controla el tamaño de los campos extensos."
);

echo "CharacterSaveTransportTest OK\n";
