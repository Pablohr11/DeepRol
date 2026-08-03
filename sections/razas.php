<?php

require_once("../classes/CompendiumRepository.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function sourceParts(string $source): array
{
    $parts = preg_split('/\s*\/\s*/u', $source) ?: [];
    return array_values(array_filter(array_map("trim", $parts)));
}

$playableRaces = CompendiumRepository::playableRaces();
$referenceAncestries = CompendiumRepository::nonPlayableAncestries();
$sourceMap = CompendiumRepository::sourceBookMap();
$categories = ["Jugable" => "Opción jugable"];
$sources = [];
$playableVariantCount = 0;
$referenceVariantCount = 0;

foreach ($playableRaces as $race) {
    $playableVariantCount += count($race["subraces"] ?? []);
    foreach (sourceParts((string) ($race["source"] ?? "")) as $source) {
        $sources[$source] = $source;
    }
}

foreach ($referenceAncestries as $ancestry) {
    $category = (string) ($ancestry["category"] ?? "Otro");
    $categories[$category] = $category;
    $referenceVariantCount += count($ancestry["variants"] ?? []);
    foreach (($ancestry["sources"] ?? []) as $source) {
        $sources[(string) $source] = (string) $source;
    }
}

ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);
ksort($sources, SORT_NATURAL | SORT_FLAG_CASE);
$totalEntries = count($playableRaces) + count($referenceAncestries);
$totalVariants = $playableVariantCount + $referenceVariantCount;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Razas y linajes · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/compendium.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script defer src="../scripts/compendium.js"></script>
</head>
<body>
    <main class="compendiumPage" data-compendium data-kind="ancestries">
        <header class="compendiumHero ancestryHero">
            <div class="heroCopy">
                <span class="sectionKicker">Atlas de los pueblos</span>
                <h1>Razas, subrazas y linajes</h1>
                <p>Un diccionario que reúne opciones de personaje y pueblos del multiverso que normalmente solo aparecen como criaturas o culturas del mundo.</p>
                <div class="heroBadges">
                    <span>Jugables y no jugables</span>
                    <span>Reglas 2014</span>
                    <span>Fuentes oficiales</span>
                </div>
            </div>
            <div class="compendiumStats" aria-label="Resumen del diccionario">
                <div>
                    <strong><?= $totalEntries ?></strong>
                    <span>familias</span>
                </div>
                <div>
                    <strong><?= $totalVariants ?></strong>
                    <span>variantes</span>
                </div>
                <div>
                    <strong><?= count($sources) ?></strong>
                    <span>fuentes</span>
                </div>
            </div>
        </header>

        <section class="catalogNotice">
            <span class="noticeIcon" aria-hidden="true">◇</span>
            <div>
                <strong>Más amplio que el creador de personajes</strong>
                <p>Las entradas marcadas como «referencia de mundo» describen pueblos, castas o estirpes oficiales, pero no conceden por sí mismas una opción jugable.</p>
            </div>
        </section>

        <section class="compendiumToolbar ancestryToolbar" aria-label="Filtros del diccionario">
            <label class="searchField">
                <span aria-hidden="true">⌕</span>
                <input type="search" data-search placeholder="Buscar raza, subraza, variante o manual..." autocomplete="off">
                <button type="button" data-clear-search aria-label="Borrar búsqueda" hidden>×</button>
            </label>
            <label>
                <span>Disponibilidad</span>
                <select data-filter="status">
                    <option value="">Todas</option>
                    <option value="playable">Jugables</option>
                    <option value="reference">No jugables</option>
                </select>
            </label>
            <label>
                <span>Familia</span>
                <select data-filter="category">
                    <option value="">Cualquier familia</option>
                    <?php foreach ($categories as $category => $label): ?>
                        <option value="<?= e($category) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Fuente</span>
                <select data-filter="source">
                    <option value="">Todos los libros</option>
                    <?php foreach ($sources as $source): ?>
                        <option value="<?= e($source) ?>"><?= e($source) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Orden</span>
                <select data-sort>
                    <option value="name">Nombre</option>
                    <option value="variants-desc">Más variantes</option>
                </select>
            </label>
        </section>

        <div class="catalogResultBar">
            <p><strong data-result-count><?= $totalEntries ?></strong> entradas encontradas</p>
            <button type="button" data-reset-filters>Restablecer filtros</button>
        </div>

        <section class="compendiumGrid ancestryGrid" data-entry-list aria-live="polite">
            <?php foreach ($playableRaces as $race): ?>
                <?php
                $name = (string) ($race["name"] ?? "");
                $label = (string) ($race["label"] ?? $name);
                $source = (string) ($race["source"] ?? "Fuente oficial");
                $variants = $race["subraces"] ?? [];
                $variantNames = array_column($variants, "name");
                $sourceNames = sourceParts($source);
                $searchText = implode(" ", array_merge(
                    [$name, $label, $source, "jugable"],
                    $variantNames,
                    array_column($variants, "source")
                ));
                ?>
                <article
                    class="compendiumCard ancestryCard isPlayable"
                    data-entry
                    data-name="<?= e($label) ?>"
                    data-status="playable"
                    data-category="Jugable"
                    data-source="<?= e(implode("|", $sourceNames)) ?>"
                    data-variants="<?= count($variants) ?>"
                    data-search-text="<?= e($searchText) ?>"
                >
                    <div class="cardTopline">
                        <span class="catalogGlyph" aria-hidden="true">♙</span>
                        <span>Opción jugable</span>
                        <b><?= count($variants) ?> variantes</b>
                    </div>
                    <h2><?= e($label) ?></h2>
                    <p class="cardDescriptor"><?= e($source) ?></p>
                    <p class="cardSummary">Disponible en la creación de personajes y compatible con la ficha de reglas 2014.</p>

                    <details class="catalogDetails">
                        <summary>Ver subrazas y variantes <span aria-hidden="true">+</span></summary>
                        <div class="detailBody">
                            <?php if ($variants): ?>
                                <ul class="variantList">
                                    <?php foreach ($variants as $variant): ?>
                                        <li>
                                            <strong><?= e($variant["name"] ?? "Variante") ?></strong>
                                            <span><?= e($variant["source"] ?? $source) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="noVariants">No tiene subrazas diferenciadas en esta edición.</p>
                            <?php endif; ?>
                        </div>
                    </details>
                </article>
            <?php endforeach; ?>

            <?php foreach ($referenceAncestries as $ancestry): ?>
                <?php
                $name = (string) ($ancestry["name"] ?? "Linaje");
                $category = (string) ($ancestry["category"] ?? "Otro");
                $sourceNames = array_map("strval", $ancestry["sources"] ?? []);
                $variants = array_map("strval", $ancestry["variants"] ?? []);
                $searchText = implode(" ", array_merge(
                    [$name, $category, $ancestry["summary"] ?? "", "no jugable"],
                    $sourceNames,
                    $variants
                ));
                ?>
                <article
                    class="compendiumCard ancestryCard isReference"
                    data-entry
                    data-name="<?= e($name) ?>"
                    data-status="reference"
                    data-category="<?= e($category) ?>"
                    data-source="<?= e(implode("|", $sourceNames)) ?>"
                    data-variants="<?= count($variants) ?>"
                    data-search-text="<?= e($searchText) ?>"
                >
                    <div class="cardTopline">
                        <span class="catalogGlyph" aria-hidden="true">⌁</span>
                        <span><?= e($category) ?></span>
                        <b>Referencia de mundo</b>
                    </div>
                    <h2><?= e($name) ?></h2>
                    <p class="cardDescriptor"><?= e(implode(" · ", $sourceNames)) ?></p>
                    <p class="cardSummary"><?= e($ancestry["summary"] ?? "") ?></p>

                    <details class="catalogDetails">
                        <summary>Ver estirpes relacionadas <span aria-hidden="true">+</span></summary>
                        <div class="detailBody">
                            <div class="variantChips">
                                <?php foreach ($variants as $variant): ?>
                                    <span><?= e($variant) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="sourceLinks">
                                <?php foreach ($sourceNames as $source): ?>
                                    <?php if (isset($sourceMap[$source])): ?>
                                        <a href="<?= e($sourceMap[$source]["url"]) ?>" target="_blank" rel="noopener noreferrer">
                                            <?= e($sourceMap[$source]["label"]) ?> ↗
                                        </a>
                                    <?php else: ?>
                                        <span><?= e($source) ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </details>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="emptyCatalogState" data-empty-state hidden>
            <span aria-hidden="true">◇</span>
            <h2>No hay linajes con esos filtros</h2>
            <p>Prueba otra fuente, familia o término de búsqueda.</p>
            <button type="button" data-reset-filters>Mostrar todo el diccionario</button>
        </section>

        <footer class="catalogAttribution">
            Catálogo de referencia de D&D 5e 2014. Las entradas no jugables resumen su lugar en el mundo y remiten al manual correspondiente.
        </footer>
    </main>
</body>
</html>
