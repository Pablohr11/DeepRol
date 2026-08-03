<?php

function assertThemeCoverage(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function themeHexToRgb(string $hex): array
{
    $value = ltrim(trim($hex), "#");
    if (strlen($value) === 3) {
        $value = $value[0] . $value[0]
            . $value[1] . $value[1]
            . $value[2] . $value[2];
    }

    return [
        hexdec(substr($value, 0, 2)),
        hexdec(substr($value, 2, 2)),
        hexdec(substr($value, 4, 2)),
    ];
}

function themeRelativeLuminance(string $hex): float
{
    $channels = array_map(
        static function (int $channel): float {
            $value = $channel / 255;
            return $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        },
        themeHexToRgb($hex)
    );

    return 0.2126 * $channels[0]
        + 0.7152 * $channels[1]
        + 0.0722 * $channels[2];
}

function themeContrastRatio(string $foreground, string $background): float
{
    $first = themeRelativeLuminance($foreground);
    $second = themeRelativeLuminance($background);
    $lighter = max($first, $second);
    $darker = min($first, $second);

    return ($lighter + 0.05) / ($darker + 0.05);
}

$projectRoot = dirname(__DIR__);
$pageFiles = [
    $projectRoot . "/index.php",
    $projectRoot . "/login.php",
    $projectRoot . "/pruebas.php",
];
$sectionIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $projectRoot . "/sections",
        FilesystemIterator::SKIP_DOTS
    )
);
foreach ($sectionIterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === "php") {
        $pageFiles[] = $file->getPathname();
    }
}

$checkedPages = 0;
foreach ($pageFiles as $pageFile) {
    $source = (string) file_get_contents($pageFile);
    if (
        stripos($source, "<html") === false
        && stripos($source, "<!DOCTYPE") === false
    ) {
        continue;
    }

    $checkedPages++;
    assertThemeCoverage(
        strpos($source, "theme.css") !== false,
        basename($pageFile) . " no carga el sistema visual compartido."
    );
    assertThemeCoverage(
        strpos($source, "theme.js") !== false,
        basename($pageFile) . " no aplica el tema correspondiente a la clase."
    );

    preg_match_all(
        '/<link\b[^>]*href=["\'][^"\']+\.css[^"\']*["\'][^>]*>/i',
        $source,
        $styleMatches
    );
    $styles = $styleMatches[0] ?? [];
    if (count($styles) > 1) {
        $lastLocalStyle = end($styles);
        assertThemeCoverage(
            strpos((string) $lastLocalStyle, "theme.css") !== false,
            basename($pageFile) . " carga estilos después del tema global."
        );
    }
}

assertThemeCoverage($checkedPages >= 15, "No se han revisado todas las vistas de la aplicación.");

$themeCss = (string) file_get_contents($projectRoot . "/styles/theme.css");
assertThemeCoverage(
    substr_count($themeCss, "{") === substr_count($themeCss, "}"),
    "Las reglas del tema global no tienen las llaves equilibradas."
);
foreach (
    [
        "--theme-display-font",
        "--theme-body-font",
        "--theme-accent-wash-strong",
        'data-theme="druida"',
        'data-theme="brujo"',
        'data-color-mode="light"',
        ".appearanceModeButton",
        ".compendiumPage",
        ".characterBuilder",
        ".characterUpdateDialog",
        ".ql-editor",
        ".landingMain",
        ".authPanel",
    ] as $requiredToken
) {
    assertThemeCoverage(
        strpos($themeCss, $requiredToken) !== false,
        "El tema global no cubre {$requiredToken}."
    );
}

preg_match(
    '/:root\[data-theme\]\[data-color-mode="light"\]\s*\{(?P<block>.*?)\}/s',
    $themeCss,
    $lightThemeMatches
);
$lightThemeBlock = (string) ($lightThemeMatches["block"] ?? "");
$lightPalette = [];
foreach (["theme-text", "theme-readable-muted", "theme-panel-strong"] as $variable) {
    preg_match(
        '/--' . preg_quote($variable, '/') . ':\s*(#[0-9a-f]{6})/i',
        $lightThemeBlock,
        $valueMatches
    );
    $lightPalette[$variable] = (string) ($valueMatches[1] ?? "");
    assertThemeCoverage(
        $lightPalette[$variable] !== "",
        "El modo claro no define --{$variable} como color verificable."
    );
}

assertThemeCoverage(
    themeContrastRatio(
        $lightPalette["theme-text"],
        $lightPalette["theme-panel-strong"]
    ) >= 7,
    "El texto principal del modo claro no alcanza contraste AAA."
);
assertThemeCoverage(
    themeContrastRatio(
        $lightPalette["theme-readable-muted"],
        $lightPalette["theme-panel-strong"]
    ) >= 4.5,
    "El texto secundario del modo claro no alcanza contraste AA."
);

$classThemes = [
    "arcano",
    "artifice",
    "barbaro",
    "bardo",
    "brujo",
    "clerigo",
    "druida",
    "explorador",
    "guerrero",
    "hechicero",
    "mago",
    "monje",
    "paladin",
    "picaro",
    "sangre",
];
$classAccentColors = [];
foreach ($classThemes as $classTheme) {
    preg_match(
        '/:root\[data-theme="' . preg_quote($classTheme, '/') . '"\]\s*\{(?P<block>.*?)\}/s',
        $themeCss,
        $classThemeMatches
    );
    $classThemeBlock = (string) ($classThemeMatches["block"] ?? "");
    preg_match(
        '/--theme-accent:\s*(#[0-9a-f]{6})/i',
        $classThemeBlock,
        $accentMatches
    );
    $classAccent = strtolower((string) ($accentMatches[1] ?? ""));
    assertThemeCoverage(
        $classAccent !== "",
        "La clase {$classTheme} no define un color de acento."
    );
    $classAccentColors[] = $classAccent;
}
assertThemeCoverage(
    count(array_unique($classAccentColors)) === count($classThemes),
    "Cada clase debe conservar un color de acento propio."
);

$themeJs = (string) file_get_contents($projectRoot . "/scripts/theme.js");
foreach (
    [
        "deeprol.colorMode",
        "getColorMode",
        "setColorMode",
        "toggleColorMode",
        "deeprol:colormodechange",
    ] as $requiredToken
) {
    assertThemeCoverage(
        strpos($themeJs, $requiredToken) !== false,
        "El controlador visual no implementa {$requiredToken}."
    );
}

$indexJs = (string) file_get_contents($projectRoot . "/scripts/index.js");
assertThemeCoverage(
    strpos($indexJs, "frameRoot.dataset.colorMode") !== false
    && strpos($indexJs, "deeprol:colormodechange") !== false,
    "El shell no sincroniza el modo claro/oscuro con el iframe."
);

foreach (
    [
        $projectRoot . "/sections/_partials/header.php",
        $projectRoot . "/sections/landing.php",
        $projectRoot . "/login.php",
    ] as $toggleView
) {
    assertThemeCoverage(
        strpos((string) file_get_contents($toggleView), "data-color-mode-toggle") !== false,
        basename($toggleView) . " no ofrece el selector de tema claro/oscuro."
    );
}

echo "ThemeCoverageTest OK ({$checkedPages} vistas)\n";
