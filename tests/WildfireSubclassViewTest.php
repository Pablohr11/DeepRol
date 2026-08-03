<?php

function assertWildfireView(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$_GET = [
    "class" => "Druida",
    "subclass" => "Círculo del Fuego Salvaje",
];

ob_start();
chdir(__DIR__ . "/../sections");
include "clase.php";
$html = (string) ob_get_clean();

foreach ([
    "Conjuros de círculo",
    "Invocar espíritu del fuego salvaje",
    "Vínculo mejorado",
    "Esfera de llamas · Rayo abrasador",
    "Crecimiento vegetal · Revivir",
    "Aura de vida · Escudo de fuego",
    "Llamas cauterizadoras",
    "Renacer ardiente",
    "Curar heridas · Manos ardientes",
    "2d10 + modificador de Sabiduría",
    "Espíritu del fuego salvaje",
    "Semilla de llamas",
    "Teletransporte ardiente",
] as $expectedText) {
    assertWildfireView(
        strpos($html, $expectedText) !== false,
        "La ficha no muestra: " . $expectedText
    );
}

assertWildfireView(
    substr_count($html, "Nivel 2") >= 1
        && substr_count($html, "Nivel 3") >= 1
        && substr_count($html, "Nivel 5") >= 1
        && substr_count($html, "Nivel 6") >= 1
        && substr_count($html, "Nivel 7") >= 1
        && substr_count($html, "Nivel 9") >= 1
        && substr_count($html, "Nivel 10") >= 1
        && substr_count($html, "Nivel 14") >= 1,
    "La ficha no presenta todos los niveles de subclase."
);
assertWildfireView(
    strpos($html, 'class="subclassProgressionFeature"') !== false,
    "La tabla 1–20 no integra los rasgos de la subclase."
);

echo "WildfireSubclassViewTest OK\n";
