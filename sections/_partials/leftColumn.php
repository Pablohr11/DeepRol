<div id="leftColumnContent">
    <div>
        <div class="brand">
            <a href="/" class="brandLink" aria-label="DeepRol, inicio">
                <img class="brandFull" src="../../resources/logos/logo_no_bg.png" alt="DeepRol">
                <img class="brandMark" src="../../resources/logos/logo_sinText.png" alt="">
                <span class="brandVersion">V2</span>
            </a>
            <button id="toogleSideBarButton" type="button" aria-label="Contraer menú" aria-expanded="true">
                <span aria-hidden="true">‹</span>
            </button>
        </div>

        <nav class="primaryNav">
            <button class="navItem active" type="button" data-page="Home" data-src="sections/home.php">
                <span class="navIcon" aria-hidden="true">⌂</span>
                <span class="lcOption">Inicio</span>
            </button>
            <button class="navItem" type="button" data-page="Personajes" data-src="sections/personajes.php">
                <span class="navIcon" aria-hidden="true">♙</span>
                <span class="lcOption">Personajes</span>
            </button>
            <button class="navItem" type="button" data-page="Partidas" data-src="sections/partidas.php">
                <span class="navIcon" aria-hidden="true">◈</span>
                <span class="lcOption">Partidas</span>
            </button>
            <button class="navItem" type="button" data-page="Habilidades" data-src="sections/allSpells.php">
                <span class="navIcon" aria-hidden="true">✦</span>
                <span class="lcOption">Conjuros</span>
            </button>
            <button class="navItem" type="button" data-page="Bestiario" data-src="sections/bestiario.php">
                <span class="navIcon" aria-hidden="true">♞</span>
                <span class="lcOption">Bestiario</span>
            </button>
            <button class="navItem" type="button" data-page="Razas" data-src="sections/razas.php">
                <span class="navIcon" aria-hidden="true">◎</span>
                <span class="lcOption">Razas y linajes</span>
            </button>
            <button class="navItem" type="button" data-page="Clases" data-src="sections/clases.php">
                <span class="navIcon" aria-hidden="true">⚔</span>
                <span class="lcOption">Clases</span>
            </button>
            <button class="navItem" type="button" data-page="Apuntes" data-src="sections/notes.php">
                <span class="navIcon" aria-hidden="true">▤</span>
                <span class="lcOption">Apuntes</span>
            </button>
        </nav>
    </div>

    <div class="sidebarBottom">
        <button class="navItem" type="button" data-page="Ajustes" data-src="sections/test.php">
            <span class="navIcon" aria-hidden="true">⚙</span>
            <span class="lcOption">Ajustes</span>
        </button>

        <button class="diceWidget" id="diceRoller" type="button" aria-label="Tirar un dado de veinte caras">
            <span class="diceLabel">Tirar dados</span>
            <span class="d20"><strong id="diceResult">20</strong></span>
            <small id="diceHint">Haz clic para lanzar</small>
        </button>
    </div>
</div>
