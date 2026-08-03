<?php
require_once("../classes/DbConnector.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
$framed = isset($_GET["framed"]) && $_GET["framed"] === "true";
$requestedCharacterId = isset($_GET["character_id"])
    ? max(0, (int) $_GET["character_id"])
    : 0;

if ($userId <= 0) {
    header("Location: ../login.php");
    exit;
}

$notes = [];
$characters = [];
$loadError = false;
$scopedCharacter = null;

try {
    $db = DbConector::singleton();
    if ($requestedCharacterId > 0) {
        $candidateCharacter = $db->getCharForUser($requestedCharacterId, $userId);
        if (!$candidateCharacter) {
            http_response_code(404);
            $loadError = true;
        } else {
            $scopedCharacter = $candidateCharacter;
            $notes = $db->getNotes($userId, $requestedCharacterId) ?: [];
            $characters[$requestedCharacterId] = $candidateCharacter;
        }
    } else {
        $notes = $db->getNotes($userId) ?: [];
        $noteCharacters = $db->getNoteChars($userId) ?: [];

        foreach ($noteCharacters as $characterId => $characterRows) {
            if (!empty($characterRows[0])) {
                $characters[(int) $characterId] = $characterRows[0];
                $characters[(int) $characterId]["id_char"] = (int) $characterId;
            }
        }
    }
} catch (Throwable $exception) {
    $loadError = true;
}

$noteCount = 0;
foreach ($notes as $characterNotes) {
    $noteCount += count($characterNotes);
}
$characterQuery = $scopedCharacter
    ? "&character_id=" . $requestedCharacterId
    : "";
$newNoteUrl = "newNote.php?framed="
    . ($framed ? "true" : "false")
    . $characterQuery;
$scopedCharacterName = (string) ($scopedCharacter["name"] ?? "");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Apuntes · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/notes.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script defer src="../scripts/notes.js"></script>
</head>
<body class="<?= $framed ? "framed" : "" ?><?= $scopedCharacter ? " characterScoped" : "" ?>">
    <main class="notesPage">
        <header class="notesHero">
            <div>
                <span class="sectionKicker">
                    <?= $scopedCharacter ? "Diario de personaje" : "Crónicas de campaña" ?>
                </span>
                <h1>
                    <?= $scopedCharacter
                        ? "Apuntes de " . e($scopedCharacterName)
                        : "Apuntes" ?>
                </h1>
                <p>
                    <?= $scopedCharacter
                        ? "Notas vinculadas exclusivamente a este personaje."
                        : "Ideas, pistas y recuerdos vinculados a cada héroe de tu compañía." ?>
                </p>
            </div>
            <div class="notesCount">
                <strong><?= $noteCount ?></strong>
                <span><?= $noteCount === 1 ? "entrada guardada" : "entradas guardadas" ?></span>
            </div>
        </header>

        <section class="notesToolbar">
            <form id="notesFilter" role="search">
                <label class="notesSearch">
                    <span aria-hidden="true">⌕</span>
                    <input id="noteSearch" type="search" placeholder="Buscar en tus apuntes..." autocomplete="off">
                </label>
                <?php if (!$scopedCharacter): ?>
                    <div class="notesFilterDiv" aria-label="Filtrar por personaje">
                        <input type="radio" id="note-filter-all" value="all" name="characterFilter" checked>
                        <label for="note-filter-all">Todos</label>
                        <?php foreach ($characters as $characterId => $character): ?>
                            <input type="radio" id="note-filter-<?= $characterId ?>" value="<?= $characterId ?>" name="characterFilter">
                            <label for="note-filter-<?= $characterId ?>"><?= e($character["name"] ?? "Personaje") ?></label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </form>
            <a class="newNoteButton" href="<?= e($newNoteUrl) ?>">
                <span aria-hidden="true">＋</span>
                Nueva nota
            </a>
        </section>

        <?php if ($loadError): ?>
            <section class="notesEmpty">
                <span aria-hidden="true">☾</span>
                <h2>No se pudo abrir el diario</h2>
                <p>Los apuntes no están disponibles en este momento.</p>
            </section>
        <?php elseif (!$notes): ?>
            <section class="notesEmpty">
                <span aria-hidden="true">▤</span>
                <h2>
                    <?= $scopedCharacter
                        ? e($scopedCharacterName) . " todavía no tiene apuntes"
                        : "El diario todavía está en blanco" ?>
                </h2>
                <p>
                    <?= $scopedCharacter
                        ? "Crea la primera anotación de este personaje."
                        : "Crea una nota y empieza a registrar tu aventura." ?>
                </p>
                <a href="<?= e($newNoteUrl) ?>">Escribir la primera nota</a>
            </section>
        <?php else: ?>
            <section class="noteList" id="noteList">
                <?php foreach ($notes as $characterId => $characterNotes): ?>
                    <?php
                        $characterId = (int) $characterId;
                        $character = $characters[$characterId] ?? ["name" => "Personaje", "image_path" => ""];
                        $characterName = (string) ($character["name"] ?? "Personaje");
                        $imageName = (string) ($character["image_path"] ?? "");
                        $imageDisk = __DIR__ . "/../resources/chars/" . $characterName . "/" . $imageName;
                    ?>
                    <article class="charGroupedNotes" data-character-id="<?= $characterId ?>">
                        <header class="charGroupedNotesHeader">
                            <?php if ($imageName !== "" && is_file($imageDisk)): ?>
                                <img
                                    class="charListImage"
                                    src="../resources/chars/<?= rawurlencode($characterName) ?>/<?= rawurlencode($imageName) ?>"
                                    alt=""
                                >
                            <?php else: ?>
                                <span class="notePortraitFallback"><?= e(substr($characterName, 0, 1)) ?></span>
                            <?php endif; ?>
                            <div>
                                <span class="sectionKicker">Diario de personaje</span>
                                <h2><?= e($characterName) ?></h2>
                            </div>
                            <strong><?= count($characterNotes) ?></strong>
                        </header>

                        <div class="actualNotes">
                            <?php foreach ($characterNotes as $note): ?>
                                <?php
                                    $title = (string) ($note["Nombre"] ?? "Nota sin título");
                                    $preview = trim(strip_tags((string) ($note["Value"] ?? "")));
                                    if ($preview === "") {
                                        $preview = "Abre esta entrada para continuar escribiendo la historia.";
                                    }
                                    $dateValue = (string) ($note["Date"] ?? "");
                                    $date = DateTimeImmutable::createFromFormat("Y-m-d", $dateValue);
                                ?>
                                <a
                                    class="noteCard"
                                    href="note.php?id=<?= (int) ($note["ID"] ?? 0) ?>&framed=<?= $framed ? "true" : "false" ?><?= e($characterQuery) ?>"
                                    data-note-search="<?= e(strtolower($title . " " . $preview . " " . $characterName)) ?>"
                                >
                                    <span class="noteIcon" aria-hidden="true">▤</span>
                                    <span class="noteCardCopy">
                                        <strong><?= e($title) ?></strong>
                                        <small><?= e(function_exists("mb_strimwidth") ? mb_strimwidth($preview, 0, 120, "…", "UTF-8") : substr($preview, 0, 117) . (strlen($preview) > 117 ? "..." : "")) ?></small>
                                    </span>
                                    <time datetime="<?= e($dateValue) ?>"><?= e($date ? $date->format("d · m · Y") : $dateValue) ?></time>
                                    <span class="noteArrow" aria-hidden="true">→</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="notesNoResults" id="notesNoResults" hidden>
                <span aria-hidden="true">⌕</span>
                No hay apuntes que coincidan con los filtros.
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
