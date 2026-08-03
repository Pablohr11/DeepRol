<?php
require_once("../classes/DbConnector.php");

$spellId = isset($_GET["id_spell"]) ? (int) $_GET["id_spell"] : 0;
$spellData = null;

try {
    if ($spellId > 0) {
        $db = DbConector::singleton();
        $spellResult = $db->getSpells((string) $spellId);
        $spellData = $spellResult[0] ?? null;
    }
} catch (Throwable $exception) {
    $spellData = null;
}

$previousPath = isset($_GET["prevPath"]) ? str_replace("--", "&", (string) $_GET["prevPath"]) : "allSpells.php";
if (
    $previousPath === ""
    || preg_match('/^[a-z][a-z0-9+.-]*:/i', $previousPath)
    || strpos($previousPath, "//") === 0
) {
    $previousPath = "allSpells.php";
}

$embedded = false;
$assetPrefix = "../";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title><?= $spellData ? htmlspecialchars($spellData["name"], ENT_QUOTES, "UTF-8") : "Conjuro no disponible" ?> · DeepRol</title>
    <script src="../scripts/theme.js"></script>
    <link rel="stylesheet" href="../styles/spell.css">
    <link rel="stylesheet" href="../styles/theme.css" data-deeprol-theme>
</head>
<body>
    <div class="mist"></div>
    <?php if ($spellData): ?>
        <?php include(__DIR__ . "/_partials/spellView.php"); ?>
    <?php else: ?>
        <main class="spellError">
            <span aria-hidden="true">✧</span>
            <h1>Conjuro no disponible</h1>
            <p>No hemos podido encontrar esta página del grimorio.</p>
            <a href="allSpells.php">Volver al grimorio</a>
        </main>
    <?php endif; ?>
</body>
</html>
