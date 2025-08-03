<?php

require_once("../classes/DbConnector.php");
//var_dump($_POST);
$db = DbConector::singleton();


if (isset($_GET["id"])) {
    $charId = $_GET["id"];
    $charData = $db->getChar($charId);
    $spellsIds = str_replace('"', '', $db->getSpellsIds($charId));
    $charSpells = $db->getSpells($spellsIds, "yes");
}

if (isset($charSpells) && $charSpells != null) {
    foreach ($charSpells as $spell) {
        $grouped[$spell['level']][] = $spell;
    }
} else {
    $grouped = null;
}

?>

<link rel="stylesheet" href="../styles/index.css">
<link rel="stylesheet" href="../styles/char.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script src="../scripts/char.js"></script>
<div class="mist"></div>
<div class="contenedor-linterna" id="contenedor-linterna">
    <div id="gancho"></div>
    <div class="linterna" id="linterna"></div>
    <div class="haz-de-luz" id="haz-de-luz"></div>
</div>

<div id="charDiv">
    <!-- //? LEFT CONTAINER  -->
    <div>
        <div class="card">
            <img src="../resources/chars/<?= $charData["name"] ?>/<?= $charData["full_body_image_path"] ?>" id="fullBodyImg" alt="" class="personaje">
        </div>
        <div id="sheetButtons">
            <button id="showPdfButton">Ver Ficha</button>
            <button id="showPdfButton">Actualizar Ficha</button>
        </div>
        <div id="stContainer">
            <h3>Tiradas de salvación</h3>
            <div class="stDiv">
                <span class="stValue" id="ST-Strength">+2</span>
                <span class="stInfo">Fuerza</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Dexterity">+2</span>
                <span class="stInfo">Destreza</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Constitution">+2</span>
                <span class="stInfo">Constitucion</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Intelligence">+2</span>
                <span class="stInfo">Inteligencia</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Wisdom">+2</span>
                <span class="stInfo">Sabiduria</span>
            </div>
            <div class="stDiv">
                <span class="stValue" id="ST-Charisma">+2</span>
                <span class="stInfo">Carisma</span>
            </div>
        </div>
    </div>

    <!-- //? MAIN CONTENT -->
    <div class="charInfo">
        <div id="topContent">
            <div>
                <div class="headerDisplay">
                    <div>
                        <h1><?= $charData["name"] ?></h1>
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
                        <span id="Passive" class="highlightable"></span>
                        <span id="ppasInfo">Percepción Pasiva</span>
                    </div>
                    <div id="profContainer">
                        <span id="ProfBonus" class="highlightable"></span>
                        <span id="profInfo">Bono de competencia</span>
                    </div>
                </div>
            </div>

            <div id="skillsDiv">
                <h2>Habilidades</h2>
                <div id="skillsContainer">
                    <div class="skillsContainerCol">
                        <div class="skillDiv">
                            <span class="stValue" id="Acrobatics">+2</span>
                            <span class="stInfo">Acrobacias</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Athletics">+2</span>
                            <span class="stInfo">Atletismo</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Arcana">+2</span>
                            <span class="stInfo">C. Arcano</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Deception"></span>
                            <span class="stInfo">Engaño</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="History"></span>
                            <span class="stInfo">Historia</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Performance"></span>
                            <span class="stInfo">Interpretacion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Intimidation"></span>
                            <span class="stInfo">Intimidacion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Investigation"></span>
                            <span class="stInfo">Investigacion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="SleightofHand"></span>
                            <span class="stInfo">Juego de manos</span>
                        </div>
                    </div>

                    <div class="skillsContainerCol">
                        <div class="skillDiv">
                            <span class="stValue" id="Medicine"></span>
                            <span class="stInfo">Medicina</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Nature"></span>
                            <span class="stInfo">Naturaleza</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Perception"></span>
                            <span class="stInfo">Percepcion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Insight"></span>
                            <span class="stInfo">Perspicacia</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Persuasion"></span>
                            <span class="stInfo">Persuasion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Religion"></span>
                            <span class="stInfo">Religion</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Stealth"></span>
                            <span class="stInfo">Sigilo</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Survival"></span>
                            <span class="stInfo">Supervivencia</span>
                        </div>
                        <div class="skillDiv">
                            <span class="stValue" id="Animal">+2</span>
                            <span class="stInfo">Trato Animal</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="tabsSelector">
            <h2 class="selected tabsSelectorH2" for="spellsTab">Conjuros</h2>
            <span class="h2separator">/</span>
            <h2 class="tabsSelectorH2" for="notesTab">Anotaciones</h2>
        </div>

        <div class="tabContainer" id="spellsTab">
            <div class="tabs" id="tabs">
                <?php if ($grouped) { ?>
                    <?php foreach ($grouped as $level => $spells): ?>
                        <div class="tab" data-tab="level<?= $level ?>">
                            <?php //$level == 0 ? 'Trucos' : 'Nivel ' . $level 
                            ?>
                            <?= $level ?>
                        </div>
                    <?php endforeach; ?>
                <?php } ?>
                <a href="allSpells.php?id_char=<?= $charId ?>" class="tab" title="Añadir Conjuro">+</a>
    
            </div>
            <?php if ($grouped) { ?>
                <div id="spellListContainer">
                    <?php foreach ($grouped as $key => $group) { ?>
                        <div class="spellList" id="level-<?= $key ?>">
    
                            <?php foreach ($group as $keySpell => $spell) { ?>
                                <?php //var_dump($spell)  
                                ?>
                                <span class="spellsInfo spellSpan" data-idSpell="id_spell=<?= $spell["id_spell"] ?>"> <?= substr($spell["name"], 0, strpos($spell["name"], "(")) ?> - <?= $spell["casteo"] ?></span>
                            <?php } ?>
    
                        </div>
                        <div class="spellCounter" id="spellCounter-<?= $key ?>">
                            <h3>Gastado</h3>
                            <h5><?= $key ?></h5>
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
        <div class="tabContainer" id="notesTab">
            <iframe src="notes.php?framed=true" frameborder="0" style="width: 100%;">
                
            </iframe>
        </div>
    </div>
</div>
<div id="embedContainer">
    <div id="embedTopBar">
        <span id="closeEmbed">X</span>
    </div>
    <embed id="embed"
        src="../resources/chars/<?= $charData["name"] ?>/ficha.pdf"
        type="application/pdf"
        width="100%"
        height="100%"
        title="Embedded PDF Viewer" />
</div>

<div id="embededSpellContainer">
    <div id="embedSpellTopBar">
        <span id="closeSpellEmbed">X</span>
    </div>
    <iframe src="" id="spellIframe" frameborder="0"></iframe>
</div>

<script>
    setPdfFields("../resources/chars//<?= $charData["name"] ?>/ficha.pdf");

    const card = document.querySelector('.card');
    const personaje = document.querySelector('.personaje');

    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left; // posición X dentro del contenedor
        const y = e.clientY - rect.top; // posición Y dentro del contenedor

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

    });

    document.getElementById("gancho").addEventListener('click', function() {
        document.getElementById("contenedor-linterna").classList.add("fallen-contenedor-linterna")
    })

    function showEmbededSpell(spellId) {
        var embedSpellContainer = document.getElementById("embededSpellContainer");

        var spellEmbedDiv = document.getElementById("embededSpellContainer");

        spellEmbedDiv.querySelector("#spellIframe").src = "../sections/_partials/embededSpell.php?" + spellId;
        spellEmbedDiv.style.display = "block";
    }

    document.getElementById("closeSpellEmbed").addEventListener('click', function() {
        document.getElementById("closeSpellEmbed").parentElement.parentElement.style.display = "none";
    })
</script>