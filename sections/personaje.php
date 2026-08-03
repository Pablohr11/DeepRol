<?php

require_once("../classes/DbConnector.php");
require_once("../classes/CharacterOptionCatalog.php");
require_once("../classes/SpellSlotProgression.php");
require_once("../classes/CharacterProgression.php");

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
if ($userId <= 0) {
    header("Location: ../login.php");
    exit;
}

function issueCharacterUpdateCsrfToken(string $cookieName): string
{
    $token = bin2hex(random_bytes(24));
    setcookie($cookieName, $token, [
        "expires" => time() + 7200,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Strict",
    ]);

    return $token;
}

$charId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$charData = null;
$charSpells = [];
$grouped = [];
$spellLoadFailed = false;

try {
    if ($charId > 0) {
        $db = DbConector::singleton();
        $charData = $db->getCharForUser($charId, $userId);
    }
} catch (Throwable $exception) {
    $charData = null;
}

if ($charData) {
    try {
        $rawSpellIds = (string) $db->getSpellsIds($charId);
        preg_match_all('/\d+/', $rawSpellIds, $spellIdMatches);
        $safeSpellIds = array_values(array_unique(array_map('intval', $spellIdMatches[0] ?? [])));

        if ($safeSpellIds) {
            $loadedSpells = $db->getSpells(implode(',', $safeSpellIds), "yes");
            $charSpells = is_array($loadedSpells) ? $loadedSpells : [];
        }
    } catch (Throwable $exception) {
        $charSpells = [];
        $spellLoadFailed = true;
    }
}

foreach ($charSpells as $spell) {
    $rawSpellLevel = trim((string) ($spell["level"] ?? ""));
    $spellLevel = null;

    if (stripos($rawSpellLevel, "truco") !== false) {
        $spellLevel = 0;
    } elseif (preg_match('/\d+/', $rawSpellLevel, $spellLevelMatch)) {
        $parsedSpellLevel = (int) $spellLevelMatch[0];
        if ($parsedSpellLevel >= 0 && $parsedSpellLevel <= 9) {
            $spellLevel = $parsedSpellLevel;
        }
    }

    if ($spellLevel !== null) {
        $grouped[$spellLevel][] = $spell;
    }
}

ksort($grouped, SORT_NUMERIC);

if (!$charData) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Personaje no disponible · DeepRol</title>
        <script src="../scripts/theme.js"></script>
        <link rel="stylesheet" href="../styles/char.css">
        <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    </head>
    <body>
        <main class="characterError">
            <span aria-hidden="true">☾</span>
            <h1>Personaje no disponible</h1>
            <p>No hemos podido abrir esta ficha de personaje.</p>
            <a href="personajes.php">Volver a personajes</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$updateCsrfCookieName = "deeprol_character_update_csrf";
$updateCsrfToken = isset($_COOKIE[$updateCsrfCookieName])
    && preg_match("/^[a-f0-9]{48}$/", (string) $_COOKIE[$updateCsrfCookieName])
        ? (string) $_COOKIE[$updateCsrfCookieName]
        : issueCharacterUpdateCsrfToken($updateCsrfCookieName);
$characterCatalog = CharacterOptionCatalog::all();
$characterName = (string) $charData["name"];
$characterDirectory = basename($characterName);
$characterDirectoryPath = dirname(__DIR__)
    . DIRECTORY_SEPARATOR . "resources"
    . DIRECTORY_SEPARATOR . "chars"
    . DIRECTORY_SEPARATOR . $characterDirectory;
$configuredPdfFilename = basename((string) ($charData["pdf_path"] ?? ""));
$configuredPdfPath = $configuredPdfFilename !== ""
    ? $characterDirectoryPath . DIRECTORY_SEPARATOR . $configuredPdfFilename
    : "";
$defaultPdfPath = $characterDirectoryPath . DIRECTORY_SEPARATOR . "ficha.pdf";
$pdfFilename = is_file($configuredPdfPath)
    ? $configuredPdfFilename
    : (is_file($defaultPdfPath) ? "ficha.pdf" : "");
$hasPdf = $pdfFilename !== "";
$portraitFilename = basename((string) ($charData["full_body_image_path"] ?? ""));
$portraitPath = $portraitFilename !== ""
    ? $characterDirectoryPath . DIRECTORY_SEPARATOR . $portraitFilename
    : "";
