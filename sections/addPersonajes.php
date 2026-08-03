<?php

require_once("../classes/DbConnector.php");
require_once("../classes/CharacterOptionCatalog.php");
require_once("../classes/CharacterProgression.php");

if (!isset($_COOKIE["logged"]) || (int) $_COOKIE["logged"] <= 0) {
    header("Location: ../login.php");
    exit;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function postedText(string $key, string $default = ""): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function postedInt(string $key, int $default = 0): int
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    return $value === false || $value === null ? $default : (int) $value;
}

function postedCharacterClasses(): array
{
    $classNames = isset($_POST["class_names"]) && is_array($_POST["class_names"])
        ? $_POST["class_names"]
        : [postedText("class_name")];
    $subclassNames = isset($_POST["subclass_names"]) && is_array($_POST["subclass_names"])
        ? $_POST["subclass_names"]
        : [postedText("subclass_name")];
    $classLevels = isset($_POST["class_levels"]) && is_array($_POST["class_levels"])
        ? $_POST["class_levels"]
        : [postedInt("level", 1)];
    $rows = [];

    foreach (array_slice($classNames, 0, 13) as $index => $className) {
        if (is_array($className)) {
            continue;
        }
        $rows[] = [
            "class_name" => trim((string) $className),
            "subclass_name" => isset($subclassNames[$index]) && !is_array($subclassNames[$index])
                ? trim((string) $subclassNames[$index])
                : "",
            "level" => max(1, (int) ($classLevels[$index] ?? 1)),
            "is_primary" => $index === 0,
        ];
    }

    return $rows ?: [[
        "class_name" => "",
        "subclass_name" => "",
        "level" => 1,
        "is_primary" => true,
    ]];
}

function formatModifier(int $value): string
{
    return ($value >= 0 ? "+" : "") . $value;
}

function abilityModifier(int $score): int
{
    return (int) floor(($score - 10) / 2);
}

function proficiencyBonus(int $level): int
{
    return 2 + (int) floor(($level - 1) / 4);
}

function safeText(string $key, int $maxLength = 5000): string
{
    $value = postedText($key);
    if (function_exists("mb_substr")) {
        return mb_substr($value, 0, $maxLength, "UTF-8");
    }

    return substr($value, 0, $maxLength);
}

function removeCreatedCharacterDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    foreach (scandir($directory) ?: [] as $entry) {
        if ($entry === "." || $entry === "..") {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $entry;
        if (is_file($path)) {
            unlink($path);
        }
    }

    rmdir($directory);
}

function issueCharacterCsrfCookie(string $cookieName): string
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

$db = DbConector::singleton();
$userId = (int) $_COOKIE["logged"];
$characterCatalog = CharacterOptionCatalog::all();
$classes = $characterCatalog["classes"];
$races = $characterCatalog["races"];
$errors = [];
$postedClassRows = postedCharacterClasses();
$availableLanguages = CharacterProgression::languages();

$csrfCookieName = "deeprol_character_csrf";
$csrfToken = isset($_COOKIE[$csrfCookieName])
    && preg_match("/^[a-f0-9]{48}$/", (string) $_COOKIE[$csrfCookieName])
        ? (string) $_COOKIE[$csrfCookieName]
        : issueCharacterCsrfCookie($csrfCookieName);

$abilityLabels = [
    "str" => "Fuerza",
    "dex" => "Destreza",
    "con" => "Constitución",
    "int" => "Inteligencia",
    "wis" => "Sabiduría",
    "cha" => "Carisma",
];

$savingThrowFields = [
    "str" => ["field" => "ST Strength", "checkbox" => "Check Box 11"],
    "dex" => ["field" => "ST Dexterity", "checkbox" => "Check Box 18"],
    "con" => ["field" => "ST Constitution", "checkbox" => "Check Box 19"],
    "int" => ["field" => "ST Intelligence", "checkbox" => "Check Box 20"],
    "wis" => ["field" => "ST Wisdom", "checkbox" => "Check Box 21"],
    "cha" => ["field" => "ST Charisma", "checkbox" => "Check Box 22"],
];

$skillFields = [
    "acrobatics" => ["label" => "Acrobacias", "ability" => "dex", "field" => "Acrobatics", "checkbox" => "Check Box 23"],
    "animal" => ["label" => "Trato con animales", "ability" => "wis", "field" => "Animal", "checkbox" => "Check Box 24"],
    "arcana" => ["label" => "Arcano", "ability" => "int", "field" => "Arcana", "checkbox" => "Check Box 25"],
    "athletics" => ["label" => "Atletismo", "ability" => "str", "field" => "Athletics", "checkbox" => "Check Box 26"],
    "deception" => ["label" => "Engaño", "ability" => "cha", "field" => "Deception ", "checkbox" => "Check Box 27"],
    "history" => ["label" => "Historia", "ability" => "int", "field" => "History ", "checkbox" => "Check Box 28"],
    "insight" => ["label" => "Perspicacia", "ability" => "wis", "field" => "Insight", "checkbox" => "Check Box 29"],
    "intimidation" => ["label" => "Intimidación", "ability" => "cha", "field" => "Intimidation", "checkbox" => "Check Box 30"],
    "investigation" => ["label" => "Investigación", "ability" => "int", "field" => "Investigation ", "checkbox" => "Check Box 31"],
    "medicine" => ["label" => "Medicina", "ability" => "wis", "field" => "Medicine", "checkbox" => "Check Box 32"],
    "nature" => ["label" => "Naturaleza", "ability" => "int", "field" => "Nature", "checkbox" => "Check Box 33"],
    "perception" => ["label" => "Percepción", "ability" => "wis", "field" => "Perception ", "checkbox" => "Check Box 34"],
    "performance" => ["label" => "Interpretación", "ability" => "cha", "field" => "Performance", "checkbox" => "Check Box 35"],
    "persuasion" => ["label" => "Persuasión", "ability" => "cha", "field" => "Persuasion", "checkbox" => "Check Box 36"],
    "religion" => ["label" => "Religión", "ability" => "int", "field" => "Religion", "checkbox" => "Check Box 37"],
    "sleight_of_hand" => ["label" => "Juego de manos", "ability" => "dex", "field" => "SleightofHand", "checkbox" => "Check Box 38"],
    "stealth" => ["label" => "Sigilo", "ability" => "dex", "field" => "Stealth ", "checkbox" => "Check Box 39"],
    "survival" => ["label" => "Supervivencia", "ability" => "wis", "field" => "Survival", "checkbox" => "Check Box 40"],
];

