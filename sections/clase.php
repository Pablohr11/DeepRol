<?php

require_once __DIR__ . "/../classes/CharacterOptionCatalog.php";
require_once __DIR__ . "/../classes/ClassDetailCatalog.php";
require_once __DIR__ . "/../classes/SpellSlotProgression.php";

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function classDetailUrl(string $className, string $subclassName = ""): string
{
    $url = "clase.php?class=" . rawurlencode($className);
    if ($subclassName !== "") {
        $url .= "&subclass=" . rawurlencode($subclassName);
    }
    return $url;
}

function sourceParts(string $source): array
{
    return array_values(array_filter(array_map(
        "trim",
        preg_split("/\\s*\\/\\s*/u", $source) ?: []
    )));
}

$className = trim((string) ($_GET["class"] ?? ""));
$class = CharacterOptionCatalog::findClass($className);
if (!$class) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Clase no disponible · DeepRol</title>
        <script src="../scripts/theme.js"></script>
        <link rel="stylesheet" href="../styles/class-detail.css">
        <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
    </head>
    <body>
        <main class="classDetailError">
            <span>✦</span>
            <h1>Clase no disponible</h1>
            <p>No hemos encontrado esa clase en el catálogo.</p>
            <a href="clases.php">Volver a clases</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$profile = ClassDetailCatalog::profile($className);
$subclasses = is_array($class["subclasses"] ?? null)
    ? $class["subclasses"]
    : [];
$selectedSubclassName = trim((string) ($_GET["subclass"] ?? ""));
$selectedSubclass = null;
foreach ($subclasses as $subclass) {
    if ((string) ($subclass["name"] ?? "") === $selectedSubclassName) {
        $selectedSubclass = $subclass;
        break;
    }
}
if ($selectedSubclassName !== "" && !$selectedSubclass) {
    http_response_code(404);
    $selectedSubclassName = "";
}

$subclassDetail = $selectedSubclass
    ? ClassDetailCatalog::subclassDetail($className, $selectedSubclassName)
    : [];
$subclassFeaturesByLevel = [];
foreach (($subclassDetail["features"] ?? []) as $feature) {
    $featureLevel = (int) ($feature["level"] ?? 0);
    if ($featureLevel > 0) {
        $subclassFeaturesByLevel[$featureLevel][] = $feature;
    }
}
ksort($subclassFeaturesByLevel);

