<?php
require_once("../../classes/DbConnector.php");

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

$embedded = true;
$assetPrefix = "../../";
$previousPath = "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Detalle del conjuro</title>
    <script src="../../scripts/theme.js"></script>
    <link rel="stylesheet" href="../../styles/spell.css">
    <link rel="stylesheet" href="../../styles/theme.css" data-deeprol-theme>
</head>
<body class="embeddedSpell">
    <div class="mist"></div>
    <?php if ($spellData): ?>
        <?php include(__DIR__ . "/spellView.php"); ?>
    <?php else: ?>
        <main class="spellError compact">
            <span aria-hidden="true">✧</span>
            <h1>Conjuro no disponible</h1>
        </main>
    <?php endif; ?>
</body>
</html>