$hitDiceByClass = [
    "Artifice" => 8,
    "Barbaro" => 12,
    "Bardo" => 8,
    "Brujo" => 8,
    "Clerigo" => 8,
    "Druida" => 8,
    "Explorador" => 10,
    "Guerrero" => 10,
    "Hechicero" => 6,
    "Mago" => 6,
    "Monje" => 8,
    "Paladin" => 10,
    "Picaro" => 8,
];

$spellcastingAbilityByClass = [
    "Artifice" => "int",
    "Bardo" => "cha",
    "Brujo" => "cha",
    "Clerigo" => "wis",
    "Druida" => "wis",
    "Explorador" => "wis",
    "Hechicero" => "cha",
    "Mago" => "int",
    "Paladin" => "cha",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postedCsrfToken = (string) ($_POST["csrf_token"] ?? "");
    $csrfCookieToken = (string) ($_COOKIE[$csrfCookieName] ?? "");
    if (
        $csrfCookieToken === ""
        || !hash_equals($csrfCookieToken, $postedCsrfToken)
    ) {
        $errors["general"] = "La sesión del formulario ha caducado. Recarga la página e inténtalo de nuevo.";
    }

    $characterName = postedText("character_name");
    $playerName = postedText("player_name");
    $className = trim((string) ($postedClassRows[0]["class_name"] ?? ""));
    $subclassName = trim((string) ($postedClassRows[0]["subclass_name"] ?? ""));
    $raceName = postedText("race_name");
    $subraceName = postedText("subrace_name");
    $level = 0;
    $background = postedText("background");
    $alignment = postedText("alignment");
    $experience = max(0, postedInt("experience", 0));

    if ($characterName === "") {
        $errors["character_name"] = "Escribe el nombre del personaje.";
    } elseif (
        !preg_match("/^[\p{L}\p{N}][\p{L}\p{N}\s'-]{0,49}$/u", $characterName)
    ) {
        $errors["character_name"] = "Usa entre 1 y 50 letras, números, espacios, apóstrofes o guiones.";
    }

    $characterClasses = [];
    $usedClasses = [];
    $classOption = null;
    foreach ($postedClassRows as $index => $postedClass) {
        $rowClassName = trim((string) $postedClass["class_name"]);
        $rowSubclassName = trim((string) $postedClass["subclass_name"]);
        $rowLevel = (int) $postedClass["level"];
        $rowClassOption = CharacterOptionCatalog::findClass($rowClassName);

        if ($rowClassOption === null) {
            $errors["classes"] = "Todas las clases deben pertenecer al catálogo oficial.";
            continue;
        }
        if (isset($usedClasses[$rowClassName])) {
            $errors["classes"] = "No repitas una clase: aumenta su nivel en la fila existente.";
            continue;
        }
        if ($rowLevel < 1 || $rowLevel > 20) {
            $errors["classes"] = "Cada clase debe tener entre 1 y 20 niveles.";
            continue;
        }

        $subclasses = is_array($rowClassOption["subclasses"] ?? null)
            ? $rowClassOption["subclasses"]
            : [];
        $subclassLevel = max(1, (int) ($rowClassOption["subclassLevel"] ?? 1));
        if ($rowLevel >= $subclassLevel && $subclasses && $rowSubclassName === "") {
            $errors["classes"] = "Selecciona la subclase correspondiente en cada clase que ya la haya obtenido.";
            continue;
        }
        if (
            $rowSubclassName !== ""
            && !CharacterOptionCatalog::hasNamedOption($subclasses, $rowSubclassName)
        ) {
            $errors["classes"] = "Una de las subclases no corresponde con su clase.";
            continue;
        }
        if ($rowLevel < $subclassLevel && $rowSubclassName !== "") {
            $errors["classes"] = "Una subclase se ha seleccionado antes del nivel en que se obtiene.";
            continue;
        }

        $usedClasses[$rowClassName] = true;
        $level += $rowLevel;
        $characterClasses[] = [
            "class_name" => $rowClassName,
            "class_label" => trim((string) ($rowClassOption["label"] ?? $rowClassName)),
            "subclass_name" => $rowSubclassName,
            "level" => $rowLevel,
            "is_primary" => $index === 0,
        ];
        if ($index === 0) {
            $classOption = $rowClassOption;
        }
    }
    if (!$characterClasses) {
        $errors["classes"] = "Añade al menos una clase.";
    } elseif ($level > 20) {
        $errors["classes"] = "La suma de los niveles de clase no puede superar 20.";
    }

    $languages = CharacterProgression::normalizeLanguages(
        (array) ($_POST["languages"] ?? []),
        postedText("custom_languages")
    );

    $raceOption = CharacterOptionCatalog::findRace($raceName);
    if ($raceOption === null) {
        $errors["race_name"] = "Selecciona una raza o linaje válido.";
    } else {
        $subraces = is_array($raceOption["subraces"] ?? null)
            ? $raceOption["subraces"]
            : [];

        if ($subraces && $subraceName === "") {
            $errors["subrace_name"] = "Selecciona una subraza o variante.";
        } elseif (
            $subraceName !== ""
            && !CharacterOptionCatalog::hasNamedOption($subraces, $subraceName)
        ) {
            $errors["subrace_name"] = "Selecciona una subraza válida para esta raza.";
        }
    }

    if ($background === "") {
        $errors["background"] = "Indica el trasfondo del personaje.";
    }

    $abilityScores = [];
    foreach ($abilityLabels as $ability => $label) {
        $score = postedInt("ability_" . $ability, 10);
        $abilityScores[$ability] = $score;
        if ($score < 1 || $score > 30) {
            $errors["ability_" . $ability] = "{$label} debe estar entre 1 y 30.";
        }
    }

    $armorClass = postedInt("armor_class", 10);
    $hitPointsMax = postedInt("hp_max", 1);
    $hitPointsCurrent = postedInt("hp_current", $hitPointsMax);
    $hitPointsTemporary = max(0, postedInt("hp_temp", 0));
    $speed = postedInt("speed", 30);

    if ($armorClass < 0 || $armorClass > 40) {
        $errors["armor_class"] = "La clase de armadura debe estar entre 0 y 40.";
    }
    if ($hitPointsMax < 1 || $hitPointsMax > 999) {
        $errors["hp_max"] = "Los puntos de golpe máximos deben estar entre 1 y 999.";
    }
    if ($hitPointsCurrent < 0 || $hitPointsCurrent > 999) {
        $errors["hp_current"] = "Los puntos de golpe actuales deben estar entre 0 y 999.";
    }
    if ($speed < 0 || $speed > 300) {
        $errors["speed"] = "La velocidad debe estar entre 0 y 300 pies.";
    }

    $uploadedImages = [];
    $imageRules = [
        "profile_image" => ["label" => "retrato", "prefix" => "imagenPequena"],
        "full_body_image" => ["label" => "imagen de cuerpo completo", "prefix" => "imagenGeneral"],
    ];
    $allowedImageMimes = [
        "image/png" => "png",
        "image/jpeg" => "jpg",
        "image/webp" => "webp",
    ];

    foreach ($imageRules as $field => $rule) {
        $upload = $_FILES[$field] ?? null;
        if (!$upload || (int) ($upload["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ((int) $upload["error"] !== UPLOAD_ERR_OK) {
            $errors[$field] = "No se pudo recibir " . $rule["label"] . ".";
            continue;
        }

        if ((int) $upload["size"] > 8 * 1024 * 1024) {
            $errors[$field] = "La imagen no puede superar 8 MB.";
            continue;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload["tmp_name"]);
        if (!isset($allowedImageMimes[$mime])) {
            $errors[$field] = "Usa una imagen PNG, JPG o WEBP.";
            continue;
        }

        $uploadedImages[$field] = [
            "tmp_name" => $upload["tmp_name"],
            "filename" => $rule["prefix"] . "." . $allowedImageMimes[$mime],
        ];
    }

    $generatedPdf = $_FILES["generated_pdf"] ?? null;
    if (!$generatedPdf || (int) $generatedPdf["error"] !== UPLOAD_ERR_OK) {
        $errors["generated_pdf"] = "No se pudo generar la ficha PDF. Comprueba que JavaScript está habilitado.";
    } elseif ((int) $generatedPdf["size"] > 20 * 1024 * 1024) {
        $errors["generated_pdf"] = "La ficha PDF generada supera el límite de 20 MB.";
    } else {
        $pdfHeader = file_get_contents($generatedPdf["tmp_name"], false, null, 0, 5);
        $pdfMime = (new finfo(FILEINFO_MIME_TYPE))->file($generatedPdf["tmp_name"]);
        if ($pdfHeader !== "%PDF-" || !in_array($pdfMime, ["application/pdf", "application/octet-stream"], true)) {
            $errors["generated_pdf"] = "El archivo generado no es una ficha PDF válida.";
        }
    }

    $characterDirectory = dirname(__DIR__)
        . DIRECTORY_SEPARATOR . "resources"
        . DIRECTORY_SEPARATOR . "chars"
        . DIRECTORY_SEPARATOR . $characterName;

    if (is_dir($characterDirectory)) {
        $errors["character_name"] = "Ya existe una carpeta de personaje con ese nombre.";
    }

    if (!$errors) {
        $classDisplayName = trim((string) ($classOption["label"] ?? $className));
        $raceDisplayName = trim((string) ($raceOption["label"] ?? $raceName));
        $profBonus = proficiencyBonus($level);
        $modifiers = [];
        foreach ($abilityScores as $ability => $score) {
            $modifiers[$ability] = abilityModifier($score);
        }

        $savingThrowProficiencies = array_map(
            "strval",
            (array) ($_POST["saving_throw_proficiencies"] ?? [])
        );
        $sheetFields = [
            "CharacterName" => $characterName,
            "CharacterName 2" => $characterName,
            "ClassLevel" => CharacterProgression::classSummary($characterClasses),
            "Background" => $background,
            "PlayerName" => $playerName,
            "Race " => $raceDisplayName
                . ($subraceName !== "" ? " · " . $subraceName : ""),
            "Alignment" => $alignment,
            "XP" => (string) $experience,
            "Inspiration" => postedText("inspiration"),
            "ProfBonus" => formatModifier($profBonus),
            "AC" => (string) $armorClass,
            "Initiative" => postedText("initiative") !== ""
                ? formatModifier(postedInt("initiative"))
                : formatModifier($modifiers["dex"]),
            "Speed" => (string) $speed,
            "HPMax" => (string) $hitPointsMax,
            "HPCurrent" => (string) $hitPointsCurrent,
            "HPTemp" => (string) $hitPointsTemporary,
            "HDTotal" => (string) $level,
            "HD" => CharacterProgression::hitDiceSummary($characterClasses),
            "PersonalityTraits " => safeText("personality_traits"),
            "Ideals" => safeText("ideals"),
            "Bonds" => safeText("bonds"),
            "Flaws" => safeText("flaws"),
            "Passive" => "",
            "AttacksSpellcasting" => safeText("attacks_spellcasting"),
            "ProficienciesLang" => safeText("proficiencies_languages"),
            "Equipment" => safeText("equipment"),
            "Features and Traits" => safeText("features_traits"),
            "CP" => (string) max(0, postedInt("coins_cp")),
            "SP" => (string) max(0, postedInt("coins_sp")),
            "EP" => (string) max(0, postedInt("coins_ep")),
            "GP" => (string) max(0, postedInt("coins_gp")),
            "PP" => (string) max(0, postedInt("coins_pp")),
            "Age" => postedText("age"),
            "Height" => postedText("height"),
            "Weight" => postedText("weight"),
            "Eyes" => postedText("eyes"),
            "Skin" => postedText("skin"),
            "Hair" => postedText("hair"),
            "Backstory" => safeText("backstory", 10000),
            "Allies" => safeText("allies"),
            "FactionName" => postedText("faction_name"),
            "Feat+Traits" => safeText("additional_features"),
            "Treasure" => safeText("treasure"),
        ];

        $pdfCheckboxes = [];
        $modifierFields = [
            "str" => "STRmod",
            "dex" => "DEXmod ",
            "con" => "CONmod",
            "int" => "INTmod",
            "wis" => "WISmod",
            "cha" => "CHamod",
        ];
        foreach ($abilityScores as $ability => $score) {
            $pdfAbilityName = strtoupper($ability);
            $sheetFields[$pdfAbilityName] = (string) $score;
            $sheetFields[$modifierFields[$ability]] = formatModifier($modifiers[$ability]);

            $savingThrow = $modifiers[$ability];
            if (in_array($ability, $savingThrowProficiencies, true)) {
                $savingThrow += $profBonus;
                $pdfCheckboxes[] = $savingThrowFields[$ability]["checkbox"];
            }
            $sheetFields[$savingThrowFields[$ability]["field"]] = formatModifier($savingThrow);
        }

        $skillProficiencyLevels = [];
        foreach ($skillFields as $skill => $skillInfo) {
            $proficiencyLevel = postedInt("skill_" . $skill, 0);
            if (!in_array($proficiencyLevel, [0, 1, 2], true)) {
                $proficiencyLevel = 0;
            }
            $skillProficiencyLevels[$skill] = $proficiencyLevel;

            $skillModifier = $modifiers[$skillInfo["ability"]] + ($profBonus * $proficiencyLevel);
            $sheetFields[$skillInfo["field"]] = formatModifier($skillModifier);
            if ($proficiencyLevel > 0) {
                $pdfCheckboxes[] = $skillInfo["checkbox"];
            }
        }

        $sheetFields["Passive"] = (string) (10 + (int) $sheetFields["Perception "]);

        $weaponFields = [
            1 => ["Wpn Name", "Wpn1 AtkBonus", "Wpn1 Damage"],
            2 => ["Wpn Name 2", "Wpn2 AtkBonus ", "Wpn2 Damage "],
            3 => ["Wpn Name 3", "Wpn3 AtkBonus  ", "Wpn3 Damage "],
        ];
        foreach ($weaponFields as $index => [$nameField, $bonusField, $damageField]) {
            $sheetFields[$nameField] = postedText("weapon_{$index}_name");
            $sheetFields[$bonusField] = postedText("weapon_{$index}_bonus");
            $sheetFields[$damageField] = postedText("weapon_{$index}_damage");
        }

        $spellcastingAbility = $spellcastingAbilityByClass[$className] ?? "";
        if ($spellcastingAbility !== "") {
            $spellModifier = $modifiers[$spellcastingAbility];
            $sheetFields["Spellcasting Class 2"] = $classDisplayName;
            $sheetFields["SpellcastingAbility 2"] = strtoupper($spellcastingAbility);
            $sheetFields["SpellSaveDC  2"] = (string) (8 + $profBonus + $spellModifier);
            $sheetFields["SpellAtkBonus 2"] = formatModifier($profBonus + $spellModifier);
        }

        $sheetFields["_pdfCheckboxes"] = array_values(array_unique($pdfCheckboxes));
        $sheetFields["_otherProficiencies"] = safeText("proficiencies_languages");
        $sheetFields = CharacterProgression::deriveSheetFields(
            $sheetFields,
            $characterClasses,
            $savingThrowProficiencies,
            $skillProficiencyLevels,
            $languages
        );

        $createdDirectory = false;
        try {
            if (!mkdir($characterDirectory, 0775, true) && !is_dir($characterDirectory)) {
                throw new RuntimeException("No se pudo crear la carpeta del personaje.");
            }
            $createdDirectory = true;

            foreach ($uploadedImages as $upload) {
                $target = $characterDirectory . DIRECTORY_SEPARATOR . $upload["filename"];
                if (!move_uploaded_file($upload["tmp_name"], $target)) {
                    throw new RuntimeException("No se pudieron guardar las imágenes del personaje.");
                }
            }

            $pdfFilename = "ficha.pdf";
            $pdfTarget = $characterDirectory . DIRECTORY_SEPARATOR . $pdfFilename;
            if (!move_uploaded_file($generatedPdf["tmp_name"], $pdfTarget)) {
                throw new RuntimeException("No se pudo guardar la ficha PDF.");
            }

            $sheetJson = json_encode(
                $sheetFields,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            if (
                $sheetJson === false
                || file_put_contents(
                    $characterDirectory . DIRECTORY_SEPARATOR . "sheet.json",
                    $sheetJson
                ) === false
            ) {
                throw new RuntimeException("No se pudieron guardar los datos de la ficha.");
            }

            $characterId = $db->createChar(
                $userId,
                $characterName,
                $raceName,
                $subraceName,
                $level,
                $className,
                $subclassName,
                $pdfFilename,
                $uploadedImages["profile_image"]["filename"] ?? "",
                $uploadedImages["full_body_image"]["filename"] ?? "",
                $characterClasses,
                $languages
            );

            issueCharacterCsrfCookie($csrfCookieName);
            header("Location: personaje.php?id=" . $characterId);
            exit;
        } catch (Throwable $exception) {
            if ($createdDirectory) {
                removeCreatedCharacterDirectory($characterDirectory);
            }

            error_log("DeepRol create character: " . $exception->getMessage());
            $errors["general"] = "No se pudo guardar el personaje. No se ha conservado ningún archivo incompleto.";
        }
    }
}

$selectedClassName = trim((string) ($postedClassRows[0]["class_name"] ?? ""));
$selectedSubclassName = trim((string) ($postedClassRows[0]["subclass_name"] ?? ""));
$selectedRaceName = postedText("race_name");
$selectedSubraceName = postedText("subrace_name");
$selectedSavingThrows = array_map(
    "strval",
    (array) ($_POST["saving_throw_proficiencies"] ?? [])
);
$selectedLanguages = CharacterProgression::normalizeLanguages(
    (array) ($_POST["languages"] ?? [])
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Crear personaje · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/index.css">
    <link rel="stylesheet" href="../styles/form.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script src="../scripts/vendor/pdf-lib/pdf-lib.min.js"></script>
    <script type="application/json" id="characterOptions"><?= json_encode(
        $characterCatalog,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ) ?></script>
    <script defer src="../scripts/form.js"></script>
</head>
<body>
    <div class="mist" aria-hidden="true"></div>

    <main class="characterBuilder">
        <header class="builderHeader">
            <a href="personajes.php" class="builderBack">← Personajes</a>
            <div>
                <span class="builderKicker">Nueva leyenda</span>
                <h1>Crear personaje</h1>
                <p>Completa los datos esenciales. DeepRol calculará modificadores, competencias y la ficha PDF.</p>
            </div>
            <span class="builderRune" aria-hidden="true">✦</span>
        </header>

        <?php if ($errors): ?>
            <aside class="formAlert" role="alert">
                <strong>No se pudo crear el personaje</strong>
                <p>Revisa los campos señalados y vuelve a intentarlo.</p>
                <?php if (isset($errors["general"])): ?>
                    <small><?= e($errors["general"]) ?></small>
                <?php endif; ?>
                <?php if (isset($errors["generated_pdf"])): ?>
                    <small><?= e($errors["generated_pdf"]) ?></small>
                <?php endif; ?>
            </aside>
        <?php endif; ?>

        <nav class="builderProgress" aria-label="Progreso del formulario">
            <button type="button" class="isActive" data-step-target="1">
                <span>01</span>
                <strong>Identidad</strong>
            </button>
            <button type="button" data-step-target="2">
                <span>02</span>
                <strong>Características</strong>
            </button>
            <button type="button" data-step-target="3">
                <span>03</span>
                <strong>Competencias</strong>
            </button>
            <button type="button" data-step-target="4">
                <span>04</span>
                <strong>Historia</strong>
            </button>
        </nav>

        <form
            id="characterForm"
            class="builderForm"
            action=""
            method="post"
            enctype="multipart/form-data"
            data-pdf-template="../resources/templates/ficha-personaje.pdf"
            data-selected-subclass="<?= e($selectedSubclassName) ?>"
            data-selected-subrace="<?= e($selectedSubraceName) ?>"
            novalidate
        >
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="file" name="generated_pdf" id="generatedPdf" accept="application/pdf" hidden>

            <section class="formStep isActive" data-step="1" aria-labelledby="identityTitle">
                <div class="stepHeading">
                    <div>
                        <span>Paso 1 de 4</span>
                        <h2 id="identityTitle">Identidad del personaje</h2>
                    </div>
                    <p>Los datos que encabezarán la ficha y definirán el tema visual.</p>
                </div>

                <div class="formGrid formGridThree">
                    <label class="field spanTwo">
                        <span>Nombre del personaje *</span>
                        <input
                            type="text"
                            name="character_name"
                            value="<?= e(postedText("character_name")) ?>"
                            maxlength="50"
                            autocomplete="off"
                            required
                        >
                        <?php if (isset($errors["character_name"])): ?><small class="fieldError"><?= e($errors["character_name"]) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span>Nombre del jugador</span>
                        <input type="text" name="player_name" value="<?= e(postedText("player_name")) ?>" maxlength="80">
                    </label>

                    <div class="field spanThree multiclassBuilder" id="multiclassBuilder">
                        <div class="multiclassHeading">
                            <span>Clases y niveles *</span>
                            <strong>Nivel total <b id="totalLevelPreview"><?= (int) array_sum(array_column($postedClassRows, "level")) ?></b>/20</strong>
                        </div>
                        <div class="classLevelRows" id="classLevelRows">
                            <?php foreach ($postedClassRows as $classIndex => $postedClass): ?>
                                <?php
                                $rowClassName = trim((string) ($postedClass["class_name"] ?? ""));
                                $rowSubclassName = trim((string) ($postedClass["subclass_name"] ?? ""));
                                $rowClassOption = CharacterOptionCatalog::findClass($rowClassName);
                                ?>
                                <div class="classLevelRow" data-class-row>
                                    <label class="field">
                                        <span><?= $classIndex === 0 ? "Clase inicial" : "Clase adicional" ?></span>
                                        <select
                                            name="class_names[]"
                                            <?= $classIndex === 0 ? 'id="classSelect"' : "" ?>
                                            data-class-select
                                            required
                                        >
                                            <option value="">Selecciona una clase</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option
                                                    value="<?= e($class["name"]) ?>"
                                                    data-description="<?= e(strip_tags((string) ($class["description"] ?? ""))) ?>"
                                                    <?= $rowClassName === (string) $class["name"] ? "selected" : "" ?>
                                                ><?= e($class["label"] ?? $class["name"]) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label class="field compactField">
                                        <span>Nivel de clase</span>
                                        <input
                                            type="number"
                                            name="class_levels[]"
                                            data-class-level
                                            value="<?= max(1, (int) ($postedClass["level"] ?? 1)) ?>"
                                            min="1"
                                            max="20"
                                            required
                                        >
                                    </label>
                                    <label class="field" data-subclass-field>
                                        <span>Subclase</span>
                                        <select name="subclass_names[]" data-subclass-select>
                                            <option value="">Sin subclase</option>
                                            <?php foreach (($rowClassOption["subclasses"] ?? []) as $subclassOption): ?>
                                                <option
                                                    value="<?= e($subclassOption["name"]) ?>"
                                                    <?= $rowSubclassName === (string) $subclassOption["name"] ? "selected" : "" ?>
                                                ><?= e($subclassOption["name"]) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="fieldHelp" data-subclass-hint></small>
                                    </label>
                                    <?php if ($classIndex > 0): ?>
                                        <button type="button" class="removeClassButton" data-remove-class aria-label="Eliminar esta clase">×</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input
                            type="hidden"
                            name="level"
                            id="totalLevel"
                            value="<?= max(1, (int) array_sum(array_column($postedClassRows, "level"))) ?>"
                        >
                        <button type="button" class="addClassButton" id="addClassButton">+ Añadir multiclase</button>
                        <small class="fieldHelp">El bono de competencia usa el nivel total; subclases, mejoras y dados de golpe usan el nivel de cada clase.</small>
                        <?php if (isset($errors["classes"])): ?><small class="fieldError"><?= e($errors["classes"]) ?></small><?php endif; ?>
                    </div>

                    <label class="field">
                        <span>Raza *</span>
                        <select name="race_name" id="raceSelect" required>
                            <option value="">Selecciona una raza</option>
                            <?php foreach ($races as $race): ?>
                                <option value="<?= e($race["name"]) ?>" <?= $selectedRaceName === (string) $race["name"] ? "selected" : "" ?>>
                                    <?= e($race["label"] ?? $race["name"]) ?> · <?= e($race["source"] ?? "Oficial") ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors["race_name"])): ?><small class="fieldError"><?= e($errors["race_name"]) ?></small><?php endif; ?>
                    </label>

                    <label class="field" id="subraceField" hidden>
                        <span>Subraza o variante <b aria-hidden="true">*</b></span>
                        <select name="subrace_name" id="subraceSelect" disabled>
                            <option value="">Selecciona una subraza</option>
                        </select>
                        <?php if (isset($errors["subrace_name"])): ?><small class="fieldError"><?= e($errors["subrace_name"]) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span>Trasfondo *</span>
                        <input type="text" name="background" value="<?= e(postedText("background")) ?>" maxlength="80" required>
                        <?php if (isset($errors["background"])): ?><small class="fieldError"><?= e($errors["background"]) ?></small><?php endif; ?>
                    </label>

                    <label class="field">
                        <span>Alineamiento</span>
                        <select name="alignment">
                            <option value="">Sin especificar</option>
                            <?php foreach (["Legal bueno", "Neutral bueno", "Caótico bueno", "Legal neutral", "Neutral", "Caótico neutral", "Legal malvado", "Neutral malvado", "Caótico malvado"] as $alignmentOption): ?>
                                <option value="<?= e($alignmentOption) ?>" <?= postedText("alignment") === $alignmentOption ? "selected" : "" ?>><?= e($alignmentOption) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="field compactField">
                        <span>Experiencia</span>
                        <input type="number" name="experience" value="<?= e(postedInt("experience", 0)) ?>" min="0" max="999999">
                    </label>
                </div>

                <div class="classHint" id="classHint" aria-live="polite"></div>

                <div class="uploadGrid">
                    <label class="uploadCard">
                        <input type="file" name="profile_image" accept="image/png,image/jpeg,image/webp">
                        <span class="uploadPreview" data-upload-preview>
                            <b aria-hidden="true">◈</b>
                            <img alt="" hidden>
                        </span>
                        <span>
                            <strong>Retrato opcional</strong>
                            <small>PNG, JPG o WEBP · máximo 8 MB</small>
                        </span>
                        <?php if (isset($errors["profile_image"])): ?><small class="fieldError"><?= e($errors["profile_image"]) ?></small><?php endif; ?>
                    </label>

                    <label class="uploadCard">
                        <input type="file" name="full_body_image" accept="image/png,image/jpeg,image/webp">
                        <span class="uploadPreview" data-upload-preview>
                            <b aria-hidden="true">♙</b>
                            <img alt="" hidden>
                        </span>
                        <span>
                            <strong>Cuerpo completo opcional</strong>
                            <small>Si la añades, también aparecerá en la ficha PDF</small>
                        </span>
                        <?php if (isset($errors["full_body_image"])): ?><small class="fieldError"><?= e($errors["full_body_image"]) ?></small><?php endif; ?>
                    </label>
                </div>
            </section>

            <section class="formStep" data-step="2" aria-labelledby="abilitiesTitle" hidden>
                <div class="stepHeading">
                    <div>
                        <span>Paso 2 de 4</span>
                        <h2 id="abilitiesTitle">Características y combate</h2>
                    </div>
                    <p>Los modificadores y el bono de competencia se calculan automáticamente.</p>
                </div>

                <div class="abilityGrid">
                    <?php foreach ($abilityLabels as $ability => $label): ?>
                        <label class="abilityField">
                            <span><?= e($label) ?></span>
                            <input
                                type="number"
                                name="ability_<?= e($ability) ?>"
                                value="<?= e(postedInt("ability_" . $ability, 10)) ?>"
                                min="1"
                                max="30"
                                required
                            >
                            <strong data-modifier-for="<?= e($ability) ?>">+0</strong>
                            <?php if (isset($errors["ability_" . $ability])): ?><small class="fieldError"><?= e($errors["ability_" . $ability]) ?></small><?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="formGrid formGridFour combatGrid">
                    <label class="field compactField">
                        <span>Clase de armadura *</span>
                        <input type="number" name="armor_class" value="<?= e(postedInt("armor_class", 10)) ?>" min="0" max="40" required>
                        <?php if (isset($errors["armor_class"])): ?><small class="fieldError"><?= e($errors["armor_class"]) ?></small><?php endif; ?>
                    </label>
                    <label class="field compactField">
                        <span>Vida máxima *</span>
                        <input type="number" name="hp_max" value="<?= e(postedInt("hp_max", 1)) ?>" min="1" max="999" required>
                        <?php if (isset($errors["hp_max"])): ?><small class="fieldError"><?= e($errors["hp_max"]) ?></small><?php endif; ?>
                    </label>
                    <label class="field compactField">
                        <span>Vida actual</span>
                        <input type="number" name="hp_current" value="<?= e(postedInt("hp_current", postedInt("hp_max", 1))) ?>" min="0" max="999">
                        <?php if (isset($errors["hp_current"])): ?><small class="fieldError"><?= e($errors["hp_current"]) ?></small><?php endif; ?>
                    </label>
                    <label class="field compactField">
                        <span>Vida temporal</span>
                        <input type="number" name="hp_temp" value="<?= e(postedInt("hp_temp", 0)) ?>" min="0" max="999">
                    </label>
                    <label class="field compactField">
                        <span>Iniciativa</span>
                        <input type="number" name="initiative" value="<?= e(postedText("initiative")) ?>" min="-20" max="30" placeholder="Automática">
                    </label>
                    <label class="field compactField">
                        <span>Velocidad (pies) *</span>
                        <input type="number" name="speed" value="<?= e(postedInt("speed", 30)) ?>" min="0" max="300" step="5" required>
                        <?php if (isset($errors["speed"])): ?><small class="fieldError"><?= e($errors["speed"]) ?></small><?php endif; ?>
                    </label>
                    <label class="field compactField">
                        <span>Inspiración</span>
                        <input type="text" name="inspiration" value="<?= e(postedText("inspiration")) ?>" maxlength="10">
                    </label>
                    <div class="calculatedCard">
                        <span>Bono de competencia</span>
                        <strong id="proficiencyPreview">+2</strong>
                        <small>Según el nivel</small>
                    </div>
                </div>

                <div class="subsectionHeading">
                    <div>
                        <span>Arsenal</span>
                        <h3>Ataques principales</h3>
                    </div>
                    <p>Puedes dejar filas vacías.</p>
                </div>
                <div class="weaponTable">
                    <div class="weaponHeader"><span>Arma o conjuro</span><span>Ataque</span><span>Daño / tipo</span></div>
                    <?php for ($weaponIndex = 1; $weaponIndex <= 3; $weaponIndex++): ?>
                        <div class="weaponRow">
                            <input type="text" name="weapon_<?= $weaponIndex ?>_name" value="<?= e(postedText("weapon_{$weaponIndex}_name")) ?>" placeholder="Nombre">
                            <input type="text" name="weapon_<?= $weaponIndex ?>_bonus" value="<?= e(postedText("weapon_{$weaponIndex}_bonus")) ?>" placeholder="+0">
                            <input type="text" name="weapon_<?= $weaponIndex ?>_damage" value="<?= e(postedText("weapon_{$weaponIndex}_damage")) ?>" placeholder="1d8 cortante">
                        </div>
                    <?php endfor; ?>
                </div>
                <label class="field">
                    <span>Ataques y lanzamiento de conjuros</span>
                    <textarea name="attacks_spellcasting" rows="4" maxlength="3000"><?= e(postedText("attacks_spellcasting")) ?></textarea>
                </label>
            </section>

            <section class="formStep" data-step="3" aria-labelledby="proficienciesTitle" hidden>
                <div class="stepHeading">
                    <div>
                        <span>Paso 3 de 4</span>
                        <h2 id="proficienciesTitle">Salvaciones y habilidades</h2>
                    </div>
                    <p>Marca las salvaciones competentes y el nivel de dominio de cada habilidad.</p>
                </div>

                <fieldset class="saveFieldset">
                    <legend>Tiradas de salvación competentes</legend>
                    <div class="saveGrid">
                        <?php foreach ($abilityLabels as $ability => $label): ?>
                            <label class="choicePill">
                                <input
                                    type="checkbox"
                                    name="saving_throw_proficiencies[]"
                                    value="<?= e($ability) ?>"
                                    <?= in_array($ability, $selectedSavingThrows, true) ? "checked" : "" ?>
                                >
                                <span><?= e($label) ?></span>
                                <strong data-save-preview="<?= e($ability) ?>">+0</strong>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="skillsGrid">
                    <?php foreach ($skillFields as $skill => $skillInfo): ?>
                        <?php $selectedSkillLevel = postedInt("skill_" . $skill, 0); ?>
                        <label class="skillField">
                            <span>
                                <strong><?= e($skillInfo["label"]) ?></strong>
                                <small><?= e(strtoupper($skillInfo["ability"])) ?></small>
                            </span>
                            <select name="skill_<?= e($skill) ?>" data-skill-ability="<?= e($skillInfo["ability"]) ?>">
                                <option value="0" <?= $selectedSkillLevel === 0 ? "selected" : "" ?>>Sin competencia</option>
                                <option value="1" <?= $selectedSkillLevel === 1 ? "selected" : "" ?>>Competencia</option>
                                <option value="2" <?= $selectedSkillLevel === 2 ? "selected" : "" ?>>Pericia</option>
                            </select>
                            <b data-skill-preview="<?= e($skill) ?>">+0</b>
                        </label>
                    <?php endforeach; ?>
                </div>

                <fieldset class="saveFieldset languageFieldset">
                    <legend>Idiomas conocidos</legend>
                    <p class="fieldHelp">Se guardan como datos del personaje y también se incorporan a la ficha PDF.</p>
                    <div class="languageGrid">
                        <?php foreach ($availableLanguages as $language): ?>
                            <label class="choicePill">
                                <input
                                    type="checkbox"
                                    name="languages[]"
                                    value="<?= e($language) ?>"
                                    <?= in_array($language, $selectedLanguages, true) ? "checked" : "" ?>
                                >
                                <span><?= e($language) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <label class="field">
                        <span>Otros idiomas</span>
                        <input
                            type="text"
                            name="custom_languages"
                            value="<?= e(postedText("custom_languages")) ?>"
                            maxlength="1000"
                            placeholder="Uno o varios, separados por comas"
                        >
                    </label>
                </fieldset>

                <label class="field">
                    <span>Otras competencias</span>
                    <textarea name="proficiencies_languages" rows="5" maxlength="4000" placeholder="Armaduras, armas, herramientas..."><?= e(postedText("proficiencies_languages")) ?></textarea>
                </label>
            </section>

            <section class="formStep" data-step="4" aria-labelledby="storyTitle" hidden>
                <div class="stepHeading">
                    <div>
                        <span>Paso 4 de 4</span>
                        <h2 id="storyTitle">Historia, equipo y rasgos</h2>
                    </div>
                    <p>Estos campos completan las páginas narrativas de la ficha.</p>
                </div>

                <div class="narrativeGrid">
                    <label class="field">
                        <span>Rasgos de personalidad</span>
                        <textarea name="personality_traits" rows="3" maxlength="1500"><?= e(postedText("personality_traits")) ?></textarea>
                    </label>
                    <label class="field">
                        <span>Ideales</span>
                        <textarea name="ideals" rows="3" maxlength="1500"><?= e(postedText("ideals")) ?></textarea>
                    </label>
                    <label class="field">
                        <span>Vínculos</span>
                        <textarea name="bonds" rows="3" maxlength="1500"><?= e(postedText("bonds")) ?></textarea>
                    </label>
                    <label class="field">
                        <span>Defectos</span>
                        <textarea name="flaws" rows="3" maxlength="1500"><?= e(postedText("flaws")) ?></textarea>
                    </label>
                </div>

                <div class="formGrid formGridThree appearanceGrid">
                    <label class="field"><span>Edad</span><input type="text" name="age" value="<?= e(postedText("age")) ?>" maxlength="30"></label>
                    <label class="field"><span>Altura</span><input type="text" name="height" value="<?= e(postedText("height")) ?>" maxlength="30"></label>
                    <label class="field"><span>Peso</span><input type="text" name="weight" value="<?= e(postedText("weight")) ?>" maxlength="30"></label>
                    <label class="field"><span>Ojos</span><input type="text" name="eyes" value="<?= e(postedText("eyes")) ?>" maxlength="40"></label>
                    <label class="field"><span>Piel</span><input type="text" name="skin" value="<?= e(postedText("skin")) ?>" maxlength="40"></label>
                    <label class="field"><span>Cabello</span><input type="text" name="hair" value="<?= e(postedText("hair")) ?>" maxlength="40"></label>
                </div>

                <div class="narrativeGrid narrativeGridWide">
                    <label class="field">
                        <span>Historia</span>
                        <textarea name="backstory" rows="7" maxlength="10000"><?= e(postedText("backstory")) ?></textarea>
                    </label>
                    <label class="field">
                        <span>Rasgos y capacidades</span>
                        <textarea name="features_traits" rows="7" maxlength="5000"><?= e(postedText("features_traits")) ?></textarea>
                    </label>
                    <label class="field">
                        <span>Aliados y organizaciones</span>
                        <textarea name="allies" rows="5" maxlength="5000"><?= e(postedText("allies")) ?></textarea>
                    </label>
                    <label class="field">
                        <span>Rasgos adicionales</span>
                        <textarea name="additional_features" rows="5" maxlength="5000"><?= e(postedText("additional_features")) ?></textarea>
                    </label>
                </div>

                <div class="formGrid formGridTwo">
                    <label class="field">
                        <span>Nombre de facción</span>
                        <input type="text" name="faction_name" value="<?= e(postedText("faction_name")) ?>" maxlength="100">
                    </label>
                    <label class="field">
                        <span>Tesoro</span>
                        <input type="text" name="treasure" value="<?= e(postedText("treasure")) ?>" maxlength="1000">
                    </label>
                </div>

                <div class="equipmentGrid">
                    <label class="field">
                        <span>Equipo</span>
                        <textarea name="equipment" rows="5" maxlength="5000"><?= e(postedText("equipment")) ?></textarea>
                    </label>
                    <fieldset class="currencyFieldset">
                        <legend>Monedas</legend>
                        <div>
                            <?php foreach (["cp" => "PC", "sp" => "PP", "ep" => "PE", "gp" => "PO", "pp" => "PPt"] as $coin => $coinLabel): ?>
                                <label>
                                    <span><?= e($coinLabel) ?></span>
                                    <input type="number" name="coins_<?= e($coin) ?>" value="<?= e(postedInt("coins_" . $coin, 0)) ?>" min="0" max="999999">
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                </div>

                <div class="reviewCard">
                    <span class="builderRune" aria-hidden="true">✦</span>
                    <div>
                        <strong>La ficha está preparada</strong>
                        <p>Al crear el personaje se generarán su PDF rellenable, los datos locales y el grimorio vacío.</p>
                    </div>
                </div>
            </section>

            <footer class="formActions">
                <button type="button" class="secondaryButton" id="previousStep" hidden>Anterior</button>
                <span id="stepStatus">Paso 1 de 4</span>
                <button type="button" class="primaryButton" id="nextStep">Continuar</button>
                <button type="submit" class="primaryButton submitButton" id="submitInput" hidden>
                    Crear personaje
                </button>
            </footer>

            <div class="creationOverlay" id="creationOverlay" hidden aria-live="assertive">
                <span class="creationSpinner" aria-hidden="true"></span>
                <strong>Forjando la ficha...</strong>
                <small>Generando el PDF y preparando el personaje.</small>
            </div>
        </form>

        <noscript>
            <p class="formAlert">Necesitas activar JavaScript para generar la ficha PDF rellenable.</p>
        </noscript>
    </main>
</body>
</html>
