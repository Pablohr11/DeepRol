<?php

/*
 * PHP puede emitir avisos de subida antes de ejecutar este archivo (por ejemplo,
 * cuando usa el directorio temporal del sistema). Si output_buffering está activo,
 * retiramos esa salida para mantener el contrato JSON del endpoint.
 */
if (ob_get_level() > 0) {
    ob_clean();
} else {
    ob_start();
}
ini_set("display_errors", "0");

require_once __DIR__ . "/../classes/DbConnector.php";
require_once __DIR__ . "/../classes/CharacterOptionCatalog.php";
require_once __DIR__ . "/../classes/CharacterSheetUpdater.php";
require_once __DIR__ . "/../classes/CharacterProgression.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

function respondJson(int $status, array $payload): void
{
    http_response_code($status);
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    if ($json === false) {
        http_response_code(500);
        $json = '{"ok":false,"message":"No se ha podido preparar la respuesta del guardado."}';
    }

    echo $json;
    exit;
}

function postedValue(string $name): string
{
    return trim((string) ($_POST[$name] ?? ""));
}

function validateCharacterIdentity(array $character): array
{
    $classNames = isset($_POST["class_names"]) && is_array($_POST["class_names"])
        ? $_POST["class_names"]
        : [postedValue("class_name")];
    $subclassNames = isset($_POST["subclass_names"]) && is_array($_POST["subclass_names"])
        ? $_POST["subclass_names"]
        : [postedValue("subclass_name")];
    $classLevels = isset($_POST["class_levels"]) && is_array($_POST["class_levels"])
        ? $_POST["class_levels"]
        : [$_POST["level"] ?? 1];
    $raceName = postedValue("race_name");
    $subraceName = postedValue("subrace_name");
    $classes = [];
    $usedClasses = [];
    $totalLevel = 0;

    foreach (array_slice($classNames, 0, 13) as $index => $rawClassName) {
        if (is_array($rawClassName)) {
            throw new InvalidArgumentException("Una de las clases no es válida.");
        }

        $className = trim((string) $rawClassName);
        $subclassName = isset($subclassNames[$index]) && !is_array($subclassNames[$index])
            ? trim((string) $subclassNames[$index])
            : "";
        $level = filter_var($classLevels[$index] ?? null, FILTER_VALIDATE_INT);
        if ($level === false || $level === null || $level < 1 || $level > 20) {
            throw new InvalidArgumentException("Cada clase debe tener entre 1 y 20 niveles.");
        }

        $classOption = CharacterOptionCatalog::findClass($className);
        if ($classOption === null) {
            throw new InvalidArgumentException("Selecciona clases válidas.");
        }
        if (isset($usedClasses[$className])) {
            throw new InvalidArgumentException("No puedes añadir dos veces la misma clase.");
        }

        $subclasses = is_array($classOption["subclasses"] ?? null)
            ? $classOption["subclasses"]
            : [];
        $subclassLevel = max(1, (int) ($classOption["subclassLevel"] ?? 1));
        if ($level >= $subclassLevel && $subclasses && $subclassName === "") {
            throw new InvalidArgumentException("Selecciona la subclase obtenida por cada clase.");
        }
        if (
            $subclassName !== ""
            && !CharacterOptionCatalog::hasNamedOption($subclasses, $subclassName)
        ) {
            throw new InvalidArgumentException("Una subclase no pertenece a la clase seleccionada.");
        }
        if ($level < $subclassLevel && $subclassName !== "") {
            throw new InvalidArgumentException(
                "Una subclase se ha seleccionado antes del nivel en que se obtiene."
            );
        }

        $usedClasses[$className] = true;
        $totalLevel += (int) $level;
        $classes[] = [
            "class_name" => $className,
            "class_label" => trim((string) ($classOption["label"] ?? $className)),
            "subclass_name" => $subclassName,
            "level" => (int) $level,
            "is_primary" => $index === 0,
        ];
    }

    if (!$classes) {
        throw new InvalidArgumentException("El personaje necesita al menos una clase.");
    }
    if ($totalLevel > 20) {
        throw new InvalidArgumentException("La suma de niveles de clase no puede superar 20.");
    }

    $raceOption = CharacterOptionCatalog::findRace($raceName);
    if ($raceOption === null) {
        throw new InvalidArgumentException("Selecciona una raza o linaje válido.");
    }

    $subraces = is_array($raceOption["subraces"] ?? null)
        ? $raceOption["subraces"]
        : [];
    if (
        $subraceName !== ""
        && !CharacterOptionCatalog::hasNamedOption($subraces, $subraceName)
    ) {
        throw new InvalidArgumentException("La subraza no pertenece a la raza seleccionada.");
    }

    return [
        "character_name" => (string) $character["name"],
        "class_name" => $classes[0]["class_name"],
        "class_label" => $classes[0]["class_label"],
        "subclass_name" => $classes[0]["subclass_name"],
        "classes" => $classes,
        "race_name" => $raceName,
        "race_label" => trim((string) ($raceOption["label"] ?? $raceName)),
        "subrace_name" => $subraceName,
        "level" => $totalLevel,
        "languages" => CharacterProgression::normalizeLanguages(
            (array) ($_POST["languages"] ?? []),
            postedValue("custom_languages")
        ),
        "saving_throw_proficiencies" => isset($_POST["saving_throw_proficiencies_present"])
                ? CharacterProgression::normalizeSavingThrowProficiencies(
                    is_array($_POST["saving_throw_proficiencies"] ?? null)
                        ? $_POST["saving_throw_proficiencies"]
                        : []
                )
                : null,
        "skill_proficiencies" => isset($_POST["skill_proficiencies"])
            && is_array($_POST["skill_proficiencies"])
                ? CharacterProgression::normalizeSkillProficiencies(
                    $_POST["skill_proficiencies"]
                )
                : null,
        "other_proficiencies" => postedValue("other_proficiencies"),
    ];
}