$hasPortrait = is_file($portraitPath);
$encodedCharacterDirectory = rawurlencode($characterDirectory);
$pdfUrl = $hasPdf
    ? "../resources/chars/{$encodedCharacterDirectory}/"
        . rawurlencode($pdfFilename)
        . "?v="
        . (int) filemtime($characterDirectoryPath . DIRECTORY_SEPARATOR . $pdfFilename)
    : "";
$portraitUrl = $hasPortrait
    ? "../resources/chars/{$encodedCharacterDirectory}/" . rawurlencode($portraitFilename)
    : "";
$sheetFallback = [];
$sheetFallbackPath = $characterDirectoryPath . DIRECTORY_SEPARATOR . "sheet.json";

if (is_readable($sheetFallbackPath)) {
    $decodedSheetFallback = json_decode((string) file_get_contents($sheetFallbackPath), true);
    if (is_array($decodedSheetFallback)) {
        $sheetFallback = $decodedSheetFallback;
    }
}

$characterRaceName = trim((string) ($charData["raza"] ?? ""));
$characterSubraceName = trim((string) ($charData["subraza"] ?? ""));
$characterRaceOption = CharacterOptionCatalog::findRace($characterRaceName);
$characterRaceDisplayName = trim((string) (
    $characterRaceOption["label"] ?? $characterRaceName
));
$characterClasses = [];
$characterLanguages = [];
try {
    $characterClasses = $db->getCharacterClasses($charId);
    $characterLanguages = $db->getCharacterLanguages($charId);
} catch (Throwable $exception) {
    $characterClasses = [];
    $characterLanguages = [];
}

if (!$characterClasses && isset($sheetFallback["_characterClasses"]) && is_array($sheetFallback["_characterClasses"])) {
    $characterClasses = $sheetFallback["_characterClasses"];
}
if (!$characterClasses) {
    $characterClasses = [[
        "class_name" => trim((string) ($charData["clase"] ?? "")),
        "subclass_name" => trim((string) ($charData["subclase"] ?? "")),
        "level" => max(1, min(20, (int) ($charData["nivel"] ?? 1))),
        "is_primary" => true,
    ]];
}

foreach ($characterClasses as &$characterClass) {
    $classOption = CharacterOptionCatalog::findClass((string) ($characterClass["class_name"] ?? ""));
    $characterClass["class_label"] = trim((string) (
        $classOption["label"] ?? $characterClass["class_name"] ?? ""
    ));
}
unset($characterClass);
$characterClasses = CharacterProgression::normalizeClasses($characterClasses);
$primaryCharacterClass = $characterClasses[0];
$characterClassName = $primaryCharacterClass["class_name"];
$characterSubclassName = $primaryCharacterClass["subclass_name"];
$characterClassDisplayName = $primaryCharacterClass["class_label"];
$characterClassOption = CharacterOptionCatalog::findClass($characterClassName);
$characterLevel = CharacterProgression::totalLevel($characterClasses);
$characterClassSummary = CharacterProgression::classSummary($characterClasses);
$characterAsiCount = CharacterProgression::abilityScoreImprovementCount($characterClasses);
if (!$characterLanguages && isset($sheetFallback["_languages"]) && is_array($sheetFallback["_languages"])) {
    $characterLanguages = CharacterProgression::normalizeLanguages($sheetFallback["_languages"]);
}

if (
    !isset($sheetFallback["Race "])
    && $characterRaceName !== ""
) {
    $sheetFallback["Race "] = $characterRaceDisplayName
        . ($characterSubraceName !== "" ? " · " . $characterSubraceName : "");
}

if (
    !isset($sheetFallback["ClassLevel"])
    && $characterClassName !== ""
) {
    $sheetFallback["ClassLevel"] = $characterClassSummary;
}

$sheetFallback["CharacterName"] = (string) (
    $sheetFallback["CharacterName"] ?? $characterName
);
$sheetFallback["CharacterName 2"] = (string) (
    $sheetFallback["CharacterName 2"] ?? $characterName
);

