<?php

require_once("../classes/CompendiumRepository.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function classSourceParts(string $source): array
{
    $parts = preg_split('/\s*\/\s*/u', $source) ?: [];
    return array_values(array_filter(array_map("trim", $parts)));
}

$classes = CompendiumRepository::classes();
$sourceMap = CompendiumRepository::sourceBookMap();
$hitDice = [
    "Artifice" => "d8",
    "Barbaro" => "d12",
    "Bardo" => "d8",
    "Brujo" => "d8",
    "Clerigo" => "d8",
    "Druida" => "d8",
    "Explorador" => "d10",
    "Guerrero" => "d10",
    "Hechicero" => "d6",
    "Mago" => "d6",
    "Monje" => "d8",
    "Paladin" => "d10",
    "Picaro" => "d8",
];
$magicTypes = [
    "Artifice" => "Medio lanzador",
    "Barbaro" => "Marcial",
    "Bardo" => "Lanzador completo",
    "Brujo" => "Magia de pacto",
    "Clerigo" => "Lanzador completo",
    "Druida" => "Lanzador completo",
    "Explorador" => "Medio lanzador",
    "Guerrero" => "Marcial / subclase",
    "Hechicero" => "Lanzador completo",
    "Mago" => "Lanzador completo",
    "Monje" => "Marcial",
    "Paladin" => "Medio lanzador",
    "Picaro" => "Marcial / subclase",
];
$sourceNames = [];
$subclassCount = 0;
foreach ($classes as $class) {
    foreach (($class["subclasses"] ?? []) as $subclass) {
        $subclassCount++;
        foreach (classSourceParts((string) ($subclass["source"] ?? "")) as $source) {
            $sourceNames[$source] = $source;
        }
    }
}
ksort($sourceNames, SORT_NATURAL | SORT_FLAG_CASE);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Clases y subclases · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/compendium.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script defer src="../scripts/compendium.js"></script>
</head>
<body>
    <main class="compendiumPage" data-compendium data-kind="classes">
        <header class="compendiumHero classesHero">
            <div class="heroCopy">
                <span class="sectionKicker">Caminos de aventura</span>
                <h1>Clases y subclases</h1>
                <p>Compara la identidad de cada clase y localiza sus arquetipos oficiales por manual.</p>
                <div class="heroBadges">
                    <span>Catálogo 2014</span>
                    <span>Sin Unearthed Arcana</span>
                    <span>Integrado con el creador</span>
                </div>
            </div>
            <div class="compendiumStats" aria-label="Resumen de clases">
                <div>
                    <strong><?= count($classes) ?></strong>
                    <span>clases</span>
                </div>
                <div>
                    <strong><?= $subclassCount ?></strong>
                    <span>subclases</span>
                </div>
                <div>
                    <strong><?= count($sourceNames) ?></strong>
                    <span>fuentes</span>
                </div>
            </div>
        </header>

        <section class="catalogNotice">
            <span class="noticeIcon" aria-hidden="true">✦</span>
            <div>
                <strong>El mismo catálogo que utiliza la ficha</strong>
                <p>Las subclases de esta página son exactamente las que ofrece el formulario de creación, incluido el nivel al que se eligen.</p>
            </div>
            <a class="noticeAction" href="addPersonajes.php">Crear personaje →</a>
        </section>

        <section class="compendiumToolbar classesToolbar" aria-label="Filtros de clases">
            <label class="searchField">
                <span aria-hidden="true">⌕</span>
                <input type="search" data-search placeholder="Buscar clase, subclase o manual..." autocomplete="off">
                <button type="button" data-clear-search aria-label="Borrar búsqueda" hidden>×</button>
            </label>
            <label>
                <span>Fuente</span>
                <select data-filter="source">
                    <option value="">Todos los libros</option>
                    <?php foreach ($sourceNames as $source): ?>
                        <option value="<?= e($source) ?>"><?= e($source) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Progresión</span>
                <select data-filter="magic">
                    <option value="">Cualquier estilo</option>
                    <?php foreach (array_unique(array_values($magicTypes)) as $magicType): ?>
                        <option value="<?= e($magicType) ?>"><?= e($magicType) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Orden</span>
                <select data-sort>
                    <option value="name">Nombre</option>
                    <option value="variants-desc">Más subclases</option>
                </select>
            </label>
        </section>

        <div class="catalogResultBar">
            <p><strong data-result-count><?= count($classes) ?></strong> clases encontradas</p>
            <button type="button" data-reset-filters>Restablecer filtros</button>
        </div>

        <section class="compendiumGrid classesGrid" data-entry-list aria-live="polite">
            <?php foreach ($classes as $index => $class): ?>
                <?php
                $name = (string) ($class["name"] ?? "Clase");
                $label = (string) ($class["label"] ?? $name);
                $subclasses = $class["subclasses"] ?? [];
                $classSources = [];
                foreach ($subclasses as $subclass) {
                    foreach (classSourceParts((string) ($subclass["source"] ?? "")) as $source) {
                        $classSources[$source] = $source;
                    }
                }
                $searchText = implode(" ", array_merge(
                    [$name, $label, $class["description"] ?? "", $magicTypes[$name] ?? ""],
                    array_column($subclasses, "name"),
                    array_column($subclasses, "source")
                ));
                ?>
                <article
                    class="compendiumCard classCard accent<?= ($index % 4) + 1 ?>"
                    data-entry
                    data-name="<?= e($label) ?>"
                    data-source="<?= e(implode("|", array_values($classSources))) ?>"
                    data-magic="<?= e($magicTypes[$name] ?? "Marcial") ?>"
                    data-variants="<?= count($subclasses) ?>"
                    data-search-text="<?= e($searchText) ?>"
                >
                    <div class="cardTopline">
                        <span class="catalogGlyph" aria-hidden="true">✦</span>
                        <span><?= e($magicTypes[$name] ?? "Marcial") ?></span>
                        <b><?= count($subclasses) ?> subclases</b>
                    </div>
                    <h2><?= e($label) ?></h2>
                    <p class="cardSummary"><?= e($class["description"] ?? "") ?></p>
                    <div class="classFacts">
                        <span><small>Dado de golpe</small><strong><?= e($hitDice[$name] ?? "d8") ?></strong></span>
                        <span><small>Subclase</small><strong>Nivel <?= max(1, (int) ($class["subclassLevel"] ?? 1)) ?></strong></span>
                    </div>

                    <a class="classDetailLink" href="clase.php?class=<?= rawurlencode($name) ?>">
                        Ver ficha completa <span aria-hidden="true">→</span>
                    </a>

                    <details class="catalogDetails">
                        <summary>Explorar subclases <span aria-hidden="true">+</span></summary>
                        <div class="detailBody">
                            <ul class="variantList subclassList">
                                <?php foreach ($subclasses as $subclass): ?>
                                    <li>
                                        <?php $subclassName = (string) ($subclass["name"] ?? "Subclase"); ?>
                                        <a
                                            class="subclassDetailLink"
                                            href="clase.php?class=<?= rawurlencode($name) ?>&amp;subclass=<?= rawurlencode($subclassName) ?>"
                                        >
                                            <strong><?= e($subclassName) ?></strong>
                                            <small>Ver detalle →</small>
                                        </a>
                                        <?php $source = (string) ($subclass["source"] ?? "Fuente oficial"); ?>
                                        <?php if (isset($sourceMap[$source])): ?>
                                            <a href="<?= e($sourceMap[$source]["url"]) ?>" target="_blank" rel="noopener noreferrer">
                                                <?= e($source) ?> ↗
                                            </a>
                                        <?php else: ?>
                                            <span><?= e($source) ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </details>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="emptyCatalogState" data-empty-state hidden>
            <span aria-hidden="true">◇</span>
            <h2>No hay clases con esos filtros</h2>
            <p>Prueba otro manual, estilo de progresión o término.</p>
            <button type="button" data-reset-filters>Mostrar todas las clases</button>
        </section>

        <footer class="catalogAttribution">
            Catálogo oficial compatible con D&D 5e 2014. No incluye material de prueba ni contenido Unearthed Arcana.
        </footer>
    </main>
</body>
</html>
