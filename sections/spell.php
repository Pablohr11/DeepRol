<?php

require_once("../classes/DbConnector.php");

$db = DbConector::singleton();

$spellId = $_GET["id_spell"];

$spellData = $db->getSpells($spellId)[0];

// var_dump($spellData);


$prevPath = $_GET["prevPath"];

if (strpos($prevPath, "--") != -1) {
    $prevPath = str_replace("--", "&", $prevPath);
}

?>

<link rel="stylesheet" href="../styles/spell.css">

<div id="spellDiv">
    <h1><?=$spellData["name"]?></h1>
    <span id="spellSubTitle"><?=$spellData["level"]?></span>
    <p class="spellInfo">Duracion: <?=$spellData["duracion"]?></p>
    <p class="spellInfo">Rango <?=$spellData["rango"]?></p>
    <p class="spellInfo">Tiempo de casteo: <?=$spellData["casteo"]?></p>
    <p class="spellInfo">Concentracion: <?=$spellData["concentracion"]?></p>
    <p class="spellInfo">Descripcion: <?=$spellData["descr"]?></p>
    <p class="spellInfo">Clase: <?=$spellData["clases"]?></p>
    <p class="spellInfo">Escuela: <?=$spellData["escuela"]?></p>
    <a href="<?=$prevPath?>">Volver</a>
</div>