$spellProgression = SpellSlotProgression::forClasses($characterClasses);
$spellProgressionGroups = (array) ($spellProgression["groups"] ?? []);
$characterSavingThrowProficiencies = CharacterProgression::inferSavingThrowProficiencies($sheetFallback);
$characterSkillProficiencies = CharacterProgression::inferSkillProficiencies(
    $sheetFallback,
    $characterLevel
);
$characterOtherProficiencies = trim((string) (
    $sheetFallback["_otherProficiencies"] ?? $sheetFallback["ProficienciesLang"] ?? ""
));
$sheetStatusClass = $hasPdf ? "isLoading" : ($sheetFallback ? "isFallback" : "isError");
$sheetStatusLabel = $hasPdf
    ? ($sheetFallback ? "Datos locales disponibles" : "Cargando ficha")
    : ($sheetFallback ? "Datos básicos disponibles" : "Ficha PDF no disponible");
?>

<!DOCTYPE html>
<html lang="es" data-character-class="<?= e($characterClassName) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title><?= e($characterName) ?> · DeepRol</title>
    <script>
        window.DeepRolCharacterSheet = <?= json_encode(
            [
                "pdfUrl" => $pdfUrl,
                "fields" => $sheetFallback,
                "update" => [
                    "endpoint" => "../src/updateCharacterSheet.php",
                    "characterId" => $charId,
                    "csrfToken" => $updateCsrfToken,
                    "hasPdf" => $hasPdf,
                    "catalog" => $characterCatalog,
                    "metadata" => [
                        "className" => $characterClassName,
                        "subclassName" => $characterSubclassName,
                        "raceName" => $characterRaceName,
                        "subraceName" => $characterSubraceName,
                        "level" => $characterLevel,
                        "classes" => $characterClasses,
                        "languages" => $characterLanguages,
                        "savingThrowProficiencies" => $characterSavingThrowProficiencies,
                        "skillProficiencies" => $characterSkillProficiencies,
                        "otherProficiencies" => $characterOtherProficiencies,
                    ],
                ],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ) ?>;
    </script>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/index.css">
    <link rel="stylesheet" href="../styles/char.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script src="../scripts/vendor/pdf-lib/pdf-lib.min.js"></script>
    <script src="../scripts/char.js"></script>
</head>
<body>
<div class="mist"></div>
<div class="contenedor-linterna" id="contenedor-linterna">
    <div id="gancho"></div>
    <div class="linterna" id="linterna"></div>
    <div class="haz-de-luz" id="haz-de-luz"></div>
</div>

<div class="characterPage">
    <nav class="characterBreadcrumb" aria-label="Migas de pan">
        <a href="personajes.php">← Personajes</a>
        <span>/</span>
        <strong><?= e($characterName) ?></strong>
        <span class="characterReady <?= e($sheetStatusClass) ?>" id="sheetStatus">
            <i></i>
            <span><?= e($sheetStatusLabel) ?></span>
        </span>
    </nav>

<div id="charDiv">
    <!-- //? LEFT CONTAINER  -->
    <aside class="portraitColumn">
        <div class="card">
            <span class="portraitRune" aria-hidden="true">✥</span>
            <?php if ($portraitUrl !== "") { ?>
                <img src="<?= e($portraitUrl) ?>" id="fullBodyImg" alt="Retrato de <?= e($characterName) ?>" class="personaje">
            <?php } else { ?>
                <span class="fullBodyFallback personaje" id="fullBodyImg" aria-hidden="true"><?= e(substr($characterName, 0, 1)) ?></span>
            <?php } ?>
            <span class="portraitName"><?= e($characterName) ?></span>
        </div>
        <div id="sheetButtons">
            <button
                type="button"
                id="showPdfButton"
                <?= $hasPdf ? "" : "disabled" ?>
                title="<?= $hasPdf ? "Abrir la ficha original" : "Este personaje no tiene una ficha PDF disponible" ?>"
            >
                <span aria-hidden="true">⌁</span> Ver ficha PDF
            </button>
            <button type="button" id="updatePdfButton" title="Editar o importar la ficha">Actualizar</button>
        </div>
        <div id="stContainer">
            <span class="panelKicker">Defensas</span>
            <h3>Tiradas de salvación</h3>
            <div class="stDiv">
                <span class="stValue" id="ST-Strength">—</span>
                <span class="stInfo">Fuerza</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Dexterity">—</span>
                <span class="stInfo">Destreza</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Constitution">—</span>
                <span class="stInfo">Constitucion</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Intelligence">—</span>
                <span class="stInfo">Inteligencia</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Wisdom">—</span>
                <span class="stInfo">Sabiduria</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Charisma">—</span>
                <span class="stInfo">Carisma</span>
            </div>
        </div>
    </aside>

    <!-- //? MAIN CONTENT -->
    <main class="charInfo">
        <div id="topContent">
            <section class="coreStatsPanel">
                <div class="headerDisplay">
                    <div>
                        <span class="panelKicker">Hoja de personaje</span>
                        <h1><?= e($characterName) ?></h1>
                        <span id="charSubTitle">
                            <span id="Race">—</span> / <span id="ClassLevel">—</span> / <span id="Background">—</span>
                        </span>
                    </div>
                    <div class="HPACDiv">
                        <div class="armorStat">
                            <div>
                                <h3>Clase de armadura</h3>
                                <span class="stat" id="AC">—</span>
                            </div>
                        </div>
                        <div class="healthStat">
                            <div>
                                <h3>Puntos Vida</h3>
                                <span class="stat" id="HPMax">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!$hasPdf) { ?>
                    <div class="sheetNotice">
                        <span aria-hidden="true">!</span>
                        <p>
                            <strong>Ficha PDF no disponible.</strong>
                            Se muestran los datos básicos almacenados para este personaje.
                        </p>
                    </div>
                <?php } ?>

                <section class="characterProgressSummary" aria-label="Progresión del personaje">
                    <div>
                        <span class="panelKicker">Progresión</span>
                        <div class="characterClassBadges">
                            <?php foreach ($characterClasses as $classIndex => $characterClass): ?>
                                <span class="characterClassBadge <?= $classIndex === 0 ? "isPrimary" : "" ?>">
                                    <strong><?= e($characterClass["class_label"]) ?></strong>
                                    <?php if ($characterClass["subclass_name"] !== ""): ?>
                                        <small><?= e($characterClass["subclass_name"]) ?></small>
                                    <?php endif; ?>
                                    <b>Nivel <?= (int) $characterClass["level"] ?></b>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="characterProgressMeta">
                        <span>
                            <small>Nivel total</small>
                            <strong><?= $characterLevel ?></strong>
                        </span>
                        <span>
                            <small>Mejoras de característica</small>
                            <strong><?= $characterAsiCount ?></strong>
                        </span>
                    </div>
                    <div class="characterLanguages">
                        <small>Idiomas</small>
                        <?php if ($characterLanguages): ?>
                            <div>
                                <?php foreach ($characterLanguages as $language): ?>
                                    <span><?= e($language) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Aún no se han indicado idiomas.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <h2 class="sectionTitle">Características</h2>
                <div class="charStats">
                    <div class="charStatContainer">
                        <div class="decorator">
                            <div class="textHidden leftCorner">a</div>
                            <div class="textHidden center">a</div>
                            <div class="textHidden rightCorner">a</div>
                        </div>
                        <div class="charStat">
                            <h3>Fuerza</h3>
                            <span class="stat" id="STR">—</span>
                            <span class="modifier" id="STRmod">—</span>
                        </div>
                    </div>
                    <div class="charStatContainer">
                        <div class="decorator">
                            <div class="textHidden leftCorner">a</div>
                            <div class="textHidden center">a</div>
                            <div class="textHidden rightCorner">a</div>
                        </div>
                        <div class="charStat">
                            <h3>Destreza</h3>
                            <span class="stat" id="DEX">—</span>
                            <span class="modifier" id="DEXmod">—</span>
                        </div>
                    </div>
                    <div class="charStatContainer">
                        <div class="decorator">
                            <div class="textHidden leftCorner">a</div>
                            <div class="textHidden center">a</div>
                            <div class="textHidden rightCorner">a</div>
                        </div>
                        <div class="charStat">
                            <h3>Constitucion</h3>
                            <span class="stat" id="CON">—</span>
                            <span class="modifier" id="CONmod">—</span>
                        </div>
                    </div>
                    <div class="charStatContainer">
                        <div class="decorator">
                            <div class="textHidden leftCorner">a</div>
                            <div class="textHidden center">a</div>
                            <div class="textHidden rightCorner">a</div>
                        </div>
                        <div class="charStat">
                            <h3>Inteligencia</h3>
                            <span class="stat" id="INT">—</span>
                            <span class="modifier" id="INTmod">—</span>
                        </div>
                    </div>
                    <div class="charStatContainer">
                        <div class="decorator">
                            <div class="textHidden leftCorner">a</div>
                            <div class="textHidden center">a</div>
                            <div class="textHidden rightCorner">a</div>
                        </div>
                        <div class="charStat">
                            <h3>Sabiduria</h3>
                            <span class="stat" id="WIS">—</span>
                            <span class="modifier" id="WISmod">—</span>
                        </div>
                    </div>
                    <div class="charStatContainer">
                        <div class="decorator">
                            <div class="textHidden leftCorner">a</div>
                            <div class="textHidden center">a</div>
                            <div class="textHidden rightCorner">a</div>
                        </div>
                        <div class="charStat">
                            <h3>Carisma</h3>
                            <span class="stat" id="CHA">—</span>
                            <span class="modifier" id="CHamod">—</span>
                        </div>
                    </div>
                </div>
                <div class="additionalStats">
                    <div id="pPasContainer">
                        <span id="Passive" class="highlightable">—</span>
                        <span id="ppasInfo">Percepción Pasiva</span>
                    </div>
                    <div id="profContainer">
                        <span id="ProfBonus" class="highlightable">—</span>
                        <span id="profInfo">Bono de competencia</span>
                    </div>
                </div>
            </section>

            <section id="skillsDiv">
                <span class="panelKicker">Competencias</span>
                <h2 class="sectionTitle">Habilidades</h2>
                <div id="skillsContainer">
                    <div class="skillsContainerCol">
                        <div class="skillDiv">
                            <span class="stValue" id="Acrobatics">—</span>
                            <span class="stInfo">Acrobacias</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Athletics">—</span>
                            <span class="stInfo">Atletismo</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Arcana">—</span>
                            <span class="stInfo">C. Arcano</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Deception">—</span>
                            <span class="stInfo">Engaño</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="History">—</span>
                            <span class="stInfo">Historia</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Performance">—</span>
                            <span class="stInfo">Interpretacion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Intimidation">—</span>
                            <span class="stInfo">Intimidacion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Investigation">—</span>
                            <span class="stInfo">Investigacion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="SleightofHand">—</span>
                            <span class="stInfo">Juego de manos</span>
                        </div>
                    </div>

                    <div class="skillsContainerCol">
                        <div class="skillDiv">
                            <span class="stValue" id="Medicine">—</span>
                            <span class="stInfo">Medicina</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Nature">—</span>
                            <span class="stInfo">Naturaleza</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Perception">—</span>
                            <span class="stInfo">Percepcion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Insight">—</span>
                            <span class="stInfo">Perspicacia</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Persuasion">—</span>
                            <span class="stInfo">Persuasion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Religion">—</span>
                            <span class="stInfo">Religion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Stealth">—</span>
                            <span class="stInfo">Sigilo</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Survival">—</span>
                            <span class="stInfo">Supervivencia</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Animal">—</span>
                            <span class="stInfo">Trato Animal</span>
                        </div>
                    </div>

                </div>
            </section>
        </div>
        <section class="characterJournal">
        <div class="tabsSelector" role="tablist" aria-label="Información adicional">
            <h2 class="selected tabsSelectorH2" for="spellsTab">Conjuros</h2>
            <h2 class="tabsSelectorH2" for="notesTab">Anotaciones</h2>
        </div>

        <div class="tabContainer" id="spellsTab">
            <section class="spellProgression" aria-labelledby="spellProgressionTitle">
                <header class="spellProgressionHeader">
                    <div>
                        <span class="panelKicker">Progresión mágica</span>
                        <h3 id="spellProgressionTitle"><?= e($spellProgression["label"]) ?></h3>
                        <p><?= e($spellProgression["description"]) ?></p>
                    </div>
                    <span class="spellProgressionClass">
                        <?= e($characterClassSummary) ?> · Nivel total <?= $characterLevel ?>
                    </span>
                </header>

                <?php if ($spellProgressionGroups): ?>
                    <?php foreach ($spellProgressionGroups as $progressionGroup): ?>
                        <section class="spellSlotGroup">
                            <?php if (count($spellProgressionGroups) > 1): ?>
                                <header class="spellSlotGroupHeader">
                                    <h4><?= e($progressionGroup["label"]) ?></h4>
                                    <p><?= e($progressionGroup["description"]) ?></p>
                                </header>
                            <?php endif; ?>
                            <div class="spellSlotsGrid" data-character-id="<?= $charId ?>">
                                <?php foreach ($progressionGroup["slots"] as $slotLevel => $slotCount): ?>
                                    <article
                                        class="spellCounter"
                                        data-slot-group="<?= e($progressionGroup["key"] ?? $progressionGroup["type"]) ?>"
                                        data-slot-level="<?= (int) $slotLevel ?>"
                                        data-total-slots="<?= (int) $slotCount ?>"
                                    >
                                        <header>
                                            <span>
                                                <?= $progressionGroup["type"] === "pact" ? "Pacto" : "Nivel" ?>
                                                <?= (int) $slotLevel ?>
                                            </span>
                                            <strong><?= (int) $slotCount ?></strong>
                                        </header>
                                        <div
                                            class="spellCounterInner"
                                            role="group"
                                            aria-label="<?= (int) $slotCount ?> espacios de nivel <?= (int) $slotLevel ?>"
                                        >
                                            <?php for ($slotIndex = 1; $slotIndex <= $slotCount; $slotIndex++): ?>
                                                <button
                                                    type="button"
                                                    class="spellSpace"
                                                    aria-label="Marcar espacio <?= $slotIndex ?> como gastado"
                                                    aria-pressed="false"
                                                ></button>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="counterButtons">
                                            <button type="button" class="minus-counter" aria-label="Recuperar un espacio">−</button>
                                            <input type="hidden" class="valueCounter" value="0">
                                            <button type="button" class="add-counter" aria-label="Gastar un espacio">+</button>
                                        </div>
                                        <small>0 de <?= (int) $slotCount ?> gastados</small>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($progressionGroup["arcanum"]): ?>
                                <div class="mysticArcanum">
                                    <span>Arcanos místicos</span>
                                    <?php foreach ($progressionGroup["arcanum"] as $arcanumLevel): ?>
                                        <b>Nivel <?= (int) $arcanumLevel ?></b>
                                    <?php endforeach; ?>
                                    <small>Un uso de cada nivel tras un descanso largo.</small>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="spellSlotsEmpty">
                        <span aria-hidden="true">◇</span>
                        <p><?= e($spellProgression["description"]) ?></p>
                    </div>
                <?php endif; ?>
            </section>

            <div class="tabs" id="tabs">
                <?php foreach ($grouped as $level => $spells): ?>
                    <button type="button" class="tab" data-level="<?= (int) $level ?>">
                        <?= (int) $level === 0 ? "Trucos" : "Nivel " . (int) $level ?>
                    </button>
                <?php endforeach; ?>
                <a href="allSpells.php?id_char=<?= $charId ?>" class="tab" title="Añadir conjuro">+</a>
            </div>

            <?php if ($grouped): ?>
                <div id="spellListContainer">
                    <?php foreach ($grouped as $level => $group): ?>
                        <div class="spellList" id="level-<?= (int) $level ?>" data-level="<?= (int) $level ?>">
                            <?php foreach ($group as $spell): ?>
                                <?php
                                $spellName = trim((string) ($spell["name"] ?? "Conjuro sin nombre"));
                                $parenthesisPosition = strpos($spellName, "(");
                                $spellDisplayName = $parenthesisPosition === false
                                    ? $spellName
                                    : trim(substr($spellName, 0, $parenthesisPosition));
                                ?>
                                <span
                                    class="spellsInfo spellSpan"
                                    data-idSpell="id_spell=<?= (int) $spell["id_spell"] ?>"
                                >
                                    <?= e($spellDisplayName) ?> - <?= e($spell["casteo"] ?? "Sin tiempo de lanzamiento") ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($spellLoadFailed): ?>
                <div class="characterJournalState isWarning">
                    <span aria-hidden="true">!</span>
                    <div>
                        <strong>No se pudieron cargar los conjuros</strong>
                        <p>La ficha principal sigue disponible. Vuelve a intentarlo en unos instantes.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="characterJournalState">
                    <span aria-hidden="true">✦</span>
                    <div>
                        <strong>Este personaje todavía no tiene conjuros</strong>
                        <p>Puedes añadirlos desde el grimorio sin perder los datos de la ficha.</p>
                    </div>
                    <a href="allSpells.php?id_char=<?= $charId ?>">Abrir grimorio</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="tabContainer" id="notesTab">
            <iframe
                src="notes.php?framed=true&amp;character_id=<?= $charId ?>"
                frameborder="0"
                style="width: 100%;"
                title="Anotaciones de <?= e($characterName) ?>"
            >
                
            </iframe>
        </div>
        </section>
    </main>
