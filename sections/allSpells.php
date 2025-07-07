<?php

require_once("../classes/DbConnector.php");

$db = DbConector::singleton();

$spells = $db->getAllSpells();

$spellsLevels = $db->getAllSpellsLevels();

$arrowUpSvg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="20" height="20" viewBox="0 0 20 20" xml:space="preserve"><desc>Created with Fabric.js 5.2.4</desc><defs></defs><g transform="matrix(0 0 0 0 0 0)" id="d8cfbb01-0150-48b1-96f7-edb208d4fe24"></g><g transform="matrix(1 0 0 1 10 10)" id="1bf01d78-b7d1-4e50-b7d3-5d4aae8885d0"><rect style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1; visibility: hidden;" vector-effect="non-scaling-stroke" x="-10" y="-10" rx="0" ry="0" width="20" height="20"/></g><g transform="matrix(0.63 0 0 0.63 10.06 10.04)"><path style="stroke: none;stroke-width: 1;stroke-dasharray: none;stroke-linecap: butt;stroke-dashoffset: 0;stroke-linejoin: miter;stroke-miterlimit: 4;fill:#202020;fill-rule: nonzero;opacity: 1;" transform=" translate(-16.5, -14)" d="M 18.221 7.206 L 27.806 16.791 C 28.685000000000002 17.67 28.685000000000002 19.108 27.806 19.986 L 27.006 20.787 C 26.129 21.665 24.69 21.665 23.812 20.787 L 16.497 13.471999999999998 L 9.181999999999999 20.787 C 8.303999999999998 21.665 6.864999999999998 21.665 5.987999999999999 20.787 L 5.187999999999999 19.986 C 4.308999999999999 19.108 4.308999999999999 17.67 5.187999999999999 16.791 L 14.774999999999999 7.2059999999999995 C 15.245999999999999 6.734 15.877999999999998 6.523999999999999 16.497999999999998 6.558999999999999 C 17.115 6.524 17.748 6.734 18.221 7.206 z" stroke-linecap="round"/></g></svg>';

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
    $spellsLevels = $db->getAllSpellsLevels($filtros);

    // $spellsLevels = 
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

array_shift($spellsLevels);
// echo("<pre>");
// var_dump($spellsLevels);
// echo("</pre>");
// die();
?>

<link rel="stylesheet" href="../styles/allSpells.css">
<link rel="stylesheet" href="../styles/index.css">

