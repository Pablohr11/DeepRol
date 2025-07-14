<?php

//require_once __DIR__ . '/vendor/autoload.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeepRol</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/leftColumn.css">
    <link rel="stylesheet" href="styles/footer.css">
    <script defer src="scripts/header.js"></script>
    <script defer src="scripts/index.js"></script>
</head>
<body>
    <?php 
        include("sections/_partials/header.php")
    ?>
    <div id="content">
        <div id="leftBar" class="dark open">
            <?php 
                include("sections/_partials/leftColumn.php")
            ?>
        </div>
        <div id="mainContent" class="dark">
            <iframe src="" frameborder="0" name="mainIframe" id="mainIframe"></iframe>            
        </div>
    </div>
    <div id="footer">
        <?php 
            include("sections/_partials/footer.php")
        ?>
    </div>

<script defer>
    function hideMain() {
        var divPartidas = document.querySelector("#partidas").style.display="none";
        var divPersonajes = document.querySelector("#personajes").style.display="none";
        var divHabilidades = document.querySelector("#habilidades").style.display="none";
        var divApuntes = document.querySelector("#apuntes").style.display="none";
        // console.log(divPartidas);

        // document.getElementById("Partidas").addEventListener('click', function() {

        // })
    }

    function changeMain(id) {
        // hideMain();

        var partidasSRC = "sections/personajes.php";
        var personajesSRC = "sections/personajes.php";
        var habilidadesSRC = "sections/allSpells.php";
        var apuntesSRC = "sections/notes.php";
        
        var targetContainerId = id.toLowerCase();
        console.log(id);

        switch (id) {
            case "Partidas":
                document.getElementById("mainIframe").src = partidasSRC;        
                break;
            case "Personajes":
                document.getElementById("mainIframe").src = personajesSRC;        
                break;
            case "Habilidades":
                document.getElementById("mainIframe").src = habilidadesSRC;        
                break;
            case "Apuntes":
                document.getElementById("mainIframe").src = apuntesSRC;        
                break;
            default:
                break;
        }
        // document.getElementById("mainIframe").src = personajesSRC;

        console.log(document.getElementById(targetContainerId));
        // document.getElementById(targetContainerId).style.display = "block";
    }
</script>
<div style="position: fixed; inset: 0; z-index: -1; overflow: hidden;">
  <svg width="100%" height="100%" viewBox="0 0 1440 900" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" xmlns:xlink="http://www.w3.org/1999/xlink">
  <defs>
    <!-- Gradiente ámbar -->
    <linearGradient id="ondaAmber" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" style="stop-color:#e2a349;stop-opacity:0.06" />
      <stop offset="100%" style="stop-color:#ffcc70;stop-opacity:0.04" />
    </linearGradient>

    <!-- Niebla: patrón de círculos difuminados -->
    <radialGradient id="fogCircle" cx="50%" cy="50%" r="50%">
      <stop offset="0%" style="stop-color:#e2a349;stop-opacity:0.03" />
      <stop offset="100%" style="stop-color:#000000;stop-opacity:0" />
    </radialGradient>
  </defs>

  <!-- Ondas -->
  <path fill="url(#ondaAmber)">
    <animate attributeName="d" dur="12s" repeatCount="indefinite"
      values="
        M0,320 Q360,420 720,320 T1440,330 V900 H0 Z;
        M0,340 Q360,300 720,340 T1440,350 V900 H0 Z;
        M0,320 Q360,420 720,320 T1440,330 V900 H0 Z" />
  </path>

  <path fill="url(#ondaAmber)">
    <animate attributeName="d" dur="18s" repeatCount="indefinite"
      values="
        M0,480 Q360,580 720,460 T1440,480 V900 H0 Z;
        M0,500 Q360,450 720,500 T1440,490 V900 H0 Z;
        M0,480 Q360,580 720,460 T1440,480 V900 H0 Z" />
  </path>

  <!-- Niebla animada -->
  <g>
    <circle cx="200" cy="300" r="250" fill="url(#fogCircle)">
      <animateTransform attributeName="transform" type="translate"
        values="0,0; 20,-10; 0,0" dur="15s" repeatCount="indefinite"/>
    </circle>
    <circle cx="800" cy="500" r="300" fill="url(#fogCircle)">
      <animateTransform attributeName="transform" type="translate"
        values="0,0; -30,20; 0,0" dur="20s" repeatCount="indefinite"/>
    </circle>
    <circle cx="1200" cy="250" r="200" fill="url(#fogCircle)">
      <animateTransform attributeName="transform" type="translate"
        values="0,0; 15,-25; 0,0" dur="22s" repeatCount="indefinite"/>
    </circle>
  </g>
</svg>

</div>
</body>
</html>