$subclassForMagic = $selectedSubclassName;
$progressionRows = [];
$maximumSpellLevel = 0;
$hasPactMagic = false;
for ($level = 1; $level <= 20; $level++) {
    $magic = SpellSlotProgression::forCharacter(
        $className,
        $level,
        $subclassForMagic
    );
    foreach (array_keys($magic["slots"] ?? []) as $spellLevel) {
        $maximumSpellLevel = max($maximumSpellLevel, (int) $spellLevel);
    }
    if ((string) ($magic["type"] ?? "") === "pact") {
        $hasPactMagic = true;
    }
    $progressionRows[] = [
        "level" => $level,
        "proficiency" => 2 + (int) floor(($level - 1) / 4),
        "features" => (string) ($profile["features"][$level] ?? "—"),
        "subclass_features" => array_values(array_filter(array_map(
            static function (array $feature): string {
                return (string) (
                    $feature["progressionLabel"]
                    ?? $feature["name"]
                    ?? ""
                );
            },
            $subclassFeaturesByLevel[$level] ?? []
        ))),
        "magic" => $magic,
    ];
}
$subclassLevels = ClassDetailCatalog::subclassLevels($className);
$classLabel = (string) ($class["label"] ?? $className);
$selectedSource = (string) ($selectedSubclass["source"] ?? "");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title><?= e($classLabel) ?><?= $selectedSubclass ? " · " . e($selectedSubclassName) : "" ?> · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/class-detail.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
</head>
<body>
    <main class="classDetailPage">
        <nav class="classBreadcrumb" aria-label="Ruta de navegación">
            <a href="clases.php">Clases y subclases</a>
            <span>›</span>
            <a href="<?= e(classDetailUrl($className)) ?>"><?= e($classLabel) ?></a>
            <?php if ($selectedSubclass): ?>
                <span>›</span>
                <strong><?= e($selectedSubclassName) ?></strong>
            <?php endif; ?>
        </nav>

        <header class="classDetailHero">
            <div class="classHeroCopy">
                <span class="classKicker"><?= $selectedSubclass ? "Subclase oficial" : "Ficha de clase" ?></span>
                <h1><?= e($selectedSubclassName ?: $classLabel) ?></h1>
                <p>
                    <?php if ($selectedSubclass): ?>
                        <?= e(ClassDetailCatalog::subclassSummary(
                            $className,
                            $selectedSubclassName,
                            $selectedSource
                        )) ?>
                    <?php else: ?>
                        <?= e($class["description"] ?? $profile["role"]) ?>
                    <?php endif; ?>
                </p>
                <div class="classHeroActions">
                    <a href="addPersonajes.php">Crear personaje con esta clase</a>
                    <?php if ($selectedSubclass): ?>
                        <a class="secondary" href="<?= e(classDetailUrl($className)) ?>">Ver clase base</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="classHeroFacts">
                <article>
                    <span>Dado de golpe</span>
                    <strong><?= e($profile["hit_die"]) ?></strong>
                </article>
                <article>
                    <span>Característica</span>
                    <strong><?= e($profile["primary"]) ?></strong>
                </article>
                <article>
                    <span>Progresión</span>
                    <strong><?= e($profile["magic"]) ?></strong>
                </article>
                <?php if ($selectedSubclass): ?>
                    <article>
                        <span>Primer rasgo</span>
                        <strong>Nivel <?= (int) $subclassLevels[0] ?></strong>
                    </article>
                <?php else: ?>
                    <article>
                        <span>Elección de subclase</span>
                        <strong>Nivel <?= max(1, (int) ($class["subclassLevel"] ?? $subclassLevels[0])) ?></strong>
                    </article>
                <?php endif; ?>
            </div>
        </header>

        <div class="classDetailLayout">
            <aside class="classIndex">
                <div class="classIndexTitle">
                    <span>Arquetipos</span>
                    <strong><?= count($subclasses) ?> subclases</strong>
                </div>
                <a
                    class="<?= $selectedSubclass ? "" : "isActive" ?>"
                    href="<?= e(classDetailUrl($className)) ?>"
                >
                    <span>Clase base</span>
                    <small>Progresión completa</small>
                </a>
                <?php foreach ($subclasses as $subclass): ?>
                    <?php
                    $subclassName = (string) ($subclass["name"] ?? "Subclase");
                    $isSelected = $selectedSubclassName === $subclassName;
                    ?>
                    <a
                        class="<?= $isSelected ? "isActive" : "" ?>"
                        href="<?= e(classDetailUrl($className, $subclassName)) ?>"
                    >
                        <span><?= e($subclassName) ?></span>
                        <small><?= e($subclass["source"] ?? "Fuente oficial") ?></small>
                    </a>
                <?php endforeach; ?>
            </aside>

            <div class="classDetailContent">
                <?php if ($selectedSubclass): ?>
                    <section class="subclassFocus">
                        <div class="detailSectionHeading">
                            <div>
                                <span class="classKicker">Identidad del arquetipo</span>
                                <h2><?= e($selectedSubclassName) ?></h2>
                            </div>
                            <div class="sourceChips">
                                <?php foreach (sourceParts($selectedSource) as $source): ?>
                                    <span><?= e($source) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p><?= e(ClassDetailCatalog::subclassSummary(
                            $className,
                            $selectedSubclassName,
                            $selectedSource
                        )) ?></p>
                        <?php if ($subclassFeaturesByLevel): ?>
                            <div class="subclassLevelDetails">
                                <?php foreach ($subclassFeaturesByLevel as $level => $features): ?>
                                    <article class="subclassLevelGroup">
                                        <header>
                                            <span>Nivel <?= (int) $level ?></span>
                                            <strong>
                                                <?= count($features) ?>
                                                <?= count($features) === 1 ? "rasgo" : "rasgos" ?>
                                            </strong>
                                        </header>
                                        <div class="subclassFeatureStack">
                                            <?php foreach ($features as $feature): ?>
                                                <section class="subclassFeature">
                                                    <div class="subclassFeatureHeading">
                                                        <span aria-hidden="true">✦</span>
                                                        <div>
                                                            <h3><?= e($feature["name"] ?? "Rasgo de subclase") ?></h3>
                                                            <p><?= e($feature["summary"] ?? "") ?></p>
                                                        </div>
                                                    </div>

                                                    <?php if (!empty($feature["facts"])): ?>
                                                        <dl class="subclassRuleFacts">
                                                            <?php foreach ($feature["facts"] as $fact): ?>
                                                                <div>
                                                                    <dt><?= e($fact["label"] ?? "") ?></dt>
                                                                    <dd><?= e($fact["value"] ?? "") ?></dd>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </dl>
                                                    <?php endif; ?>

                                                    <?php if (!empty($feature["details"])): ?>
                                                        <ul class="subclassRuleList">
                                                            <?php foreach ($feature["details"] as $detail): ?>
                                                                <li><?= e($detail) ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php endif; ?>

                                                    <?php $featureTable = $feature["table"] ?? []; ?>
                                                    <?php if (!empty($featureTable["rows"])): ?>
                                                        <div class="subclassRuleTableWrap">
                                                            <table
                                                                class="subclassRuleTable"
                                                                aria-label="<?= e($featureTable["label"] ?? "Tabla del rasgo") ?>"
                                                            >
                                                                <thead>
                                                                    <tr>
                                                                        <?php foreach (($featureTable["headers"] ?? []) as $header): ?>
                                                                            <th><?= e($header) ?></th>
                                                                        <?php endforeach; ?>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($featureTable["rows"] as $tableRow): ?>
                                                                        <tr>
                                                                            <?php foreach ($tableRow as $cell): ?>
                                                                                <td><?= e($cell) ?></td>
                                                                            <?php endforeach; ?>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php endif; ?>
                                                </section>
                                            <?php endforeach; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="subclassMilestones">
                                <?php foreach ($subclassLevels as $index => $level): ?>
                                    <article>
                                        <span>Nivel <?= (int) $level ?></span>
                                        <strong>
                                            <?= $index === 0
                                                ? "Elección y rasgo inicial"
                                                : ($index === count($subclassLevels) - 1
                                                    ? "Rasgo culminante"
                                                    : "Nuevo rasgo de subclase") ?>
                                        </strong>
                                        <p>
                                            Consulta el manual indicado para la redacción completa
                                            de este rasgo.
                                        </p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <?php foreach (($subclassDetail["companions"] ?? []) as $companion): ?>
                        <section class="subclassCompanion">
                            <div class="detailSectionHeading">
                                <div>
                                    <span class="classKicker">Compañero invocado</span>
                                    <h2><?= e($companion["name"] ?? "Compañero") ?></h2>
                                </div>
                                <span class="tableLegend"><?= e($companion["type"] ?? "") ?></span>
                            </div>

                            <dl class="companionFacts">
                                <?php foreach (($companion["facts"] ?? []) as $fact): ?>
                                    <div>
                                        <dt><?= e($fact["label"] ?? "") ?></dt>
                                        <dd><?= e($fact["value"] ?? "") ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>

                            <div class="companionAbilities" aria-label="Características del compañero">
                                <?php foreach (($companion["abilities"] ?? []) as $ability): ?>
                                    <article>
                                        <span><?= e($ability["name"] ?? "") ?></span>
                                        <strong><?= e($ability["score"] ?? "") ?></strong>
                                        <small><?= e($ability["modifier"] ?? "") ?></small>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <dl class="companionTraits">
                                <?php foreach (($companion["traits"] ?? []) as $trait): ?>
                                    <div>
                                        <dt><?= e($trait["label"] ?? "") ?></dt>
                                        <dd><?= e($trait["value"] ?? "") ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>

                            <div class="companionActions">
                                <span class="classKicker">Acciones</span>
                                <?php foreach (($companion["actions"] ?? []) as $action): ?>
                                    <article>
                                        <h3><?= e($action["name"] ?? "") ?></h3>
                                        <p><?= e($action["value"] ?? "") ?></p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                <?php else: ?>
                    <section class="classOverview">
                        <div class="detailSectionHeading">
                            <div>
                                <span class="classKicker">Fundamentos</span>
                                <h2>Perfil de la clase</h2>
                            </div>
                        </div>
                        <p class="roleSummary"><?= e($profile["role"]) ?></p>
                        <div class="proficiencyGrid">
                            <article>
                                <span>Salvaciones</span>
                                <strong><?= e($profile["saves"]) ?></strong>
                            </article>
                            <article>
                                <span>Armaduras</span>
                                <strong><?= e($profile["armor"]) ?></strong>
                            </article>
                            <article>
                                <span>Armas</span>
                                <strong><?= e($profile["weapons"]) ?></strong>
                            </article>
                            <article>
                                <span>Habilidades</span>
                                <strong><?= e($profile["skills"]) ?></strong>
                            </article>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="progressionSection">
                    <div class="detailSectionHeading">
                        <div>
                            <span class="classKicker">Niveles 1–20</span>
                            <h2>Tabla de progresión</h2>
                        </div>
                        <?php if ($maximumSpellLevel > 0 || $hasPactMagic): ?>
                            <span class="tableLegend">Los números indican espacios disponibles</span>
                        <?php endif; ?>
                    </div>
                    <div class="progressionTableWrap">
                        <table class="progressionTable">
                            <thead>
                                <tr>
                                    <th>Nivel</th>
                                    <th>Competencia</th>
                                    <th>Rasgos del nivel</th>
                                    <?php if ($hasPactMagic): ?>
                                        <th>Espacios</th>
                                        <th>Nivel del espacio</th>
                                    <?php elseif ($maximumSpellLevel > 0): ?>
                                        <?php for ($spellLevel = 1; $spellLevel <= $maximumSpellLevel; $spellLevel++): ?>
                                            <th title="Espacios de nivel <?= $spellLevel ?>"><?= $spellLevel ?>º</th>
                                        <?php endfor; ?>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($progressionRows as $row): ?>
                                    <?php $slots = $row["magic"]["slots"] ?? []; ?>
                                    <tr>
                                        <td><strong><?= (int) $row["level"] ?></strong></td>
                                        <td>+<?= (int) $row["proficiency"] ?></td>
                                        <td>
                                            <?php if ($row["features"] !== "—" || empty($row["subclass_features"])): ?>
                                                <span><?= e($row["features"]) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($row["subclass_features"])): ?>
                                                <strong class="subclassProgressionFeature">
                                                    <?= e(implode(" · ", $row["subclass_features"])) ?>
                                                </strong>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($hasPactMagic): ?>
                                            <?php
                                            $pactLevel = $slots ? (int) array_key_first($slots) : 0;
                                            $pactCount = $slots ? (int) reset($slots) : 0;
                                            ?>
                                            <td><?= $pactCount ?: "—" ?></td>
                                            <td><?= $pactLevel ? $pactLevel . "º" : "—" ?></td>
                                        <?php elseif ($maximumSpellLevel > 0): ?>
                                            <?php for ($spellLevel = 1; $spellLevel <= $maximumSpellLevel; $spellLevel++): ?>
                                                <td><?= isset($slots[$spellLevel]) ? (int) $slots[$spellLevel] : "—" ?></td>
                                            <?php endfor; ?>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <?php if (!$selectedSubclass && !empty($profile["core"])): ?>
                    <section class="coreFeatures">
                        <div class="detailSectionHeading">
                            <div>
                                <span class="classKicker">Reglas esenciales</span>
                                <h2>Rasgos que definen a <?= e($classLabel) ?></h2>
                            </div>
                        </div>
                        <div class="coreFeatureGrid">
                            <?php foreach ($profile["core"] as $feature => $description): ?>
                                <article>
                                    <span aria-hidden="true">✦</span>
                                    <h3><?= e($feature) ?></h3>
                                    <p><?= e($description) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if (!$selectedSubclass): ?>
                    <section class="subclassGallery">
                        <div class="detailSectionHeading">
                            <div>
                                <span class="classKicker">Especializaciones oficiales</span>
                                <h2>Subclases de <?= e($classLabel) ?></h2>
                            </div>
                        </div>
                        <div class="subclassGalleryGrid">
                            <?php foreach ($subclasses as $subclass): ?>
                                <?php
                                $subclassName = (string) ($subclass["name"] ?? "Subclase");
                                $source = (string) ($subclass["source"] ?? "");
                                ?>
                                <a href="<?= e(classDetailUrl($className, $subclassName)) ?>">
                                    <span><?= e($source ?: "Fuente oficial") ?></span>
                                    <h3><?= e($subclassName) ?></h3>
                                    <p><?= e(ClassDetailCatalog::subclassSummary(
                                        $className,
                                        $subclassName,
                                        $source
                                    )) ?></p>
                                    <strong>Ver detalle →</strong>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <footer class="classRulesNotice">
                    <strong>Referencia de mesa</strong>
                    <p>
                        Esta ficha resume la progresión de D&D 5e 2014. Para resolver
                        redacciones excepcionales o interacciones concretas, consulta
                        el manual indicado en cada subclase.
                    </p>
                </footer>
            </div>
        </div>
    </main>
</body>
</html>
