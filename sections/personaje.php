<?php

require_once("../classes/DbConnector.php");
//var_dump($_POST);
$db = DbConector::singleton();


if (isset($_GET["id"])) {
    $charId = $_GET["id"];
    $charData = $db->getChar($charId);
    $spellsIds = str_replace('"', '',$db->getSpellsIds($charId));
    $charSpells = $db->getSpells($spellsIds, "yes");
}

?>

<link rel="stylesheet" href="../styles/char.css">

<div id="charDiv">
    <img src="../resources/chars/draelith_cuerpo_completo.png" id="fullBodyImg" alt="">
    <div class="charInfo">
        <h1><?=$charData["name"]?></h1>
        <span id="charSubTitle"><?=$charData["raza"]?> / <?=$charData["clase"]?> (<?=$charData["nivel"]?>)</span>
        <h3>Conjuros</h3>
        <?php foreach ($charSpells as $key => $spell) { ?>
            <a class="spellsInfo" href="spell.php?id_spell=<?=$spell["id_spell"]?>&prevPath=<?=$_SERVER['REQUEST_URI']?>"> <?=$spell["name"]?> - <?=$spell["level"]?> - <?=$spell["casteo"]?></a>
        <?php } ?>
        <a href="allSpells.php?classFilter=<?=strtolower($charData["clase"])?>">Añadir Conjuro</a>
    </div>
</div>