function validatePdfUpload(array $upload, bool $required): ?array
{
    $error = (int) ($upload["error"] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE && !$required) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException("No se ha podido recibir la ficha PDF.");
    }
    if ((int) ($upload["size"] ?? 0) <= 0 || (int) $upload["size"] > 20 * 1024 * 1024) {
        throw new InvalidArgumentException("La ficha PDF debe ocupar menos de 20 MB.");
    }

    $temporaryPath = (string) ($upload["tmp_name"] ?? "");
    $header = file_get_contents($temporaryPath, false, null, 0, 5);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
    if (
        $header !== "%PDF-"
        || !in_array($mime, ["application/pdf", "application/octet-stream"], true)
    ) {
        throw new InvalidArgumentException("El archivo seleccionado no es un PDF válido.");
    }

    return [
        "tmp_name" => $temporaryPath,
        "size" => (int) $upload["size"],
    ];
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respondJson(405, ["ok" => false, "message" => "Método no permitido."]);
}

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
if ($userId <= 0) {
    respondJson(401, ["ok" => false, "message" => "Vuelve a iniciar sesión para actualizar la ficha."]);
}

$csrfCookie = (string) ($_COOKIE["deeprol_character_update_csrf"] ?? "");
$csrfToken = postedValue("csrf_token");
if (
    $csrfCookie === ""
    || $csrfToken === ""
    || !hash_equals($csrfCookie, $csrfToken)
) {
    respondJson(403, [
        "ok" => false,
        "message" => "La sesión de edición ha caducado. Recarga la página e inténtalo de nuevo.",
    ]);
}

$characterId = filter_var($_POST["character_id"] ?? null, FILTER_VALIDATE_INT);
$action = postedValue("action");
if (
    $characterId === false
    || $characterId === null
    || $characterId <= 0
    || !in_array($action, ["fields", "pdf"], true)
) {
    respondJson(422, ["ok" => false, "message" => "La solicitud de actualización no es válida."]);
}

$pdfTarget = "";
$sheetPath = "";
$originalSheetJson = null;

