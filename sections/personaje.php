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

<div id="charDiv">
    <div>
        <img src="../resources/chars/draelith_cuerpo_completo.png" id="fullBodyImg" alt="">
        <button id="showPdfButton">Ver Ficha</button>
    </div>
    <div class="charInfo">
        <h1><?=$charData["name"]?></h1>
        <span id="charSubTitle"><?=$charData["raza"]?> / <?=$charData["clase"]?> (<?=$charData["nivel"]?>)</span>
        <h2>Conjuros</h2>
        <!-- <?php foreach ($charSpells as $key => $spell) { ?>
            <a class="spellsInfo" href="spell.php?id_spell=<?=$spell["id_spell"]?>&prevPath=<?=$_SERVER['REQUEST_URI']?>"> <?=$spell["name"]?> - <?=$spell["casteo"]?></a>
        <?php } ?> -->

        <div class="tabs" id="tabs">
            <?php foreach ($grouped as $level => $spells): ?>
                <div class="tab" data-tab="level<?=$level?>">
                    <?php //$level == 0 ? 'Trucos' : 'Nivel ' . $level ?>
                    <?= $level ?>
                </div>
            <?php endforeach; ?>
                <!-- <div class="tab"> -->
                    <a href="allSpells.php?id_char=<?=$charId?>&classFilter=<?=strtolower($charData["clase"])?>" class="tab"  title="Añadir Conjuro">+</a>
                <!-- </div> -->
        </div>

        <?php foreach ($grouped as $key => $group) { ?>
            <div class="spellList" id="level-<?=$key?>">

                <?php foreach ($group as $keySpell => $spell) { ?>
                    <?php //var_dump($spell)  ?>
                    <a class="spellsInfo" href="spell.php?id_spell=<?=$spell["id_spell"]?>&prevPath=<?=$_SERVER['REQUEST_URI']?>"> <?=substr($spell["name"], 0, strpos($spell["name"],"("))?> - <?=$spell["casteo"]?></a>
                <?php } ?>
        
            </div>
        <?php } ?>

        <!-- <a href="allSpells.php?id_char=<?php//echo($charId)?>&classFilter=<?php//echo(strtolower($charData["clase"]))?>">Añadir Conjuro</a> -->
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
        document.getElementById("embedContainer").style.display = "block";

    })

    document.getElementById("closeEmbed").addEventListener('click', function() {
        document.getElementById("embedContainer").style.display = "none"
    })

    const tabs = document.querySelectorAll('.tab');
    const lists = document.querySelectorAll('.spellList');

    function activateTab(index) {
        tabs.forEach(tab => tab.classList.remove('active'));
        lists.forEach(list => list.classList.remove('active'));

        tabs[index].classList.add('active');
        lists[index].classList.add('active');
    }

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => activateTab(i));
    });

    // Activar la primera pestaña por defecto
    activateTab(0);

</script>