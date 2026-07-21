<?php

require_once __DIR__ . '/../src/bootstrap.php';
//var_dump($_POST);
$db = DbConector::singleton();


if (isset($_GET["id"])) {
<<<<<<< Updated upstream
    $charId = $_GET["id"];
    $charData = $db->getChar($charId);
    $spellsIds = str_replace('"', '',$db->getSpellsIds($charId));
=======
    $charId = (int) $_GET["id"];
    $charData = $db->getCharForUser($charId, require_login());
    if (!$charData) { http_response_code(404); exit('Personaje no encontrado.'); }
    $spellsIds = str_replace('"', '', $db->getSpellsIds($charId));
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
<div id="charDiv">
    <!-- //? LEFT CONTAINER  -->
    <div>
        <img src="../resources/chars/draelith_cuerpo_completo.png" id="fullBodyImg" alt="">
        <div id="sheetButtons">
            <button id="showPdfButton">Ver Ficha</button>
<<<<<<< Updated upstream
            <button id="showPdfButton">Actualizar Ficha</button>
=======
<div class="mist"></div>
<!-- <div class="contenedor-linterna" id="contenedor-linterna">
<div id="gancho"></div>
  <div class="linterna" id="linterna"></div>
  <div class="haz-de-luz" id="haz-de-luz"></div>
</div> -->

    <div id="charDiv">
        <!-- //? LEFT CONTAINER  -->
        <div class="charControls">
            <div class="card">
                <img src="../resources/chars/<?=$charData["name"]?>/<?=$charData["full_body_image_path"]?>" id="fullBodyImg" alt="" class="personaje">
            </div>
            <div id="sheetButtons">
                <button id="showPdfButton">Ver Ficha</button>
                <button id="showPdfButton">Actualizar Ficha</button>
            </div>
            <div id="stContainer">
                <h3>Tiradas de salvación</h3>
                <div class="stDiv">
                    <span class="stValue"  id="ST-Strength">+2</span>
                    <span class="stInfo">Fuerza</span>
                </div>
                <div class="stDiv">
                    <span class="stValue"  id="ST-Dexterity">+2</span>
                    <span class="stInfo">Destreza</span>
                </div>
                <div class="stDiv">
                    <span class="stValue"  id="ST-Constitution">+2</span>
                    <span class="stInfo">Constitucion</span>
                </div>
                <div class="stDiv">
                    <span class="stValue"  id="ST-Intelligence">+2</span>
                    <span class="stInfo">Inteligencia</span>
                </div>
                <div class="stDiv">
                    <span class="stValue"  id="ST-Wisdom">+2</span>
                    <span class="stInfo">Sabiduria</span>
                </div>
                <div class="stDiv">
                    <span class="stValue"  id="ST-Charisma">+2</span>
                    <span class="stInfo">Carisma</span>
                </div>
            </div>
>>>>>>> Stashed changes
=======
            <button id="updatePdfButton" type="button" disabled title="Próximamente">Actualizar ficha</button>
>>>>>>> Stashed changes
        </div>
        <div id="stContainer">
            <h3>Tiradas de salvación</h3>
            <div class="stDiv">
                <span class="stValue"  id="ST-Strength">+2</span>
                <span class="stInfo">Fuerza</span>
            </div>
            <div class="stDiv">
                <span class="stValue"  id="ST-Dexterity">+2</span>
                <span class="stInfo">Destreza</span>
            </div>
<<<<<<< Updated upstream
            <div class="stDiv">
                <span class="stValue"  id="ST-Constitution">+2</span>
                <span class="stInfo">Constitucion</span>
            </div>
            <div class="stDiv">
                <span class="stValue"  id="ST-Intelligence">+2</span>
                <span class="stInfo">Inteligencia</span>
            </div>
            <div class="stDiv">
                <span class="stValue"  id="ST-Wisdom">+2</span>
                <span class="stInfo">Sabiduria</span>
            </div>
            <div class="stDiv">
                <span class="stValue"  id="ST-Charisma">+2</span>
                <span class="stInfo">Carisma</span>
=======
            <?php if ($grouped) { ?>
                <div id="spellListContainer">
                    <?php foreach ($grouped as $key => $group) { ?>
                        <div class="spellList" id="level-<?=$key?>">

                            <?php foreach ($group as $keySpell => $spell) { ?>
                                <?php //var_dump($spell)  ?>
                                <!-- <a class="spellsInfo" href="spell.php?id_spell=<?=$spell["id_spell"]?>&prevPath=<?=$_SERVER['REQUEST_URI']?>"> <?=substr($spell["name"], 0, strpos($spell["name"],"("))?> - <?=$spell["casteo"]?></a> -->
                                <a class="spellsInfo" onclick='showEmbedSpell(<?=$spell["id_spell"]?>)'> <?=substr($spell["name"], 0, strpos($spell["name"],"("))?> - <?=$spell["casteo"]?></a>
                            <?php } ?>
                    
                        </div>
                        <div class="spellCounter" id="spellCounter-<?=$key?>">
                            <h3>Gastado</h3>
                            <h5><?=$key?></h5>
                            <div class="spellCounterInner">
                                <div class="spellSpace counter-1"></div>
                                <div class="spellSpace counter-2"></div>
                                <div class="spellSpace counter-3"></div>
                                <div class="spellSpace counter-4"></div>
                            </div>
                            <div class="counterButtons">
                                <button class="minus-counter" value="0">-</button>
                                <input type="hidden" id="valueCounter" value="0">
                                <button class="add-counter" value=0>+</button>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            <?php } ?>
        </div>
        <div id="embedContainer">
            <div id="embedTopBar">
                <span id="closeEmbed">X</span>
>>>>>>> Stashed changes
            </div>
        </div>
        <div id="imgAmpliadaContainer" class="hidden">
            <div id="imgAmpliadaInnerContainer">
                <img src="" id="imgAmpliada">
                <button id="closeImage">X</button>
            </div>
        </div>
        <div id="spellAmpliadoContainer" class="hidden">
            <div id="spellAmpliadaInnerContainer">
                <iframe src="" frameborder="0" id="spellIframe"></iframe>
                <button id="closeSpellIframe">X</button>
            </div>
        </div>
        <div id="imgAmpliadaContainer" class="hidden">
            <div id="imgAmpliadaInnerContainer">
                <img src="" id="imgAmpliada">
                <button id="closeImage">X</button>
            </div>
        </div>
        <div id="spellAmpliadoContainer" class="hidden">
            <div id="spellAmpliadaInnerContainer">
                <iframe src="" frameborder="0" id="spellIframe"></iframe>
                <button id="closeSpellIframe">X</button>
            </div>
        </div>
    </div>

<<<<<<< Updated upstream
<<<<<<< Updated upstream
    <!-- //? MAIN CONTENT -->
    <div class="charInfo">
        <div id="topContent">
            <div>
                <div class="headerDisplay">
                    <div>
                        <h1><?=$charData["name"]?></h1>
                        <span id="charSubTitle">
                            <span id="Race"></span> / <span id="ClassLevel"></span> / <span id="Background"></span>
                        </span>
                    </div>
                    <div class="HPACDiv">
                        <div class="armorStat">
                            <div>
                                <h3>Clase de armadura</h3>
                                <span class="stat" id="AC">14</span>
                            </div>
                        </div>
                        <div class="healthStat">
                            <div>
                                <h3>Puntos Vida</h3>
                                <span class="stat" id="HPMax">35</span>
                            </div>
                        </div>
                    </div>
                </div>
=======
=======
>>>>>>> Stashed changes

<script>
    setPdfFields("../resources/chars//<?=$charData["name"]?>/ficha.pdf");
>>>>>>> Stashed changes

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
                            <span class="modifier" id="CHamod">0</span>
                        </div>
                    </div>                   
                </div>    
                <div class="additionalStats">
                    <div id="pPasContainer">
                        <span id="Passive"></span>
                        <span id="ppasInfo">Percepción Pasiva</span>
                    </div>
                    <div id="profContainer">
                        <span id="ProfBonus"></span>
                        <span id="profInfo">Bono de competencia</span>
                    </div>
                </div>
            </div>

<<<<<<< Updated upstream
            <div id="skillsDiv">
                <h2>Habilidades</h2>
                <div id="skillsContainer">
                    <div class="skillsContainerCol">
                        <div class="skillDiv">
                            <span class="stValue"  id="Acrobatics">+2</span>
                            <span class="stInfo">Acrobacias</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Animal">+2</span>
                            <span class="stInfo">Trato Animal</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Arcana">+2</span>
                            <span class="stInfo">C. Arcano</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Athletics">+2</span>
                            <span class="stInfo">Atletismo</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Deception"></span>
                            <span class="stInfo">Engaño</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="History"></span>
                            <span class="stInfo">Historia</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Insight"></span>
                            <span class="stInfo">Perspicacia</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Intimidation"></span>
                            <span class="stInfo">Intimidacion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Investigation"></span>
                            <span class="stInfo">Investigacion</span>
                        </div>
                    </div>
                    <div class="skillsContainerCol">
                        <div class="skillDiv">
                            <span class="stValue"  id="Medicine"></span>
                            <span class="stInfo">Medicina</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Nature"></span>
                            <span class="stInfo">Naturaleza</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Perception"></span>
                            <span class="stInfo">Percepcion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Performance"></span>
                            <span class="stInfo">Interpretacion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Persuasion"></span>
                            <span class="stInfo">Persuasion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Religion"></span>
                            <span class="stInfo">Religion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="SleightofHand"></span>
                            <span class="stInfo">Juego de manos</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Stealth"></span>
                            <span class="stInfo">Sigilo</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue"  id="Survival"></span>
                            <span class="stInfo">Supervivencia</span>
                        </div>
                    </div>
                </div>
            </div>
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
=======
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left; // posición X dentro del contenedor
        const y = e.clientY - rect.top;  // posición Y dentro del contenedor

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = -(y - centerY) / 15;
        const rotateY = (x - centerX) / 15;

        personaje.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });

    card.addEventListener('mouseleave', () => {
        personaje.style.transform = 'rotateX(0deg) rotateY(0deg)';
    });

    document.getElementById("linterna").addEventListener('click', function() {
        document.getElementById("linterna").classList.toggle("linterna_off");
        document.getElementById("haz-de-luz").classList.toggle("haz-de-luz_off");
>>>>>>> Stashed changes
        
            </div>
        <?php } ?>

<<<<<<< Updated upstream
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
=======
    document.getElementById("gancho").addEventListener('click', function() {
        document.getElementById("contenedor-linterna").classList.add("fallen-contenedor-linterna")
    })
</script>

<script>
    const smallImage = document.getElementById("fullBodyImg");
    const imgAmpliadaContainer = document.getElementById("imgAmpliadaContainer");
    const imagenAmpliada = document.getElementById("imgAmpliada");
    const closeImage = document.getElementById("closeImage");

    smallImage.addEventListener('click', function() {
        imagenAmpliada.setAttribute("src", smallImage.getAttribute("src"));
        imgAmpliadaContainer.classList.toggle("hidden");
        imgAmpliadaContainer.classList.toggle("shown");
    })
<<<<<<< Updated upstream

    closeImage.addEventListener('click', function() {
        imgAmpliadaContainer.classList.toggle("hidden");
        imgAmpliadaContainer.classList.toggle("shown");
    })
</script>
<script>
    const spellAmpliadoContainer = document.getElementById("spellAmpliadoContainer");
    const spellIframe = document.getElementById("spellIframe");
    const closeSpellIframe = document.getElementById("closeSpellIframe");

    function showEmbedSpell(spellId) {
        spellIframe.setAttribute("src", "spellToEmbed.php?id_spell="+spellId);
        spellAmpliadoContainer.classList.toggle("hidden");
        spellAmpliadoContainer.classList.toggle("shown");


    }
    closeSpellIframe.addEventListener('click', function() {
        spellAmpliadoContainer.classList.toggle("hidden");
        spellAmpliadoContainer.classList.toggle("shown");
    })
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
</script>
=======
</script>
>>>>>>> Stashed changes
