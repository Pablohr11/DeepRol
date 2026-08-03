<?php
require_once("../classes/DbConnector.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
$characters = [];
$loadError = false;

if ($userId > 0) {
    try {
        $db = DbConector::singleton();
        $characters = $db->getChars($userId) ?: [];
    } catch (Throwable $exception) {
        $loadError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Personajes · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/personajes.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script defer src="../scripts/personajes.js"></script>
</head>
<body>
    <main class="charactersPage">
        <header class="charactersHero">
            <div>
                <span class="sectionKicker">Tu compañía</span>
                <h1>Personajes</h1>
                <p>Reúne a tus héroes, consulta sus fichas y prepara la siguiente aventura.</p>
            </div>
            <div class="characterCount">
                <strong><?= count($characters) ?></strong>
                <span><?= count($characters) === 1 ? "héroe preparado" : "héroes preparados" ?></span>
            </div>
        </header>

        <section class="charactersToolbar" aria-label="Herramientas de personajes">
            <label class="characterSearch">
                <span aria-hidden="true">⌕</span>
                <input id="characterSearch" type="search" placeholder="Buscar por nombre o raza..." autocomplete="off">
            </label>
            <?php if ($userId > 0): ?>
                <a class="newCharacterButton" href="addPersonajes.php">
                    <span aria-hidden="true">＋</span>
                    Nuevo personaje
                </a>
            <?php else: ?>
                <a class="newCharacterButton" href="../login.php">Iniciar sesión</a>
            <?php endif; ?>
        </section>

        <?php if ($loadError): ?>
            <section class="emptyState">
                <span class="emptyRune" aria-hidden="true">☾</span>
                <h2>No se pudo abrir el códice</h2>
                <p>La colección de personajes no está disponible en este momento.</p>
            </section>
        <?php elseif ($userId === 0): ?>
            <section class="emptyState">
                <span class="emptyRune" aria-hidden="true">♙</span>
                <h2>Tu compañía te espera</h2>
                <p>Inicia sesión para consultar tus personajes y sus fichas.</p>
                <a href="../login.php">Entrar en DeepRol</a>
            </section>
        <?php else: ?>
            <section class="charactersGrid" id="charactersGrid">
                <?php foreach ($characters as $index => $character): ?>
                    <?php
                        $name = (string) ($character["name"] ?? "Personaje");
                        $safeDirectoryName = basename($name);
                        $characterDirectory = __DIR__ . "/../resources/chars/" . $safeDirectoryName;
                        $imageName = basename((string) ($character["image_path"] ?? ""));
                        $imageFile = $characterDirectory . "/" . $imageName;
                        $hasImage = $imageName !== "" && is_file($imageFile);
                        $configuredPdfName = basename((string) ($character["pdf_path"] ?? ""));
                        $hasPdf = (
                            $configuredPdfName !== ""
                            && is_file($characterDirectory . "/" . $configuredPdfName)
                        ) || is_file($characterDirectory . "/ficha.pdf");
                    ?>
                    <article
                        class="characterCard accent<?= ($index % 4) + 1 ?><?= $hasPdf ? "" : " isIncomplete" ?>"
                        data-character="<?= e(strtolower(
                            $name
                            . " " . ($character["raza"] ?? "")
                            . " " . ($character["subraza"] ?? "")
                            . " " . ($character["clase"] ?? "")
                            . " " . ($character["subclase"] ?? "")
                        )) ?>"
                    >
                        <a class="characterImage" href="personaje.php?id=<?= (int) $character["id_char"] ?>">
                            <?php if ($hasImage): ?>
                                <img
                                    src="../resources/chars/<?= rawurlencode($name) ?>/<?= rawurlencode($imageName) ?>"
                                    alt="Retrato de <?= e($name) ?>"
                                >
                            <?php else: ?>
                                <span class="portraitFallback" aria-hidden="true"><?= e(substr($name, 0, 1)) ?></span>
                            <?php endif; ?>
                            <span class="characterStatus"><i></i> <?= $hasPdf ? "Disponible" : "Datos básicos" ?></span>
                            <span class="viewCharacter">Ver ficha <b>→</b></span>
                        </a>
                        <div class="characterInfo">
                            <span class="characterNumber">HÉROE <?= str_pad((string) ($index + 1), 2, "0", STR_PAD_LEFT) ?></span>
                            <h2><?= e($name) ?></h2>
                            <p>
                                <?= e($character["raza"] ?? "Linaje desconocido") ?>
                                <?php if (!empty($character["subraza"])): ?>
                                    · <?= e($character["subraza"]) ?>
                                <?php endif; ?>
                                <?php if (!empty($character["clase"])): ?>
                                    · <?= e($character["clase"]) ?>
                                    <?php if (!empty($character["subclase"])): ?>
                                        / <?= e($character["subclase"]) ?>
                                    <?php endif; ?>
                                    nivel <?= max(1, (int) ($character["nivel"] ?? 1)) ?>
                                <?php endif; ?>
                            </p>
                            <footer>
                                <span><i aria-hidden="true">⌁</i> <?= $hasPdf ? "Ficha enlazada" : "Sin ficha PDF" ?></span>
                                <a href="personaje.php?id=<?= (int) $character["id_char"] ?>" aria-label="Abrir <?= e($name) ?>">•••</a>
                            </footer>
                        </div>
                    </article>
                <?php endforeach; ?>

                <article class="addCharacter">
                    <video id="background-video" loop muted playsinline poster="../resources/imgs/book.jpg">
                        <source src="../resources/vids/book.mp4" type="video/mp4">
                    </video>
                    <div class="addOverlay"></div>
                    <a id="addCharButton" href="addPersonajes.php">
                        <span class="addIcon" aria-hidden="true">＋</span>
                        <strong>Crear personaje</strong>
                        <small>Abre una nueva página en tu historia</small>
                    </a>
                </article>
            </section>

            <section class="noResults" id="noResults" hidden>
                <span aria-hidden="true">⌕</span>
                No hay personajes que coincidan con la búsqueda.
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
