<?php

require_once("../classes/DbConnector.php");

$db = DbConector::singleton();

$spells = $db->getAllSpells();


$prevPathParameters = "allSpells.php";

$classFilter = "";

if (isset($_GET["submit"]) || isset($_GET["nameFilter"]) && ($_GET["nameFilter"] != "") || isset($_GET["classFilter"])) {
    $filtros = "";
    if (isset($_GET["nameFilter"]) && $_GET["nameFilter"] != "") {
        $filtros .= "name like '%".$_GET["nameFilter"]."%'";
        $prevPathParameters .= "?nameFilter=".$_GET["nameFilter"];
    }
    
    if (isset($_GET["classFilter"])) {
        $classFilter = $_GET["classFilter"];
        if ($filtros == "") {
            $filtros .= "clases like '%".$_GET["classFilter"]."%'";
            $prevPathParameters .= "?classFilter=".$_GET["classFilter"];
        } else {
            $filtros .= " AND clases like '%".$_GET["classFilter"]."%'";
            $prevPathParameters .= "&classFilter=".$_GET["classFilter"];
        }
    }
    
    $spells = $db->getAllSpells($filtros);
    
}
if (isset($spells[0]["level"])) {
    $currentLevel = $spells[0]["level"];
}

if (isset($_GET["id_char"])) {

    $charId = $_GET["id_char"];
    $prevPathParameters .= "--id_char=".$charId;
    $spellList = explode( ", ",$db->getSpellsIds($charId));
} else {
    $spellList = [];
}

?>

<link rel="stylesheet" href="../styles/allSpells.css">

<div id="allSpellsContainer">
    <h2>Filtros</h2>
    <form id="filtros" method="get">
        <?php if (isset($charId)) { ?>
            <input type="hidden" name="id_char" value="<?=$charId?>">
        <?php } ?>
        <input type="text" name="nameFilter">
        <div id="classesFilterDiv">
            <input type="radio" id="bardo" value="bardo" name="classFilter" <?php if($classFilter == "bardo") echo("checked") ?>>
            <label for="bardo">Bardo</label>
            
            <input type="radio" id="brujo" value="brujo" name="classFilter" <?php if($classFilter == "brujo") echo("checked") ?>>
            <label for="brujo">Brujo</label>
            
            <input type="radio" id="clerigo" value="clerigo" name="classFilter" <?php if($classFilter == "clerigo") echo("checked") ?>>
            <label for="clerigo">Clerigo</label>
            
            <input type="radio" id="druida" value="druida" name="classFilter" <?php if($classFilter == "druida") echo("checked") ?>>
            <label for="druida">Druida</label>
            
            <input type="radio" id="explorador" value="explorador" name="classFilter" <?php if($classFilter == "explorador") echo("checked") ?>>
            <label for="explorador">Explorador</label>
            
            <input type="radio" id="hechicero" value="hechicero" name="classFilter" <?php if($classFilter == "hechicero") echo("checked") ?>>
            <label for="hechicero">Hechicero</label>
            
            <input type="radio" id="rituale" value="rituale" name="classFilter" <?php if($classFilter == "rituale") echo("checked") ?>>
            <label for="rituale">Lanzador</label>
            
            <input type="radio" id="mago" value="mago" name="classFilter" <?php if($classFilter == "mago") echo("checked") ?>>
            <label for="mago">Mago</label>
            
            <input type="radio" id="paladin" value="paladin" name="classFilter" <?php if($classFilter == "paladin") echo("checked") ?>>
            <label for="paladin">Paladin</label>
        </div>
        <input type="submit" value="Filtrar" name="submit">
    </form>
    <span class="spell">TOTAL: <?= count($spells) ?></span>
    <dl>
        <dt>
            <?php if (isset($currentLevel)) { ?> 
                <h2><?=$currentLevel?></h2>
            <?php } ?>
        </dt>
        <?php foreach ($spells as $key => $spell) {
            if ($spell["level"] == $currentLevel) { ?>
            <dd>
                <a href="spell.php?id_spell=<?=$spell["id_spell"]?>&charId=<?=$charId?>&prevPath=<?=$prevPathParameters?>" class="spell"><?=$spell["name"]?></a>
                <?php if (isset($charId)) { ?>
                    <?php if (in_array( $spell["id_spell"], $spellList)) { ?>
                        <span class="addSpell" >✓</span>
                    <?php } else { ?>
                        <a href="addSpell.php?id_spell=<?=$spell["id_spell"]?>&charId=<?=$charId?>&prevPath=<?=$prevPathParameters?>"  class="addSpell"> + </a>
                    <?php } ?>
                <?php } ?>
            </dd>
            <?php } else {?>
                <?php $currentLevel = $spell["level"] ?>                
                <dt><h2><?=$currentLevel?></h2></dt>
                <!-- <a class="spellsInfo" href="spell.php?id_spell=<?=$spell["id_spell"]?>&id_char=<?=$charId?>"></a> -->
                <dd>
                <a href="spell.php?id_spell=<?=$spell["id_spell"]?>&charId=<?=$charId?>&prevPath=<?=$prevPathParameters?>" class="spell"><?=$spell["name"]?></a>
                <?php if (isset($charId)) { ?>
                    <?php if (in_array( $spell["id_spell"], $spellList)) { ?>
                        <span class="addSpell" >✓</span>
                    <?php } else { ?>
                        <a href="addSpell.php?id_spell=<?=$spell["id_spell"]?>&charId=<?=$charId?>&prevPath=<?=$prevPathParameters?>"  class="addSpell"> + </a>
                    <?php } ?>
                <?php } ?>
                </dd>
                <?php }
        } ?>
    </dl>
    <?php ?>
</div>

<script>
    document.getElementById('filtros').addEventListener('submit', function(e) {
        const nameFilterInput = document.getElementById('nameFilter');
        if (!nameFilterInput.value.trim()) {
            nameFilterInput.removeAttribute('name');
        }
    });
</script>