<?php

require_once(__DIR__ . "/../classes/GlobalSearchService.php");

function assertGlobalSearch(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

final class GlobalSearchFakeDatabase
{
    public $characterUserIds = [];
    public $noteUserIds = [];

    public function searchCharacters($userId, $query, $limit): array
    {
        $this->characterUserIds[] = (int) $userId;
        return [[
            "id_char" => 41,
            "name" => "Raíz Serena",
            "raza" => "Elfo",
            "subraza" => "Elfo de los bosques",
            "nivel" => 7,
            "clase" => "Druida",
            "subclase" => "Círculo de la Tierra",
        ]];
    }

    public function searchSpells($query, $limit): array
    {
        return [[
            "id_spell" => 9,
            "name" => "Druidismo",
            "level" => "Truco",
            "escuela" => "Transmutación",
            "clases" => "Druida",
            "descr" => "Una señal menor de la naturaleza.",
        ]];
    }

    public function searchNotes($userId, $query, $limit): array
    {
        $this->noteUserIds[] = (int) $userId;
        return [[
            "ID" => 12,
            "Nombre" => "Ritual druida",
            "Value" => "<p>Preparar el círculo antes del alba.</p>",
            "Date" => "2026-07-27",
            "RelatedChar" => 41,
            "character_name" => "Raíz Serena",
        ]];
    }
}

function groupByKey(array $payload, string $key): ?array
{
    foreach ($payload["groups"] as $group) {
        if (($group["key"] ?? "") === $key) {
            return $group;
        }
    }

    return null;
}

$database = new GlobalSearchFakeDatabase();
$service = new GlobalSearchService($database);
$payload = $service->search("  druida  ", 7, 5);

assertGlobalSearch($payload["query"] === "druida", "La consulta no se normaliza.");
assertGlobalSearch(
    groupByKey($payload, "characters") !== null,
    "No se incluyen los personajes del usuario."
);
assertGlobalSearch(
    groupByKey($payload, "spells") !== null,
    "No se incluyen los conjuros."
);
$spellGroup = groupByKey($payload, "spells");
assertGlobalSearch(
    strpos((string) ($spellGroup["results"][0]["path"] ?? ""), "id_spell=") !== false,
    "El resultado de conjuro no usa el parámetro que espera la vista."
);
assertGlobalSearch(
    groupByKey($payload, "classes") !== null,
    "No se incluyen clases o subclases."
);
assertGlobalSearch(
    groupByKey($payload, "notes") !== null,
    "No se incluyen los apuntes del usuario."
);
assertGlobalSearch(
    $database->characterUserIds === [7] && $database->noteUserIds === [7],
    "La búsqueda privada no respeta el usuario actual."
);

$guestDatabase = new GlobalSearchFakeDatabase();
$guestPayload = (new GlobalSearchService($guestDatabase))->search("druida", 0, 5);
assertGlobalSearch(
    groupByKey($guestPayload, "characters") === null
        && groupByKey($guestPayload, "notes") === null,
    "El invitado recibe resultados privados."
);
assertGlobalSearch(
    $guestDatabase->characterUserIds === [] && $guestDatabase->noteUserIds === [],
    "La búsqueda invitada ha consultado datos privados."
);

$monsterPayload = $service->search("dragón negro adulto", 0, 5);
$monsterGroup = groupByKey($monsterPayload, "monsters");
assertGlobalSearch($monsterGroup !== null, "No se consulta el bestiario.");
assertGlobalSearch(
    ($monsterGroup["results"][0]["title"] ?? "") === "Dragón negro adulto",
    "El resultado del bestiario no está localizado al castellano."
);

$ancestryPayload = $service->search("elfo astral", 0, 5);
$ancestryGroup = groupByKey($ancestryPayload, "ancestries");
assertGlobalSearch($ancestryGroup !== null, "No se consultan subrazas y linajes.");

foreach ([$payload, $guestPayload, $monsterPayload, $ancestryPayload] as $searchPayload) {
    foreach ($searchPayload["groups"] as $group) {
        foreach ($group["results"] as $result) {
            $path = (string) ($result["path"] ?? "");
            assertGlobalSearch(
                $path !== ""
                    && strpos($path, "..") === false
                    && !preg_match('/^[a-z]+:/i', $path),
                "La búsqueda ha generado una ruta no segura."
            );
        }
    }
}

$shortPayload = $service->search("a", 7, 5);
assertGlobalSearch(
    $shortPayload["groups"] === [] && $shortPayload["total"] === 0,
    "Las consultas demasiado cortas deberían ignorarse."
);

echo "GlobalSearchServiceTest OK\n";
