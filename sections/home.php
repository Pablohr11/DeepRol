<?php
require_once("../classes/DbConnector.php");

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function characterImage(array $character): ?string
{
    $name = $character["name"] ?? "";
    $image = $character["image_path"] ?? "";
    $absolutePath = __DIR__ . "/../resources/chars/" . $name . "/" . $image;

    if ($name !== "" && $image !== "" && is_file($absolutePath)) {
        return "../resources/chars/" . rawurlencode($name) . "/" . rawurlencode($image);
    }

    return null;
}

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
$characters = [];
$allSpells = [];
$notes = [];
$databaseAvailable = false;

try {
    $db = DbConector::singleton();
    $databaseAvailable = true;
    $allSpells = $db->getAllSpells() ?: [];

    if ($userId > 0) {
        $characters = $db->getChars($userId) ?: [];
        $groupedNotes = $db->getNotes($userId) ?: [];

        foreach ($groupedNotes as $characterNotes) {
            foreach ($characterNotes as $note) {
                $notes[] = $note;
            }
        }
    }
} catch (Throwable $exception) {
    $databaseAvailable = false;
}

if (!$characters) {
    $characters = [
        [
            "id_char" => 1,
            "name" => "Draelith",
            "raza" => "Elfo de los bosques",
            "image_path" => "imagenPequeña.png",
            "pdf_path" => "ficha.pdf",
        ],
        [
            "id_char" => 5,
            "name" => "Ren",
            "raza" => "Humano",
            "image_path" => "imagenPequeña.png",
            "pdf_path" => "ficha.pdf",
        ],
        [
            "id_char" => 4,
            "name" => "JoseJu",
            "raza" => "Fungus",
            "image_path" => "imagenPequeña.png",
            "pdf_path" => "ficha.pdf",
        ],
    ];
}

$spellJson = __DIR__ . "/../data/spells.json";
$spellCount = count($allSpells);
if ($spellCount === 0 && is_file($spellJson)) {
    $decodedSpells = json_decode((string) file_get_contents($spellJson), true);
    $spellCount = is_array($decodedSpells) ? count($decodedSpells) : 0;
}

$featuredSpellNames = ["Bola de fuego", "Curar heridas", "Detectar magia", "Deseo"];
$featuredSpells = [];
foreach ($featuredSpellNames as $featuredName) {
    foreach ($allSpells as $spell) {
        if (stripos((string) ($spell["name"] ?? ""), $featuredName) === 0) {
            $featuredSpells[] = $spell;
            break;
        }
    }
}

if (!$featuredSpells) {
    $featuredSpells = [
        ["id_spell" => 0, "name" => "Bola de fuego", "level" => "Nivel 3", "escuela" => "Evocacion"],
        ["id_spell" => 0, "name" => "Curar heridas", "level" => "Nivel 1", "escuela" => "Evocacion"],
        ["id_spell" => 0, "name" => "Detectar magia", "level" => "Nivel 1", "escuela" => "Adivinacion"],
        ["id_spell" => 0, "name" => "Deseo", "level" => "Nivel 9", "escuela" => "Conjuracion"],
    ];
}

