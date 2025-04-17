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
</head>
<body>
    <?php 
        include("sections/_partials/header.php")
    ?>
    <div id="content">
        <div id="leftBar" class="dark">
            <?php 
                include("sections/_partials/leftColumn.php")
            ?>
        </div>
        <div id="mainContent" class="dark">
            <iframe src="" frameborder="0" name="mainIframe" id="mainIframe"></iframe>
            <!-- <div id="partidas" class="mainContentScreen">
                <?php
                    // include("sections/partidas.php")
                ?>
            </div>
            <div id="personajes" class="mainContentScreen">
                <?php
                    // include("sections/personajes.php")
                ?>
            </div>
            <div id="habilidades" class="mainContentScreen">
                <?php
                    // include("sections/personajes.php")
                ?>
            </div>
            <div id="apuntes" class="mainContentScreen">
                <?php
                    // include("sections/personajes.php")
                ?>
            </div> -->
            
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

        var personajesSRC = "sections/personajes.php";
        var personajeSRC = "sections/personaje.php";
        
        var targetContainerId = id.toLowerCase();
        console.log(id);

        document.getElementById("mainIframe").src = personajesSRC;

        console.log(document.getElementById(targetContainerId));
        // document.getElementById(targetContainerId).style.display = "block";
    }
</script>

</body>
</html>