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
    <div>
        <img src="../resources/chars/draelith_cuerpo_completo.png" id="fullBodyImg" alt="">
        <button id="showPdfButton">Ver Ficha</button>
    </div>
    <div class="charInfo">
        <h1><?=$charData["name"]?></h1>
        <span id="charSubTitle"><?=$charData["raza"]?> / <?=$charData["clase"]?> (<?=$charData["nivel"]?>)</span>
        <h3>Conjuros</h3>
        <?php foreach ($charSpells as $key => $spell) { ?>
            <a class="spellsInfo" href="spell.php?id_spell=<?=$spell["id_spell"]?>&prevPath=<?=$_SERVER['REQUEST_URI']?>"> <?=$spell["name"]?> - <?=$spell["level"]?> - <?=$spell["casteo"]?></a>
        <?php } ?>
        <a href="allSpells.php?id_char=<?=$charId?>&classFilter=<?=strtolower($charData["clase"])?>">Añadir Conjuro</a>
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
    document.getElementById("showPdfButton").addEventListener('click', function() {
        document.getElementById("embedContainer").style.display = "block"
    })

    document.getElementById("closeEmbed").addEventListener('click', function() {
        document.getElementById("embedContainer").style.display = "none"
    })


</script>