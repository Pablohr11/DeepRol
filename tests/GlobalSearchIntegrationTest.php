<?php

require_once(__DIR__ . "/../classes/DbConnector.php");
require_once(__DIR__ . "/../classes/GlobalSearchService.php");

function assertGlobalSearchIntegration(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$database = DbConector::singleton();
$characters = $database->getChars(1) ?: [];
assertGlobalSearchIntegration($characters !== [], "Falta un personaje local para la prueba.");

$characterName = (string) ($characters[0]["name"] ?? "");
$characterResults = $database->searchCharacters(1, $characterName, 5);
assertGlobalSearchIntegration(
    $characterResults !== []
        && (string) ($characterResults[0]["name"] ?? "") === $characterName,
    "La consulta global no encuentra los personajes del usuario."
);

$spellResults = $database->searchSpells("Druida", 5);
assertGlobalSearchIntegration(
    $spellResults !== [],
    "La consulta global no encuentra conjuros por clase o contenido."
);

$service = new GlobalSearchService($database);
$privatePayload = $service->search($characterName, 1, 5);
$privateGroupKeys = array_column($privatePayload["groups"], "key");
assertGlobalSearchIntegration(
    in_array("characters", $privateGroupKeys, true),
    "El servicio no incluye el personaje del usuario."
);

$guestPayload = $service->search($characterName, 0, 5);
$guestGroupKeys = array_column($guestPayload["groups"], "key");
assertGlobalSearchIntegration(
    !in_array("characters", $guestGroupKeys, true)
        && !in_array("notes", $guestGroupKeys, true),
    "El servicio expone resultados privados al invitado."
);

$_COOKIE["logged"] = 1;
$_GET["q"] = "dragón";
ob_start();
include(__DIR__ . "/../src/globalSearch.php");
$json = (string) ob_get_clean();
$endpointPayload = json_decode($json, true);

assertGlobalSearchIntegration(
    is_array($endpointPayload) && empty($endpointPayload["error"]),
    "El endpoint no devuelve JSON válido."
);
assertGlobalSearchIntegration(
    in_array("monsters", array_column($endpointPayload["groups"], "key"), true),
    "El endpoint no incluye resultados del bestiario."
);
$endpointSpellGroups = array_values(array_filter(
    $endpointPayload["groups"],
    static fn(array $group): bool => ($group["key"] ?? "") === "spells"
));
if ($endpointSpellGroups) {
    assertGlobalSearchIntegration(
        strpos(
            (string) ($endpointSpellGroups[0]["results"][0]["path"] ?? ""),
            "id_spell="
        ) !== false,
        "El endpoint genera una ruta de conjuro incompatible."
    );
}

$originalDirectory = getcwd();
chdir(__DIR__ . "/../sections");
ob_start();
include("search.php");
$searchHtml = (string) ob_get_clean();
chdir($originalDirectory);

assertGlobalSearchIntegration(
    strpos($searchHtml, "Búsqueda global") !== false
        && strpos($searchHtml, 'class="searchGroup"') !== false
        && strpos($searchHtml, "Bestiario") !== false,
    "La página global no renderiza los resultados agrupados."
);

echo "GlobalSearchIntegrationTest OK\n";