</div>
</div>
<?php require __DIR__ . "/_partials/characterUpdateModal.php"; ?>
<?php if ($hasPdf) { ?>
    <div id="embedContainer">
        <div id="embedTopBar">
            <span id="closeEmbed">X</span>
        </div>
        <embed id="embed"
            src="<?= e($pdfUrl) ?>"
            type="application/pdf"
            width="100%"
            height="100%"
            title="Ficha PDF de <?= e($characterName) ?>" />
    </div>
<?php } ?>

<div id="embededSpellContainer">
    <div id="embedSpellTopBar">
        <span id="closeSpellEmbed">X</span>
    </div>
    <iframe src="" id="spellIframe" frameborder="0"></iframe>
</div>

<script>
    (() => {
        const sheet = window.DeepRolCharacterSheet || {};
        const fallbackFields = sheet.fields || {};
        let fallbackFieldCount = 0;

        Object.entries(fallbackFields).forEach(([name, value]) => {
            const elementId = String(name).trim().replace(/\s+/g, "-");
            const target = document.getElementById(elementId);

            if (target) {
                target.textContent = value ?? "";
                target.setAttribute("dataframe-name", String(name));
                fallbackFieldCount += 1;
            }
        });

        if (typeof window.setPdfFields === "function") {
            window.setPdfFields(sheet.pdfUrl || "", fallbackFields);
            return;
        }

        const sheetStatus = document.getElementById("sheetStatus");
        if (sheetStatus) {
            sheetStatus.classList.remove("isLoading", "isReady", "isFallback", "isError");
            sheetStatus.classList.add(fallbackFieldCount > 0 ? "isFallback" : "isError");

            const sheetStatusLabel = sheetStatus.querySelector("span");
            if (sheetStatusLabel) {
                sheetStatusLabel.textContent = fallbackFieldCount > 0
                    ? "Datos locales disponibles"
                    : "No se pudieron cargar los datos";
            }
        }

        console.error("No se pudo cargar char.js; se muestran los datos locales disponibles.");
    })();

    const card = document.querySelector('.card');
    const personaje = document.querySelector('.personaje');

    if (card && personaje) {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = -(y - centerY) / 15;
            const rotateY = (x - centerX) / 15;

            personaje.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });

        card.addEventListener('mouseleave', () => {
            personaje.style.transform = 'rotateX(0deg) rotateY(0deg)';
        });
    }

    document.getElementById("linterna")?.addEventListener('click', function() {
        this.classList.toggle("linterna_off");
        document.getElementById("haz-de-luz")?.classList.toggle("haz-de-luz_off");

    });

    document.getElementById("gancho")?.addEventListener('click', function() {
        document.getElementById("contenedor-linterna")?.classList.add("fallen-contenedor-linterna");
    });

    function showEmbededSpell(spellId) {
        var embedSpellContainer = document.getElementById("embededSpellContainer");

        var spellEmbedDiv = document.getElementById("embededSpellContainer");

        spellEmbedDiv.querySelector("#spellIframe").src = "../sections/_partials/embededSpell.php?" + spellId;
        spellEmbedDiv.style.display = "block";
    }

    document.getElementById("closeSpellEmbed")?.addEventListener('click', function() {
        this.parentElement.parentElement.style.display = "none";
    });
</script>
</body>
</html>
