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

if (isset($charSpells) && $charSpells != null) {
    foreach ($charSpells as $spell) {
        $grouped[$spell['level']][] = $spell;
    }
}


?>

<link rel="stylesheet" href="../styles/index.css">
<link rel="stylesheet" href="../styles/char.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script src="../scripts/char.js" ></script>
<div id="charDiv">
    <div>
        <img src="../resources/chars/draelith_cuerpo_completo.png" id="fullBodyImg" alt="">
        <button id="showPdfButton">Ver Ficha</button>
    </div>
    <div class="charInfo">
        <h1><?=$charData["name"]?></h1>
        <span id="charSubTitle"><?=$charData["raza"]?> / <?=$charData["clase"]?> (<?=$charData["nivel"]?>)</span>

        <h2>Caracteristicas</h2>
        <div class="charStats">
            <div class="charStatContainer">
                <div class="decorator">
                    <div class="textHidden leftCorner">a</div>
                    <div class="textHidden center">a</div>
                    <div class="textHidden rightCorner">a</div>
                </div>
                <div class="charStat">
                    <h3>Fuerza</h3>
                    <span class="stat" id="STR">10</span>
                    <span class="modifier" id="STRmod">0</span>
                </div>
            </div>
            <div class="charStatContainer">
                <div class="decorator">
                    <div class="textHidden leftCorner">a</div>
                    <div class="textHidden center">a</div>
                    <div class="textHidden rightCorner">a</div>
                </div>
                <div class="charStat">
                    <h3>Destreza</h3>
                    <span class="stat" id="DEX">10</span>
                    <span class="modifier" id="DEXmod">0</span>
                </div>
            </div>
            <div class="charStatContainer">
                <div class="decorator">
                    <div class="textHidden leftCorner">a</div>
                    <div class="textHidden center">a</div>
                    <div class="textHidden rightCorner">a</div>
                </div>
                <div class="charStat">
                    <h3>Constitucion</h3>
                    <span class="stat" id="CON">10</span>
                    <span class="modifier" id="CONmod">0</span>
                </div>
            </div>
            <div class="charStatContainer">
                <div class="decorator">
                    <div class="textHidden leftCorner">a</div>
                    <div class="textHidden center">a</div>
                    <div class="textHidden rightCorner">a</div>
                </div>
                <div class="charStat">
                    <h3>Inteligencia</h3>
                    <span class="stat" id="INT">10</span>
                    <span class="modifier" id="INTmod">0</span>
                </div>
            </div>
            <div class="charStatContainer">
                <div class="decorator">
                    <div class="textHidden leftCorner">a</div>
                    <div class="textHidden center">a</div>
                    <div class="textHidden rightCorner">a</div>
                </div>
                <div class="charStat">
                    <h3>Sabiduria</h3>
                    <span class="stat" id="WIS">10</span>
                    <span class="modifier" id="WISmod">0</span>
                </div>
            </div>
            <div class="charStatContainer">
                <div class="decorator">
                    <div class="textHidden leftCorner">a</div>
                    <div class="textHidden center">a</div>
                    <div class="textHidden rightCorner">a</div>
                </div>
                <div class="charStat">
                    <h3>Carisma</h3>
                    <span class="stat" id="CHA">10</span>
                    <span class="modifier" id="ST Charisma">0</span>
                </div>
            </div>
            
        </div>    

            <div id="pPasContainer">
                <span id="Passive"></span>
            </div>
        <div>
            <h3>Tiradas de salvación</h3>
        </div>

        <h2>Conjuros</h2>

        <div class="tabs" id="tabs">
            <?php foreach ($grouped as $level => $spells): ?>
                <div class="tab" data-tab="level<?=$level?>">
                    <?php //$level == 0 ? 'Trucos' : 'Nivel ' . $level ?>
                    <?= $level ?>
                </div>
            <?php endforeach; ?>
                
            <a href="allSpells.php?id_char=<?=$charId?>&classFilter=<?=strtolower($charData["clase"])?>" class="tab"  title="Añadir Conjuro">+</a>
               
        </div>

        <?php foreach ($grouped as $key => $group) { ?>
            <div class="spellList" id="level-<?=$key?>">

                <?php foreach ($group as $keySpell => $spell) { ?>
                    <?php //var_dump($spell)  ?>
                    <a class="spellsInfo" href="spell.php?id_spell=<?=$spell["id_spell"]?>&prevPath=<?=$_SERVER['REQUEST_URI']?>"> <?=substr($spell["name"], 0, strpos($spell["name"],"("))?> - <?=$spell["casteo"]?></a>
                <?php } ?>
        
            </div>
        <?php } ?>

    </div>
	<div id="embedContainer">
		<div id="embedTopBar">
			<span id="closeEmbed">X</span>
		</div>
		<embed id="embed"
	        src="../resources/fichas/<?=$charData["pdf_path"]?>"
	        type="application/pdf"
	        width="100%"
	        height="100%"
	        title="Embedded PDF Viewer"
	    />
	</div>
</div>

<script>
    setPdfFields("../resources/fichas/<?=$charData["pdf_path"]?>");
</script>