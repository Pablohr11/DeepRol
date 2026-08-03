<?php

require_once __DIR__ . "/../classes/CharacterOptionCatalog.php";
require_once __DIR__ . "/../classes/ClassDetailCatalog.php";

function assertClassDetail(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$classes = CharacterOptionCatalog::classes();
assertClassDetail(count($classes) === 13, "El catálogo no contiene las trece clases.");
foreach ($classes as $class) {
    $name = (string) ($class["name"] ?? "");
    $profile = ClassDetailCatalog::profile($name);
    assertClassDetail(
        (string) ($profile["role"] ?? "") !== "",
        "Falta la descripción de " . $name . "."
    );
    assertClassDetail(
        count($profile["features"] ?? []) > 0,
        "Falta la progresión de " . $name . "."
    );
    foreach (($class["subclasses"] ?? []) as $subclass) {
        $summary = ClassDetailCatalog::subclassSummary(
            $name,
            (string) ($subclass["name"] ?? ""),
            (string) ($subclass["source"] ?? "")
        );
        assertClassDetail(
            mb_strlen($summary) > 40,
            "Falta la descripción de una subclase de " . $name . "."
        );
    }
}

$wildfire = ClassDetailCatalog::subclassDetail(
    "Druida",
    "Círculo del Fuego Salvaje"
);
assertClassDetail(
    count($wildfire["features"] ?? []) === 9,
    "El Círculo del Fuego Salvaje no contiene todos sus rasgos y conjuros detallados."
);
assertClassDetail(
    array_values(array_unique(array_map(
        static function (array $feature): int {
            return (int) ($feature["level"] ?? 0);
        },
        $wildfire["features"] ?? []
    ))) === [2, 3, 5, 6, 7, 9, 10, 14],
    "La progresión detallada del Fuego Salvaje omite niveles con ganancias."
);
assertClassDetail(
    count($wildfire["companions"][0]["actions"] ?? []) === 2,
    "Falta el perfil operativo del espíritu del fuego salvaje."
);

$_GET = ["class" => "Mago"];
ob_start();
chdir(__DIR__ . "/../sections");
include "clase.php";
$html = (string) ob_get_clean();

assertClassDetail(
    strpos($html, "Tabla de progresión") !== false,
    "La vista de clase no muestra la tabla de progresión."
);
assertClassDetail(
    strpos($html, "Espacios de nivel 9") !== false,
    "La tabla del mago no alcanza los espacios de nivel 9."
);
assertClassDetail(
    strpos($html, "Subclases de Mago") !== false,
    "La ficha no enlaza sus subclases."
);

echo "ClassDetailViewTest OK\n";