<div id="allSpellsContainer">
    <div class="stickyDiv">
        <div class="subDiv">
            <h2>Filtros</h2>
            <?php if (isset($charId)) { ?>
                <div>
                    <a href="personaje.php?id=<?=$charId?>">← VOLVER</a>
                </div>
            <?php } ?>
        </div>
        <form id="filtros" method="get">
            <?php if (isset($charId)) { ?>
                <input type="hidden" name="id_char" value="<?=$charId?>">
            <?php } ?>
            <input type="text" name="nameFilter">
            <div class="classesFilterDiv">
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
    </div>
    <div class="subDiv2">
        <div id="subDiv3">
            <span class="spell first" id="firstSpell">TOTAL: <?= count($spells) ?></span>
            <div>
                <img id="displayList" src="../resources/imgs/icons/list.png" alt="">
                <img id="displayColumns" class="selectedDisplay" src="../resources/imgs/icons/columns.png" alt="">
            </div>
        </div>
        <div class="classesFilterDiv">
            <?php foreach ($spellsLevels as  $spellLevel) { ?>
                <button onclick="autoScroll('<?=$spellLevel['level']?>')"><?=$spellLevel["level"]?></button>
            <?php }  ?>
            <!-- <input type="radio" id="bardo" value="bardo" name="classFilter" <?php if($classFilter == "bardo") echo("checked") ?>>             -->
        </div>
    </div>
    <!-- >AKI <!-->
    <div id="spellListHehe" class="displayColumns">

    <dl>
    <?php if (isset($currentLevel)) { ?>
        <?php $index = 0;  ?>
            <dt><h2 id="<?=$currentLevel?>"><?=$currentLevel?></h2></dt>
            <div class="actualSpellList">
        
            <?php foreach ($spells as $key => $spell) { ?>
                <?php if($spell["level"] != $currentLevel) {
                    $currentLevel = $spell["level"]; $index++; ?>
                    </div>
                    </dl>
                    <dl <?php if (($index+2)%3==0) { ?>class="dlConFilete"<?php } ?>>
                        <dt><h2 id="<?=$currentLevel?>"><?=$currentLevel?></h2></dt>
                        <div class="actualSpellList">
                <?php } ?>
                
                <?php if (isset($charId)) { ?>
                    <a href="spell.php?id_spell=<?=$spell["id_spell"]?>&charId=<?=$charId?>&prevPath=<?=$prevPathParameters?>" class="spell"><?=$spell["name"]?></a>
                    <?php if (in_array( $spell["id_spell"], $spellList)) { ?>
                        <span class="addSpell" >✓</span>
                    <?php } else { ?>
                        <a href="addSpell.php?id_spell=<?=$spell["id_spell"]?>&charId=<?=$charId?>&prevPath=<?=$prevPathParameters?>"  class="addSpell"> + </a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="spell.php?id_spell=<?=$spell["id_spell"]?>&prevPath=<?=$prevPathParameters?>" class="spell"><?=$spell["name"]?></a>
                <?php }  ?>
            <?php } ?>
        <?php } ?>
        </div>
    </dl>
    </div>
    <button onclick="scrollUp()" id="volverArriba" class="hidden"><?=$arrowUpSvg?></button>
</div>

<script>
    document.getElementById('filtros').addEventListener('submit', function(e) {
        const nameFilterInput = document.getElementById('nameFilter');
        if (!nameFilterInput.value.trim()) {
            nameFilterInput.removeAttribute('name');
        }
    });

    document.getElementById("displayList").addEventListener('click', function() {
        if(document.getElementById("spellListHehe").classList.contains("displayColumns")) {
            document.getElementById("spellListHehe").classList.remove("displayColumns");
            document.getElementById("spellListHehe").classList.add("displayList");
        }
        if(document.getElementById("displayColumns").classList.contains("selectedDisplay")) {
            document.getElementById("displayColumns").classList.remove("selectedDisplay");
            document.getElementById("displayList").classList.add("selectedDisplay");
        }
    })

    document.getElementById("displayColumns").addEventListener('click', function() {
        if(document.getElementById("spellListHehe").classList.contains("displayList")) {
            document.getElementById("spellListHehe").classList.add("displayColumns");
            document.getElementById("spellListHehe").classList.remove("displayList");
        }
        if(document.getElementById("displayList").classList.contains("selectedDisplay")) {
            document.getElementById("displayColumns").classList.add("selectedDisplay");
            document.getElementById("displayList").classList.remove("selectedDisplay");
        }
    })


    function autoScroll(idToScrollTo) {
        console.log(document.getElementById(idToScrollTo));
        console.log(idToScrollTo);
        if (document.getElementById(idToScrollTo)) {
            window.scrollTo(0,(document.getElementById(idToScrollTo).getBoundingClientRect().top-135));
        }
    }
    function scrollUp() {
        window.scrollTo(0,0);

    }

    window.addEventListener('scroll', function() {
        
        var targetButton = document.getElementById("volverArriba");
        
        if (window.scrollY != 0 ) {
            if (targetButton.classList.contains("hidden")) {    
                targetButton.classList.remove("hidden");
            }
        } else {
            if (!targetButton.classList.contains("hidden")) {    
                targetButton.classList.add("hidden");
            }
        }
    })
    window.addEventListener("load", (event) => {
        var targetwidth = document.getElementsByClassName("classesFilterDiv")[0].getBoundingClientRect().width;
        console.log(targetwidth);
        document.getElementsByClassName("classesFilterDiv")[1].style.width = targetwidth ;

    });
</script>

