<?php
$fullSpellName = trim((string) ($spellData["name"] ?? "Conjuro"));
$parenthesisPosition = strpos($fullSpellName, "(");
$spellName = $parenthesisPosition === false
    ? $fullSpellName
    : trim(substr($fullSpellName, 0, $parenthesisPosition));

$school = trim((string) ($spellData["escuela"] ?? "Arcano"));
$normalizedSchool = strtolower(strtr($school, [
    "á" => "a", "é" => "e", "í" => "i", "ó" => "o", "ú" => "u", "ü" => "u",
    "Á" => "a", "É" => "e", "Í" => "i", "Ó" => "o", "Ú" => "u", "Ü" => "u",
]));
$schoolSlug = preg_replace("/[^a-z]/", "", $normalizedSchool);
$knownSchools = [
    "abjuracion", "adivinacion", "conjuracion", "encantamiento",
    "evocacion", "ilusion", "nigromancia", "transmutacion",
];
if (!in_array($schoolSlug, $knownSchools, true)) {
    $schoolSlug = "conjuracion";
}
$spellImageDisk = __DIR__ . "/../../resources/imgs/spells/" . $spellName . ".png";
$spellImageUrl = is_file($spellImageDisk)
    ? $assetPrefix . "resources/imgs/spells/" . rawurlencode($spellName) . ".png"
    : $assetPrefix . "resources/imgs/spells/generico.png";

$schoolFile = strtolower($school) . ".png";
$schoolImageDisk = __DIR__ . "/../../resources/imgs/spelltypes/" . $schoolFile;
$schoolImageUrl = is_file($schoolImageDisk)
    ? $assetPrefix . "resources/imgs/spelltypes/" . rawurlencode($schoolFile)
    : $assetPrefix . "resources/imgs/spelltypes/conjuracion.png";

$allowedDescriptionTags = "<p><br><b><strong><i><em><ul><ol><li>";
$description = strip_tags((string) ($spellData["descr"] ?? ""), $allowedDescriptionTags);
?>
<main class="spellPage <?= $embedded ? "isEmbedded" : "" ?>">
    <?php if (!$embedded): ?>
        <nav class="spellBreadcrumb" aria-label="Migas de pan">
            <a href="<?= htmlspecialchars($previousPath, ENT_QUOTES, "UTF-8") ?>">← Grimorio</a>
            <span>/</span>
            <strong><?= htmlspecialchars($spellName, ENT_QUOTES, "UTF-8") ?></strong>
        </nav>
    <?php endif; ?>

    <article class="spellCard">
        <header class="spellHero school-<?= htmlspecialchars($schoolSlug, ENT_QUOTES, "UTF-8") ?>">
            <div class="spellIdentity">
                <span class="spellArtwork">
                    <img src="<?= htmlspecialchars($spellImageUrl, ENT_QUOTES, "UTF-8") ?>" alt="">
                </span>
                <div>
                    <span class="spellKicker">Entrada del grimorio</span>
                    <h1><?= htmlspecialchars($spellName, ENT_QUOTES, "UTF-8") ?></h1>
                    <p><?= htmlspecialchars((string) ($spellData["level"] ?? "Nivel desconocido"), ENT_QUOTES, "UTF-8") ?></p>
                </div>
            </div>
            <div class="schoolBadge">
                <img src="<?= htmlspecialchars($schoolImageUrl, ENT_QUOTES, "UTF-8") ?>" alt="">
                <span>
                    <small>ESCUELA</small>
                    <strong><?= htmlspecialchars($school, ENT_QUOTES, "UTF-8") ?></strong>
                </span>
            </div>
        </header>

        <section class="spellFacts" aria-label="Datos del conjuro">
            <div>
                <span aria-hidden="true">◷</span>
                <small>Duración</small>
                <strong><?= htmlspecialchars((string) ($spellData["duracion"] ?? "—"), ENT_QUOTES, "UTF-8") ?></strong>
            </div>
            <div>
                <span aria-hidden="true">⌖</span>
                <small>Alcance</small>
                <strong><?= htmlspecialchars((string) ($spellData["rango"] ?? "—"), ENT_QUOTES, "UTF-8") ?></strong>
            </div>
            <div>
                <span aria-hidden="true">✦</span>
                <small>Tiempo de lanzamiento</small>
                <strong><?= htmlspecialchars((string) ($spellData["casteo"] ?? "—"), ENT_QUOTES, "UTF-8") ?></strong>
            </div>
            <div>
                <span aria-hidden="true">◎</span>
                <small>Concentración</small>
                <strong><?= htmlspecialchars(ucfirst((string) ($spellData["concentracion"] ?? "—")), ENT_QUOTES, "UTF-8") ?></strong>
            </div>
        </section>

        <section class="spellDescription">
            <span class="spellKicker">Descripción</span>
            <div><?= $description ?></div>
        </section>

        <footer class="spellFooter">
            <div>
                <small>DISPONIBLE PARA</small>
                <strong><?= htmlspecialchars((string) ($spellData["clases"] ?? "Clase desconocida"), ENT_QUOTES, "UTF-8") ?></strong>
            </div>
            <?php if (!$embedded): ?>
                <a href="<?= htmlspecialchars($previousPath, ENT_QUOTES, "UTF-8") ?>">Volver al grimorio <span>→</span></a>
            <?php endif; ?>
        </footer>
    </article>
</main>
