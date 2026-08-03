<?php

require_once(__DIR__ . "/../classes/CompendiumRepository.php");
require_once(__DIR__ . "/../classes/BestiaryLocalizer.php");

function assertCompendium(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$monsters = CompendiumRepository::monsters();
$playableRaces = CompendiumRepository::playableRaces();
$referenceAncestries = CompendiumRepository::nonPlayableAncestries();
$classes = CompendiumRepository::classes();
$sourceBooks = CompendiumRepository::sourceBooks();

assertCompendium(count($monsters) === 334, "El bestiario SRD debe contener 334 criaturas.");
assertCompendium(count($playableRaces) === 52, "Faltan razas jugables.");
assertCompendium(count($referenceAncestries) === 52, "Faltan linajes no jugables.");
assertCompendium(count($classes) === 13, "Faltan clases del catálogo.");
assertCompendium(count($sourceBooks) >= 20, "El índice de manuales oficiales está incompleto.");

$monsterIndexes = array_column($monsters, "index");
$referenceNames = array_column($referenceAncestries, "name");
assertCompendium(
    count($monsterIndexes) === count(array_unique($monsterIndexes)),
    "Hay criaturas duplicadas en el bestiario."
);
assertCompendium(
    count($referenceNames) === count(array_unique($referenceNames)),
    "Hay linajes de referencia duplicados."
);

foreach ($monsters as $monster) {
    assertCompendium(
        isset(
            $monster["name"],
            $monster["type"],
            $monster["challengeRating"],
            $monster["armorClass"],
            $monster["hitPoints"]
        ),
        "Una criatura no contiene los datos mínimos de consulta."
    );
    assertCompendium(
        BestiaryLocalizer::hasName((string) $monster["index"]),
        "Falta el nombre en castellano de " . $monster["name"] . "."
    );
}

assertCompendium(
    BestiaryLocalizer::name($monsters[0]) === "Aboleth",
    "No se está aplicando el catálogo de nombres en castellano."
);
assertCompendium(
    BestiaryLocalizer::damageList(["fire", "cold"]) === "fuego, frío",
    "No se están traduciendo los tipos de daño."
);
assertCompendium(
    BestiaryLocalizer::languages("Common, Draconic") === "común, dracónico",
    "No se están traduciendo los idiomas."
);
assertCompendium(
    BestiaryLocalizer::distance("30 ft.") === "9 m",
    "No se están convirtiendo las distancias al sistema métrico."
);
assertCompendium(
    BestiaryLocalizer::name([
        "index" => "gibbering-mouther",
        "name" => "Gibbering Mouther",
    ]) === "Bocón barbotante",
    "No se está empleando la terminología oficial en castellano."
);

echo "CompendiumRepositoryTest OK\n";
