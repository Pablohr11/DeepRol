<?php

require_once(__DIR__ . "/../classes/CharacterOptionCatalog.php");

function assertCatalog(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$classes = CharacterOptionCatalog::classes();
$races = CharacterOptionCatalog::races();
$classNames = array_column($classes, "name");
$raceNames = array_column($races, "name");

assertCatalog(count($classes) === 13, "El catálogo debe incluir las 13 clases de 5e 2014.");
assertCatalog(count($races) === 52, "El catálogo de razas y linajes está incompleto.");
assertCatalog(
    count($classNames) === count(array_unique($classNames)),
    "Hay clases duplicadas en el catálogo."
);
assertCatalog(
    count($raceNames) === count(array_unique($raceNames)),
    "Hay razas duplicadas en el catálogo."
);

$subclassCount = 0;
foreach ($classes as $class) {
    $subclasses = $class["subclasses"] ?? [];
    $subclassNames = array_column($subclasses, "name");
    $subclassCount += count($subclasses);
    assertCatalog(
        count($subclassNames) === count(array_unique($subclassNames)),
        "Hay subclases duplicadas para " . $class["name"] . "."
    );
}

$subraceCount = 0;
foreach ($races as $race) {
    $subraces = $race["subraces"] ?? [];
    $subraceNames = array_column($subraces, "name");
    $subraceCount += count($subraces);
    assertCatalog(
        count($subraceNames) === count(array_unique($subraceNames)),
        "Hay subrazas duplicadas para " . $race["name"] . "."
    );
}

assertCatalog($subclassCount === 118, "El catálogo oficial debe contener 118 subclases.");
assertCatalog($subraceCount === 73, "El catálogo debe contener 73 subrazas o variantes.");
assertCatalog(
    CharacterOptionCatalog::hasNamedOption(
        CharacterOptionCatalog::findClass("Guerrero")["subclasses"],
        "Caballero Arcano"
    ),
    "Falta la subclase Caballero Arcano."
);
assertCatalog(
    CharacterOptionCatalog::hasNamedOption(
        CharacterOptionCatalog::findRace("Elfo")["subraces"],
        "Elfo astral"
    ),
    "Falta la subraza Elfo astral."
);

echo "CharacterOptionCatalogTest OK\n";
