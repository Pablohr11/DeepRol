<?php

require_once("../classes/CompendiumRepository.php");
require_once("../classes/BestiaryLocalizer.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function challengeBucket($challengeRating): string
{
    $challenge = (float) $challengeRating;
    if ($challenge <= 1) {
        return "0-1";
    }
    if ($challenge <= 4) {
        return "2-4";
    }
    if ($challenge <= 10) {
        return "5-10";
    }

    return "11+";
}

function formatSpeed(array $speed, array $movementLabels): string
{
    $parts = [];
    foreach ($speed as $movement => $value) {
        if ($movement === "hover") {
            if ($value) {
                $parts[] = "Puede flotar";
            }
            continue;
        }

        if ($value !== "") {
            $parts[] = ($movementLabels[$movement] ?? "Otro")
                . " " . BestiaryLocalizer::distance($value);
        }
    }

    return implode(" · ", $parts);
}

$monsters = CompendiumRepository::monsters();
$sourceMap = CompendiumRepository::sourceBookMap();
$movementLabels = [
    "walk" => "Caminando",
    "fly" => "Vuelo",
    "swim" => "Nado",
    "burrow" => "Excavar",
    "climb" => "Trepar",
    "hover" => "Flotar",
];
$abilityLabels = [
    "str" => "FUE",
    "dex" => "DES",
    "con" => "CON",
    "int" => "INT",
    "wis" => "SAB",
    "cha" => "CAR",
];
$availableTypes = [];
$highestChallenge = 0;
foreach ($monsters as $monster) {
    $type = (string) ($monster["type"] ?? "other");
    $availableTypes[$type] = BestiaryLocalizer::type($type);
    $highestChallenge = max($highestChallenge, (float) ($monster["challengeRating"] ?? 0));
}
asort($availableTypes, SORT_NATURAL | SORT_FLAG_CASE);
$officialBestiarySources = [
    "Monster Manual",
    "Volo",
    "Mordenkainen",
    "Mordenkainen Multiverso",
    "Fizban",
    "Spelljammer",
    "Bigby",
    "Planescape",
];
$sourceDisplayNames = [
    "Monster Manual" => "Manual de Monstruos (2014)",
    "Volo" => "Guía de monstruos de Volo",
    "Mordenkainen" => "Tomo de enemigos de Mordenkainen",
    "Mordenkainen Multiverso" => "Mordenkainen presenta: Monstruos del Multiverso",
    "Fizban" => "El tesoro de los dragones de Fizban",
    "Spelljammer" => "Spelljammer: Aventuras en el espacio",
    "Bigby" => "Bigby presenta: La gloria de los gigantes",
    "Planescape" => "Planescape: Aventuras en el Multiverso",
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Bestiario · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/compendium.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    <script defer src="../scripts/compendium.js"></script>
</head>
<body>
    <main class="compendiumPage" data-compendium data-kind="bestiary">
        <header class="compendiumHero bestiaryHero">
            <div class="heroCopy">
                <span class="sectionKicker">Archivo del Dungeon Master</span>
                <h1>Bestiario</h1>
                <p>Consulta criaturas, compara su peligro y abre una ficha rápida sin abandonar la mesa.</p>
                <div class="heroBadges">
                    <span>SRD 5.1</span>
                    <span>Reglas 2014</span>
                    <span>Contenido en castellano</span>
                </div>
            </div>
            <div class="compendiumStats" aria-label="Resumen del bestiario">
                <div>
                    <strong><?= count($monsters) ?></strong>
                    <span>criaturas</span>
                </div>
                <div>
                    <strong><?= count($availableTypes) ?></strong>
                    <span>familias</span>
                </div>
                <div>
                    <strong><?= e($highestChallenge) ?></strong>
                    <span>VD máximo</span>
                </div>
            </div>
        </header>

        <section class="catalogNotice">
            <span class="noticeIcon" aria-hidden="true">⌁</span>
            <div>
                <strong>Estadísticas abiertas y fuentes oficiales</strong>
                <p>Las fichas mecánicas proceden del SRD 5.1 y emplean la terminología de su edición oficial en castellano. También puedes buscar una criatura por su nombre original.</p>
            </div>
        </section>

        <section class="compendiumToolbar" aria-label="Filtros del bestiario">
            <label class="searchField">
                <span aria-hidden="true">⌕</span>
                <input type="search" data-search placeholder="Buscar criatura, tipo o alineamiento..." autocomplete="off">
                <button type="button" data-clear-search aria-label="Borrar búsqueda" hidden>×</button>
            </label>
            <label>
                <span>Tipo</span>
                <select data-filter="type">
                    <option value="">Todos</option>
                    <?php foreach ($availableTypes as $type => $label): ?>
                        <option value="<?= e($type) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Desafío</span>
                <select data-filter="range">
                    <option value="">Cualquier VD</option>
                    <option value="0-1">VD 0–1</option>
                    <option value="2-4">VD 2–4</option>
                    <option value="5-10">VD 5–10</option>
                    <option value="11+">VD 11+</option>
                </select>
            </label>
            <label>
                <span>Orden</span>
                <select data-sort>
                    <option value="name">Nombre</option>
                    <option value="challenge-asc">VD ascendente</option>
                    <option value="challenge-desc">VD descendente</option>
                </select>
            </label>
        </section>

        <div class="catalogResultBar">
            <p><strong data-result-count><?= count($monsters) ?></strong> criaturas encontradas</p>
            <button type="button" data-reset-filters>Restablecer filtros</button>
        </div>

        <section class="compendiumGrid bestiaryGrid" data-entry-list aria-live="polite">
            <?php foreach ($monsters as $monster): ?>
                <?php
                $originalName = (string) ($monster["name"] ?? "Criatura");
                $name = BestiaryLocalizer::name($monster);
                $type = (string) ($monster["type"] ?? "other");
                $typeLabel = BestiaryLocalizer::type($type);
                $subtype = BestiaryLocalizer::subtype(trim((string) ($monster["subtype"] ?? "")));
                $alignment = BestiaryLocalizer::alignment((string) ($monster["alignment"] ?? ""));
                $size = BestiaryLocalizer::size((string) ($monster["size"] ?? ""));
                $challenge = $monster["challengeRating"] ?? 0;
                $armorClass = $monster["armorClass"] ?? "—";
                $hitPoints = $monster["hitPoints"] ?? "—";
                $searchText = implode(" ", [
                    $name,
                    $originalName,
                    $type,
                    $typeLabel,
                    $subtype,
                    $alignment,
                    $size,
                ]);
                ?>
                <article
                    class="compendiumCard monsterCard"
                    data-entry
                    data-name="<?= e($name) ?>"
                    data-challenge="<?= e($challenge) ?>"
                    data-type="<?= e($type) ?>"
                    data-range="<?= e(challengeBucket($challenge)) ?>"
                    data-search-text="<?= e($searchText) ?>"
                >
                    <div class="cardTopline">
                        <span class="catalogGlyph" aria-hidden="true">✦</span>
                        <span><?= e($typeLabel) ?></span>
                        <b>VD <?= e($challenge) ?></b>
                    </div>
                    <h2><?= e($name) ?></h2>
                    <p class="cardDescriptor">
                        <?= e($size) ?>
                        <?php if ($subtype !== ""): ?>
                            · <?= e($subtype) ?>
                        <?php endif; ?>
                        · <?= e($alignment) ?>
                    </p>

                    <div class="quickStats">
                        <span><small>CA</small><strong><?= e($armorClass) ?></strong></span>
                        <span><small>PG</small><strong><?= e($hitPoints) ?></strong></span>
                        <span><small>PX</small><strong><?= e(number_format((int) ($monster["xp"] ?? 0), 0, ",", ".")) ?></strong></span>
                    </div>

                    <details class="catalogDetails">
                        <summary>Consultar ficha <span aria-hidden="true">+</span></summary>
                        <div class="detailBody">
                            <p class="movementLine">
                                <strong>Movimiento</strong>
                                <?= e(formatSpeed($monster["speed"] ?? [], $movementLabels) ?: "No indicado") ?>
                            </p>
                            <div class="abilityStrip" aria-label="Características">
                                <?php foreach ($abilityLabels as $ability => $label): ?>
                                    <span>
                                        <small><?= e($label) ?></small>
                                        <strong><?= e($monster["abilities"][$ability] ?? "—") ?></strong>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <dl class="detailList">
                                <?php if (!empty($monster["senses"])): ?>
                                    <div><dt>Sentidos</dt><dd><?= e(BestiaryLocalizer::senses($monster["senses"])) ?></dd></div>
                                <?php endif; ?>
                                <?php if (!empty($monster["damageVulnerabilities"])): ?>
                                    <div><dt>Vulnerabilidades</dt><dd><?= e(BestiaryLocalizer::damageList($monster["damageVulnerabilities"])) ?></dd></div>
                                <?php endif; ?>
                                <?php if (!empty($monster["damageResistances"])): ?>
                                    <div><dt>Resistencias</dt><dd><?= e(BestiaryLocalizer::damageList($monster["damageResistances"])) ?></dd></div>
                                <?php endif; ?>
                                <?php if (!empty($monster["damageImmunities"])): ?>
                                    <div><dt>Inmunidades</dt><dd><?= e(BestiaryLocalizer::damageList($monster["damageImmunities"])) ?></dd></div>
                                <?php endif; ?>
                                <?php if (!empty($monster["conditionImmunities"])): ?>
                                    <div><dt>Estados inmunes</dt><dd><?= e(BestiaryLocalizer::conditionList($monster["conditionImmunities"])) ?></dd></div>
                                <?php endif; ?>
                                <?php if (!empty($monster["languages"])): ?>
                                    <div><dt>Idiomas</dt><dd><?= e(BestiaryLocalizer::languages((string) $monster["languages"])) ?></dd></div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </details>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="emptyCatalogState" data-empty-state hidden>
            <span aria-hidden="true">◇</span>
            <h2>No hay criaturas con esos filtros</h2>
            <p>Prueba otro nombre, familia o intervalo de desafío.</p>
            <button type="button" data-reset-filters>Mostrar todo el bestiario</button>
        </section>

        <section class="sourceShelf">
            <div class="sectionHeading">
                <div>
                    <span class="sectionKicker">Biblioteca oficial</span>
                    <h2>Amplía la consulta</h2>
                </div>
                <p>Índices editoriales para criaturas que no forman parte del SRD abierto.</p>
            </div>
            <div class="sourceGrid">
                <?php foreach ($officialBestiarySources as $sourceKey): ?>
                    <?php if (isset($sourceMap[$sourceKey])): ?>
                        <a href="<?= e($sourceMap[$sourceKey]["url"]) ?>" target="_blank" rel="noopener noreferrer">
                            <span><?= e($sourceMap[$sourceKey]["kind"]) ?></span>
                            <strong><?= e($sourceDisplayNames[$sourceKey] ?? $sourceMap[$sourceKey]["label"]) ?></strong>
                            <b aria-hidden="true">↗</b>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <footer class="catalogAttribution">
            <p>
                Esta obra incluye materiales extraídos del Documento de referencia del sistema 5.1 («SRD 5.1») de Wizards of the Coast LLC,
                disponible en <a href="https://dnd.wizards.com/es/resources/systems-reference-document" target="_blank" rel="noopener noreferrer">la web oficial de D&amp;D</a>.
                El SRD 5.1 tiene licencia Creative Commons Atribución/Reconocimiento 4.0.
            </p>
            <p>
                Datos estructurados mediante
                <a href="https://www.dnd5eapi.co/" target="_blank" rel="noopener noreferrer">D&amp;D 5e SRD API</a>.
            </p>
        </footer>
    </main>
</body>
</html>