try {
    $db = DbConector::singleton();
    $character = $db->getCharForUser((int) $characterId, $userId);
    if (!$character) {
        respondJson(404, ["ok" => false, "message" => "No se ha encontrado este personaje."]);
    }

    $identity = validateCharacterIdentity($character);
    $submittedFields = $action === "pdf"
        ? CharacterSheetUpdater::decodePdfFields((string) ($_POST["fields"] ?? "{}"))
        : CharacterSheetUpdater::decodeSubmittedFields((string) ($_POST["fields"] ?? ""));

    $characterDirectoryName = basename((string) $character["name"]);
    if (
        $characterDirectoryName === ""
        || $characterDirectoryName === "."
        || $characterDirectoryName === ".."
    ) {
        throw new RuntimeException("La carpeta del personaje no es válida.");
    }

    $charactersRoot = dirname(__DIR__)
        . DIRECTORY_SEPARATOR . "resources"
        . DIRECTORY_SEPARATOR . "chars";
    $characterDirectory = $charactersRoot
        . DIRECTORY_SEPARATOR . $characterDirectoryName;
    if (!is_dir($characterDirectory)) {
        if (!mkdir($characterDirectory, 0775, true) && !is_dir($characterDirectory)) {
            throw new RuntimeException("No se ha podido preparar la carpeta del personaje.");
        }
    }

    $sheetPath = $characterDirectory . DIRECTORY_SEPARATOR . "sheet.json";
    if (is_file($sheetPath)) {
        $originalSheetJson = file_get_contents($sheetPath);
    }
    $existingFields = [];
    if (is_string($originalSheetJson)) {
        $decodedExistingFields = json_decode($originalSheetJson, true);
        if (is_array($decodedExistingFields)) {
            $existingFields = $decodedExistingFields;
        }
    }

    $fields = CharacterSheetUpdater::compose(
        $existingFields,
        $submittedFields,
        $identity
    );

    $uploadKey = $action === "pdf" ? "updated_pdf" : "generated_pdf";
    $upload = validatePdfUpload(
        is_array($_FILES[$uploadKey] ?? null) ? $_FILES[$uploadKey] : [],
        $action === "pdf"
    );
    $pdfFilename = basename((string) ($character["pdf_path"] ?? ""));

    if ($upload !== null) {
        $pdfFilename = "ficha-actualizada-"
            . date("Ymd-His")
            . "-"
            . bin2hex(random_bytes(3))
            . ".pdf";
        $pdfTarget = $characterDirectory . DIRECTORY_SEPARATOR . $pdfFilename;
        if (!move_uploaded_file($upload["tmp_name"], $pdfTarget)) {
            throw new RuntimeException("No se ha podido guardar la nueva ficha PDF.");
        }
    }

    CharacterSheetUpdater::writeJson($sheetPath, $fields);

    $updated = $db->updateCharacterSheetMetadata(
        (int) $characterId,
        $userId,
        $identity["race_name"],
        $identity["subrace_name"],
        $identity["level"],
        $identity["class_name"],
        $identity["subclass_name"],
        $pdfFilename,
        $identity["classes"],
        $identity["languages"]
    );
    if (!$updated) {
        throw new RuntimeException("No se han podido actualizar los datos del personaje.");
    }

    respondJson(200, [
        "ok" => true,
        "message" => $action === "pdf"
            ? "La nueva ficha PDF se ha importado correctamente."
            : "Los cambios de la ficha se han guardado.",
        "fields" => $fields,
        "pdf" => $pdfFilename,
        "reload" => true,
    ]);
} catch (InvalidArgumentException $exception) {
    respondJson(422, ["ok" => false, "message" => $exception->getMessage()]);
} catch (Throwable $exception) {
    if ($pdfTarget !== "" && is_file($pdfTarget)) {
        unlink($pdfTarget);
    }
    if ($sheetPath !== "") {
        if (is_string($originalSheetJson)) {
            file_put_contents($sheetPath, $originalSheetJson, LOCK_EX);
        } elseif (is_file($sheetPath)) {
            unlink($sheetPath);
        }
    }

    error_log("DeepRol updateCharacterSheet: " . $exception->getMessage());
    respondJson(500, [
        "ok" => false,
        "message" => "No se ha podido guardar la ficha. No se han aplicado cambios.",
    ]);
}