$sheetFiles = glob(__DIR__ . "/../resources/chars/*/ficha.pdf") ?: [];
$characterCount = count($characters);
$noteCount = count($notes);
$sheetCount = count($sheetFiles);
$welcomeName = $characters[0]["name"] ?? "Aventurero";
$visibleCharacters = array_slice($characters, 0, 4);
$today = new DateTimeImmutable("now", new DateTimeZone("Europe/Madrid"));
$monthNames = [
    1 => "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre",
];
$realmDate = $today->format("d") . " de " . $monthNames[(int) $today->format("n")] . ", " . $today->format("Y");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Inicio · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
</head>
<body>
    <div class="dashboardShell">
        <section class="hero" aria-labelledby="welcomeTitle">
            <div class="heroGlow"></div>
            <div class="heroCopy">
                <p class="eyebrow">Bienvenido de nuevo,</p>
                <div class="titleLine">
                    <h1 id="welcomeTitle"><?= e($welcomeName) ?></h1>
                    <span class="rune" aria-hidden="true">✥</span>
                </div>
                <p>“Que tus dados sean afilados y tu historia legendaria.”</p>
            </div>
            <div class="heroMeta">
                <span><?= e($today->format("d · m · Y")) ?></span>
                <strong><?= $databaseAvailable ? "Crónica sincronizada" : "Modo códice local" ?></strong>
            </div>
        </section>

        <div class="dashboardColumns">
            <main class="mainColumn">
                <section class="statsGrid" aria-label="Resumen de la cuenta">
                    <a class="statCard purple" href="personajes.php">
                        <span class="statIcon" aria-hidden="true">♙</span>
                        <span>
                            <small>Personajes</small>
                            <strong><?= $characterCount ?></strong>
                            <em>Ver todos</em>
                        </span>
                    </a>
                    <a class="statCard green" href="allSpells.php">
                        <span class="statIcon" aria-hidden="true">✦</span>
                        <span>
                            <small>Conjuros</small>
                            <strong><?= $spellCount ?></strong>
                            <em>Abrir grimorio</em>
                        </span>
                    </a>
                    <a class="statCard blue" href="notes.php">
                        <span class="statIcon" aria-hidden="true">▤</span>
                        <span>
                            <small>Apuntes</small>
                            <strong><?= $noteCount ?></strong>
                            <em>Ver notas</em>
                        </span>
                    </a>
                    <a class="statCard red" href="personajes.php">
                        <span class="statIcon" aria-hidden="true">⌁</span>
                        <span>
                            <small>Fichas PDF</small>
                            <strong><?= $sheetCount ?></strong>
                            <em>Gestionar fichas</em>
                        </span>
                    </a>
                </section>

                <section class="panel characterPanel">
                    <div class="panelHeader">
                        <div>
                            <span class="sectionKicker">Tu grupo</span>
                            <h2>Personajes recientes</h2>
                        </div>
                        <a href="personajes.php">Ver todos <span>→</span></a>
                    </div>

                    <div class="characterGrid">
                        <?php foreach ($visibleCharacters as $index => $character): ?>
                            <?php
                                $image = characterImage($character);
                                $characterId = (int) ($character["id_char"] ?? 0);
                                $characterHref = $databaseAvailable && $characterId > 0
                                    ? "personaje.php?id=" . $characterId
                                    : "personajes.php";
                            ?>
                            <a class="characterCard" href="<?= e($characterHref) ?>">
                                <span class="cardMenu" aria-hidden="true">⋮</span>
                                <?php if ($image): ?>
                                    <img src="<?= e($image) ?>" alt="Retrato de <?= e($character["name"]) ?>">
                                <?php else: ?>
                                    <span class="avatarFallback" aria-hidden="true"><?= e(substr($character["name"], 0, 1)) ?></span>
                                <?php endif; ?>
                                <strong><?= e($character["name"]) ?></strong>
                                <small>
                                    <?= e($character["raza"] ?? "Aventurero") ?>
                                    <?php if (!empty($character["subraza"])): ?>
                                        · <?= e($character["subraza"]) ?>
                                    <?php endif; ?>
                                </small>
                                <span class="sheetState">
                                    <i></i>
                                    Ficha preparada
                                    <b><?= str_pad((string) ($index + 1), 2, "0", STR_PAD_LEFT) ?></b>
                                </span>
                            </a>
                        <?php endforeach; ?>

                        <?php if (count($visibleCharacters) < 4): ?>
                            <a class="characterCard addCharacter" href="<?= $userId > 0 ? "addPersonajes.php" : "../login.php" ?>">
                                <span class="addCharacterIcon" aria-hidden="true">＋</span>
                                <strong>Nuevo personaje</strong>
                                <small>Amplía tu compañía</small>
                            </a>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="panel resourcePanel">
                    <div class="panelHeader">
                        <div>
                            <span class="sectionKicker">Accesos directos</span>
                            <h2>Recursos de aventura</h2>
                        </div>
                    </div>

                    <div class="resourceGrid">
                        <a class="resourceCard sheets" href="personajes.php">
                            <span class="resourceContent">
                                <small>PERSONAJES</small>
                                <strong>Fichas de héroe</strong>
                                <em><?= $sheetCount ?> fichas preparadas</em>
                            </span>
                            <span class="resourceArrow">→</span>
                        </a>
                        <a class="resourceCard grimoire" href="allSpells.php">
                            <span class="resourceContent">
                                <small>GRIMORIO</small>
                                <strong>Biblioteca arcana</strong>
                                <em><?= $spellCount ?> conjuros disponibles</em>
                            </span>
                            <span class="resourceArrow">→</span>
                        </a>
                        <a class="resourceCard journal" href="notes.php">
                            <span class="resourceContent">
                                <small>CRÓNICAS</small>
                                <strong>Diario de campaña</strong>
                                <em><?= $noteCount > 0 ? $noteCount . " notas guardadas" : "Empieza una nueva historia" ?></em>
                            </span>
                            <span class="resourceArrow">→</span>
                        </a>
                    </div>
                </section>

                <div class="lowerGrid">
                    <section class="panel compactPanel">
                        <div class="panelHeader compact">
                            <div>
                                <span class="sectionKicker">Biblioteca</span>
                                <h2>Conjuros destacados</h2>
                            </div>
                            <a href="allSpells.php">Ver grimorio</a>
                        </div>
                        <div class="spellList">
                            <?php foreach (array_slice($featuredSpells, 0, 4) as $spell): ?>
                                <?php
                                    $cleanName = trim((string) ($spell["name"] ?? "Conjuro"));
                                    $parenthesis = strpos($cleanName, "(");
                                    if ($parenthesis !== false) {
                                        $cleanName = trim(substr($cleanName, 0, $parenthesis));
                                    }
                                    $spellId = (int) ($spell["id_spell"] ?? 0);
                                    $spellHref = $spellId > 0
                                        ? "spell.php?id_spell=" . $spellId . "&prevPath=allSpells.php"
                                        : "allSpells.php?nameFilter=" . rawurlencode($cleanName) . "&submit=Filtrar";
                                ?>
                                <a class="spellRow" href="<?= e($spellHref) ?>">
                                    <span class="miniRune" aria-hidden="true">✧</span>
                                    <span>
                                        <strong><?= e($cleanName) ?></strong>
                                        <small><?= e($spell["escuela"] ?? "Arcano") ?></small>
                                    </span>
                                    <em><?= e($spell["level"] ?? "—") ?></em>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="panel compactPanel">
                        <div class="panelHeader compact">
                            <div>
                                <span class="sectionKicker">Campaña</span>
                                <h2>Notas rápidas</h2>
                            </div>
                            <a href="<?= $userId > 0 ? "newNote.php?uid=" . $userId : "../login.php" ?>">＋ Nueva nota</a>
                        </div>
                        <?php if ($notes): ?>
                            <ul class="quickNotes">
                                <?php foreach (array_slice($notes, 0, 4) as $note): ?>
                                    <li>
                                        <a href="note.php?id=<?= (int) ($note["ID"] ?? 0) ?>">
                                            <span><?= e($note["Nombre"] ?? "Nota sin título") ?></span>
                                            <small><?= e($note["Date"] ?? "") ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="emptyNotes">
                                <span aria-hidden="true">☾</span>
                                <p>Aún no hay anotaciones en este diario.</p>
                                <a href="<?= $userId > 0 ? "newNote.php?uid=" . $userId : "../login.php" ?>">
                                    <?= $userId > 0 ? "Escribir la primera" : "Inicia sesión para escribir" ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </main>

            <aside class="rightColumn" aria-label="Información de la aventura">
                <section class="sidePanel nextAdventure">
                    <span class="sideLabel">Próxima aventura</span>
                    <div class="nextAdventureCopy">
                        <small>PREPARA TU MESA</small>
                        <h2>La llamada de las sombras</h2>
                        <p>Tu compañía está lista para cruzar el umbral.</p>
                    </div>
                    <div class="partyStack" aria-label="<?= $characterCount ?> personajes disponibles">
                        <?php foreach (array_slice($characters, 0, 4) as $character): ?>
                            <?php $image = characterImage($character); ?>
                            <?php if ($image): ?>
                                <img src="<?= e($image) ?>" alt="<?= e($character["name"]) ?>">
                            <?php else: ?>
                                <span><?= e(substr($character["name"], 0, 1)) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <b>+<?= max(0, $characterCount - 4) ?></b>
                    </div>
                    <a class="primaryButton" href="personajes.php">Preparar el grupo <span>→</span></a>
                </section>

                <section class="sidePanel activityPanel">
                    <span class="sideLabel">Actividad reciente</span>
                    <div class="activityList">
                        <?php foreach (array_slice($characters, 0, 3) as $index => $character): ?>
                            <a href="personajes.php">
                                <span class="activityIcon" aria-hidden="true"><?= $index === 0 ? "♙" : ($index === 1 ? "⌁" : "✦") ?></span>
                                <span>
                                    <strong><?= e($character["name"]) ?> está listo para la aventura</strong>
                                    <small>Ficha preparada para jugar</small>
                                </span>
                            </a>
                        <?php endforeach; ?>
                        <a href="allSpells.php">
                            <span class="activityIcon" aria-hidden="true">✧</span>
                            <span>
                                <strong>El grimorio contiene <?= $spellCount ?> conjuros</strong>
                                <small>Ordenados por nivel y clase</small>
                            </span>
                        </a>
                    </div>
                    <a class="activityFooter" href="notes.php">Ver todas las crónicas</a>
                </section>

                <section class="sidePanel realmPanel">
                    <span class="sideLabel">El reino actual</span>
                    <div>
                        <h2>Tierras de la Niebla</h2>
                        <p>Una noche densa aguarda tras la entrada de la cueva.</p>
                    </div>
                    <footer>
                        <span>
                            <small>FECHA DEL REINO</small>
                            <strong><?= e($realmDate) ?></strong>
                        </span>
                        <b aria-label="Noche despejada">☾</b>
                    </footer>
                </section>
            </aside>
        </div>
    </div>
</body>
</html>
