<?php
require_once("../classes/DbConnector.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
$framed = isset($_GET["framed"]) && $_GET["framed"] === "true";
$requestedCharacterId = max(
    0,
    (int) ($_POST["context_character_id"] ?? $_GET["character_id"] ?? 0)
);

if ($userId <= 0) {
    header("Location: ../login.php");
    exit;
}

$characters = [];
$errors = [];
$noteName = "";
$noteDate = date("Y-m-d");
$selectedCharacter = $requestedCharacterId;
$scopedCharacter = null;

try {
    $db = DbConector::singleton();
    $characters = $db->getChars($userId) ?: [];
    $ownedCharacterIds = array_map(
        static fn($character) => (int) $character["id_char"],
        $characters
    );

    if ($requestedCharacterId > 0) {
        foreach ($characters as $character) {
            if ((int) ($character["id_char"] ?? 0) === $requestedCharacterId) {
                $scopedCharacter = $character;
                break;
            }
        }

        if ($scopedCharacter) {
            $characters = [$scopedCharacter];
        } else {
            http_response_code(404);
            $characters = [];
            $errors[] = "No se ha encontrado el personaje de esta anotación.";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $noteName = trim((string) ($_POST["noteName"] ?? ""));
        $noteDate = (string) ($_POST["noteDate"] ?? date("Y-m-d"));
        $selectedCharacter = (int) ($_POST["personaje"] ?? 0);

        if ($noteName === "") {
            $errors[] = "Escribe un título para el apunte.";
        }

        if (!in_array($selectedCharacter, $ownedCharacterIds, true)) {
            $errors[] = "Selecciona uno de tus personajes.";
        }
        if (
            $requestedCharacterId > 0
            && $selectedCharacter !== $requestedCharacterId
        ) {
            $errors[] = "La anotación debe permanecer vinculada al personaje actual.";
        }

        $parsedDate = DateTimeImmutable::createFromFormat("Y-m-d", $noteDate);
        if (!$parsedDate || $parsedDate->format("Y-m-d") !== $noteDate) {
            $errors[] = "La fecha del apunte no es válida.";
        }

        if (!$errors && $db->createNote($userId, $selectedCharacter, $noteName, $noteDate)) {
            header(
                "Location: notes.php?framed="
                . ($framed ? "true" : "false")
                . ($requestedCharacterId > 0
                    ? "&character_id=" . $requestedCharacterId
                    : "")
            );
            exit;
        }

        if (!$errors) {
            $errors[] = "No se pudo crear el apunte.";
        }
    }
} catch (Throwable $exception) {
    $errors[] = "No se pudo abrir el diario.";
}

$backToNotesUrl = "notes.php?framed="
    . ($framed ? "true" : "false")
    . ($requestedCharacterId > 0
        ? "&character_id=" . $requestedCharacterId
        : "");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Nueva nota · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/newNote.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
</head>
<body class="<?= $framed ? "framed" : "" ?>">
    <div class="noteMist"></div>
    <main class="newNotePage">
        <nav class="newNoteBreadcrumb">
            <a href="<?= e($backToNotesUrl) ?>">← Apuntes</a>
            <span>/</span>
            <strong>Nueva entrada</strong>
        </nav>

        <form action="" method="post" id="nnForm">
            <input
                type="hidden"
                name="context_character_id"
                value="<?= $requestedCharacterId ?>"
            >
            <header class="newNoteHeader">
                <div>
                    <span class="sectionKicker">Nueva crónica</span>
                    <h1>Crear apunte</h1>
                    <p>Elige al protagonista y abre una nueva página en su diario.</p>
                </div>
                <span class="headerRune" aria-hidden="true">▤</span>
            </header>

            <?php if ($errors): ?>
                <div class="formErrors" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <span><?= e($error) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="noteDetails">
                <label>
                    <span>Título del apunte</span>
                    <input
                        type="text"
                        name="noteName"
                        id="noteName"
                        value="<?= e($noteName) ?>"
                        placeholder="Ej. El secreto de la torre"
                        maxlength="255"
                        required
                    >
                </label>
                <label>
                    <span>Fecha de la crónica</span>
                    <input type="date" name="noteDate" id="noteDate" value="<?= e($noteDate) ?>" required>
                </label>
            </section>

            <section class="characterSelection">
                <div class="selectionHeader">
                    <div>
                        <span class="sectionKicker">Protagonista</span>
                        <h2><?= $scopedCharacter ? "Personaje vinculado" : "Vincular a un personaje" ?></h2>
                    </div>
                    <small><?= count($characters) ?> disponibles</small>
                </div>

                <?php if ($characters): ?>
                    <div class="selectableCharacters">
                        <?php foreach ($characters as $index => $character): ?>
                            <?php
                                $name = (string) ($character["name"] ?? "Personaje");
                                $imageName = (string) ($character["image_path"] ?? "");
                                $imageDisk = __DIR__ . "/../resources/chars/" . $name . "/" . $imageName;
                                $isChecked = $selectedCharacter > 0
                                    ? $selectedCharacter === (int) $character["id_char"]
                                    : $index === 0;
                            ?>
                            <label class="selectableChar">
                                <input
                                    type="radio"
                                    name="personaje"
                                    value="<?= (int) $character["id_char"] ?>"
                                    <?= $isChecked ? "checked" : "" ?>
                                    required
                                >
                                <span class="selectableCharContent">
                                    <span class="selectedMark" aria-hidden="true">✓</span>
                                    <?php if ($imageName !== "" && is_file($imageDisk)): ?>
                                        <img
                                            src="../resources/chars/<?= rawurlencode($name) ?>/<?= rawurlencode($imageName) ?>"
                                            alt=""
                                        >
                                    <?php else: ?>
                                        <span class="selectableFallback"><?= e(substr($name, 0, 1)) ?></span>
                                    <?php endif; ?>
                                    <strong><?= e($name) ?></strong>
                                    <small>
                                        <?= e($character["raza"] ?? "Aventurero") ?>
                                        <?php if (!empty($character["subraza"])): ?>
                                            · <?= e($character["subraza"]) ?>
                                        <?php endif; ?>
                                    </small>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="noCharacters">
                        <span aria-hidden="true">♙</span>
                        <p>Necesitas un personaje antes de crear un apunte.</p>
                        <a href="addPersonajes.php">Crear personaje</a>
                    </div>
                <?php endif; ?>
            </section>

            <footer class="formActions">
                <a href="<?= e($backToNotesUrl) ?>">Cancelar</a>
                <button type="submit" name="submit" <?= !$characters ? "disabled" : "" ?>>
                    Crear apunte <span>→</span>
                </button>
            </footer>
        </form>
    </main>
</body>
</html>
