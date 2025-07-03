<?php

require_once("../classes/DbConnector.php");

$db = DbConector::singleton();

$spellId = $_GET["id_spell"];

$spellData = $db->getSpells($spellId)[0];

// var_dump($spellData);

$spellSchoolPath = "../resources/imgs/spelltypes/";
$spellImagePath = "../resources/imgs/spells/";

$prevPath = $_GET["prevPath"];

if (strpos($prevPath, "--") != -1) {
    $prevPath = str_replace("--", "&", $prevPath);
}

?>

<link rel="stylesheet" href="../styles/spell.css">
<div class="mist"></div>
<div id="spellDiv">
    <div id="spellTitleImageContainer">
        <?php $spellNameCut = strpos($spellData["name"], "(")-1; $cutName = substr($spellData["name"], 0, $spellNameCut) ?>
        <div id="spellNameImageContainer">
            <div id="spellImageContainer" style="width: 50px;height: 50px;
            <?php if (file_exists($spellImagePath.$cutName.".png")) { ?>
                    background-image:url('<?= $spellImagePath.$cutName?>.png')
                <?php } else {?>
                    background-image:url('<?= $spellImagePath?>generico.png')
                <?php } ?>
            ">
                
            
            
            

            </div>
            <h1><?=$cutName?></h1>
        </div>
        <img class="spellSchoolImage" src="<?= $spellSchoolPath.$spellData["escuela"]?>.png">
    </div>
 
    <span id="spellSubTitle"><?=$spellData["level"]?></span>
    
    <div id="spellContent">
        <div class="tableDiv">
            <p class="spellInfo spelltitle">Duracion</p>
            <p class="spellInfo spelltitle">Rango</p>
            <p class="spellInfo spelltitle">Tiempo de casteo</p>
            <p class="spellInfo spelltitle">Concentracion</p>
        </div>
        <div class="tableDiv">
            <p class="spellInfo"><?=$spellData["duracion"]?></p>
            <p class="spellInfo"><?=$spellData["rango"]?></p>
            <p class="spellInfo"><?=$spellData["casteo"]?></p>
            <p class="spellInfo"><?=$spellData["concentracion"]?></p>
        </div>
        <p class="spellInfo" id="spellDesc"><span class=" spelltitle">Descripcion</span><br> <?=$spellData["descr"]?></p>
        <p class="spellInfo"><span class=" spelltitle">Clase</span><br> <?=$spellData["clases"]?></p>
        <div class="bottomSpellContainer">
            <p class="spellInfo"><span class="spelltitle">Escuela </span><br><?=$spellData["escuela"]?></p>
            <a href="<?=$prevPath?>">← Volver</a>
        </div>
    </div>
</div>