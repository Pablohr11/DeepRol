<?php

require_once("../classes/DbConnector.php");
require_once("../classes/GlobalSearchService.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

$query = GlobalSearchService::cleanQuery(isset($_GET["q"]) ? (string) $_GET["q"] : "");
$userId = isset($_COOKIE["logged"]) ? max(0, (int) $_COOKIE["logged"]) : 0;
$results = [
    "query" => $query,
    "minimumLength" => 2,
    "groups" => [],
    "total" => 0,
];
$loadError = false;

if ($query !== "") {
    try {
        $database = null;
        try {
            $database = DbConector::singleton();
        } catch (Throwable $databaseException) {
            $database = null;
        }
        $search = new GlobalSearchService($database);
        $results = $search->search($query, $userId, 30);
    } catch (Throwable $exception) {
        $loadError = true;
    }
}

$queryIsLongEnough = (
    function_exists("mb_strlen")
        ? mb_strlen($query, "UTF-8")
        : strlen($query)
) >= (int) $results["minimumLength"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Búsqueda global · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/search.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script defer src="../scripts/search.js"></script>
</head>
<body>
    <main class="searchPage">
        <header class="searchHero">
            <div>
                <span class="searchKicker">Archivo de DeepRol</span>
                <h1>Búsqueda global</h1>
                <p>Personajes, conjuros, bestiario, linajes, clases y apuntes en un único lugar.</p>
            </div>
            <?php if ($queryIsLongEnough && !$loadError): ?>
                <div class="searchCount" aria-label="<?= (int) $results["total"] ?> resultados mostrados">
                    <strong><?= (int) $results["total"] ?></strong>
                    <span>resultados mostrados</span>
                </div>
            <?php endif; ?>
        </header>

        <form class="searchPageForm" action="search.php" method="get" role="search">
            <label for="searchPageInput">Buscar en toda la aplicación</label>
            <div>
                <span aria-hidden="true">⌕</span>
                <input
                    id="searchPageInput"
                    name="q"
                    type="search"
                    value="<?= e($query) ?>"
                    placeholder="Ej.: dragón, druida, evocación..."
                    autocomplete="off"
                    minlength="2"
                    maxlength="80"
                    autofocus
                >
                <button type="submit">Buscar</button>
            </div>
        </form>

        <?php if ($loadError): ?>
            <section class="searchEmpty" role="status">
                <span aria-hidden="true">!</span>
                <h2>No se pudo completar la búsqueda</h2>
                <p>Revisa que la base de datos esté disponible e inténtalo de nuevo.</p>
            </section>
        <?php elseif (!$queryIsLongEnough): ?>
            <section class="searchEmpty">
                <span aria-hidden="true">⌕</span>
                <h2>Busca en todo tu archivo</h2>
                <p>Escribe al menos dos caracteres para comenzar.</p>
            </section>
        <?php elseif (empty($results["groups"])): ?>
            <section class="searchEmpty" role="status">
                <span aria-hidden="true">◇</span>
                <h2>No hay resultados para «<?= e($query) ?>»</h2>
                <p>Prueba con un nombre, una escuela de magia, una clase o un término más corto.</p>
            </section>
        <?php else: ?>
            <section class="searchResults" aria-label="Resultados para <?= e($query) ?>">
                <?php foreach ($results["groups"] as $group): ?>
                    <article class="searchGroup">
                        <header>
                            <div>
                                <span><?= e($group["results"][0]["badge"] ?? "DR") ?></span>
                                <h2><?= e($group["label"] ?? "Resultados") ?></h2>
                            </div>
                            <strong><?= count($group["results"] ?? []) ?><?= !empty($group["hasMore"]) ? "+" : "" ?></strong>
                        </header>
                        <div class="searchGroupList">
                            <?php foreach (($group["results"] ?? []) as $result): ?>
                                <a
                                    class="searchResultCard"
                                    href="<?= e($result["path"] ?? "home.php") ?>"
                                    data-search-result
                                    data-page="<?= e($result["page"] ?? $group["page"] ?? "") ?>"
                                >
                                    <span class="searchResultBadge" aria-hidden="true">
                                        <?= e($result["badge"] ?? "DR") ?>
                                    </span>
                                    <span class="searchResultCopy">
                                        <strong><?= e($result["title"] ?? "Resultado") ?></strong>
                                        <?php if (!empty($result["meta"])): ?>
                                            <small><?= e($result["meta"]) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($result["excerpt"])): ?>
                                            <span><?= e($result["excerpt"]) ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <b aria-hidden="true">→</b>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($group["hasMore"])): ?>
                            <p class="searchGroupNotice">
                                Hay más coincidencias. Afina el término para reducir los resultados.
                            </p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
