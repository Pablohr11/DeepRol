<?php
require_once("../classes/DbConnector.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
$noteId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$framed = isset($_GET["framed"]) && $_GET["framed"] === "true";
$requestedCharacterId = isset($_GET["character_id"])
    ? max(0, (int) $_GET["character_id"])
    : 0;
$noteInfo = null;
$relatedCharName = "Personaje";

if ($userId <= 0) {
    header("Location: ../login.php");
    exit;
}

try {
    if ($noteId > 0) {
        $db = DbConector::singleton();
        $candidateNote = $db->getNote($noteId);

        if ($candidateNote && (int) ($candidateNote["ID_User"] ?? 0) === $userId) {
            $noteInfo = $candidateNote;
            $relatedCharName = $db->getCharName((int) $noteInfo["RelatedChar"]);
            if (
                $requestedCharacterId > 0
                && $requestedCharacterId !== (int) $noteInfo["RelatedChar"]
            ) {
                $requestedCharacterId = 0;
            }
        }
    }
} catch (Throwable $exception) {
    $noteInfo = null;
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
    <title><?= $noteInfo ? e($noteInfo["Nombre"]) : "Apunte no disponible" ?> · DeepRol</title>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/note.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
</head>
<body class="<?= $framed ? "framed" : "" ?>">
    <div class="mist"></div>

    <?php if ($noteInfo): ?>
        <main class="editorPage">
            <header class="editorHeader">
                <a class="backToNotes" href="<?= e($backToNotesUrl) ?>">← Apuntes</a>
                <div class="editorTitle">
                    <span class="editorKicker">Diario de <?= e($relatedCharName) ?></span>
                    <h1><?= e($noteInfo["Nombre"]) ?></h1>
                    <p>
                        <time datetime="<?= e($noteInfo["Date"] ?? "") ?>"><?= e($noteInfo["Date"] ?? "") ?></time>
                        <span>•</span>
                        <span id="saveStatus" class="saved"><i></i> Guardado</span>
                    </p>
                </div>
                <span class="editorRune" aria-hidden="true">▤</span>
            </header>

            <section id="editorContainer" aria-label="Editor del apunte">
                <div id="editor"><?= $noteInfo["Value"] ?></div>
            </section>
        </main>
    <?php else: ?>
        <main class="noteError">
            <span aria-hidden="true">▤</span>
            <h1>Apunte no disponible</h1>
            <p>No hemos podido abrir esta entrada del diario.</p>
            <a href="<?= e($backToNotesUrl) ?>">Volver a apuntes</a>
        </main>
    <?php endif; ?>

<?php if ($noteInfo): ?>
<script>
const noteId = <?= $noteId ?>;
const saveStatus = document.getElementById("saveStatus");

if (window.Quill) {
    const Font = Quill.import("formats/font");
    Font.whitelist = ["sans", "serif", "cinzel", "uncial", "merriweather", "librebaskerville", "ebgaramond"];
    Quill.register(Font, true);

    const toolbarOptions = [
        [{ header: [1, 2, 3, 4, false] }],
        [{ size: ["small", false, "large", "huge"] }],
        [{ font: Font.whitelist }],
        ["bold", "italic", "underline", "strike", "blockquote"],
        ["link", "image"],
        [{ list: "ordered" }, { list: "bullet" }, { list: "check" }],
        [{ script: "sub" }, { script: "super" }],
        [{ color: [] }, { background: [] }],
        ["clean"]
    ];

    const quill = new Quill("#editor", {
        modules: { toolbar: toolbarOptions },
        theme: "snow"
    });

    const saveButton = document.createElement("button");
    saveButton.type = "button";
    saveButton.className = "ql-custom-button";
    saveButton.innerHTML = "<span>Guardar</span>";
    saveButton.setAttribute("aria-label", "Guardar apunte");

    const wrapper = document.createElement("span");
    wrapper.className = "ql-formats saveFormat";
    wrapper.appendChild(saveButton);
    quill.getModule("toolbar").container.appendChild(wrapper);

    quill.on("text-change", (delta, oldDelta, source) => {
        if (source === "user") {
            saveStatus.className = "pending";
            saveStatus.innerHTML = "<i></i> Cambios sin guardar";
        }
    });

    saveButton.addEventListener("click", async () => {
        saveButton.disabled = true;
        saveStatus.className = "saving";
        saveStatus.innerHTML = "<i></i> Guardando...";

        try {
            const body = new URLSearchParams({
                noteId: String(noteId),
                value: quill.root.innerHTML
            });
            const response = await fetch("../src/saveNote.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body
            });

            if (!response.ok) throw new Error("No se pudo guardar");

            saveStatus.className = "saved";
            saveStatus.innerHTML = "<i></i> Guardado";
        } catch (error) {
            saveStatus.className = "error";
            saveStatus.innerHTML = "<i></i> Error al guardar";
        } finally {
            saveButton.disabled = false;
        }
    });
} else {
    saveStatus.className = "error";
    saveStatus.innerHTML = "<i></i> Editor no disponible";
}
</script>
<?php endif; ?>
</body>
</html>
