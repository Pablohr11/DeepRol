<?php

	require_once("../classes/DbConnector.php");
	$db = DbConector::singleton();

    if(isset($_POST["submitInput"])){
        $errores = [];

        if(!isset($_POST["nombrePersonaje"]) || trim($_POST["nombrePersonaje"]) == ""){
            $errores['nombrePersonaje'] = "El nombre no puede estar vacio";
        }

        if(!isset($_POST["razaPersonaje"]) || trim($_POST["razaPersonaje"]) == ""){
            $errores['razaPersonaje'] = "La raza no puede estar vacia";
        }

        if(!isset($_FILES["imagenPerfilPersonaje"]) || $_FILES["imagenPerfilPersonaje"]["error"] != 0){
            $errores['imagenPerfilPersonaje'] = "La imagen de perfil no puede estar vacia";
        }else if($_FILES["imagenPerfilPersonaje"]["type"] != "image/png" &&
                $_FILES["imagenPerfilPersonaje"]["type"] != "image/jpeg" &&
                $_FILES["imagenPerfilPersonaje"]["type"] != "image/jpg"){
                $errores['imagenPerfilPersonaje'] = "La imagen de perfil debe ser un PNG, un JPEG o un JPG";
        }
        

        if(!isset($_FILES["imagenCompletaPersonaje"]) || $_FILES["imagenCompletaPersonaje"]["error"] != 0){
            $errores['imagenCompletaPersonaje'] = "La imagen completa del personaje no puede estar vacia";
        }else if($_FILES["imagenCompletaPersonaje"]["type"] != "image/png" &&
                $_FILES["imagenCompletaPersonaje"]["type"] != "image/jpeg" &&
                $_FILES["imagenCompletaPersonaje"]["type"] != "image/jpg"){
                $errores['imagenCompletaPersonaje'] = "La imagen de perfil debe ser un PNG, un JPEG o un JPG";
        }

        if(!isset($_FILES["fichaPersonaje"]) || $_FILES["fichaPersonaje"]["error"] != 0){
            $errores['fichaPersonaje'] = "La ficha no puede estar vacia";
        }else if($_FILES["fichaPersonaje"]["type"] != "application/pdf"){
                $errores['fichaPersonaje'] = "La ficha debe ser un PDF";
        }

        if(empty($errores)){
            echo "subiendo personaje";
            print_r($_POST);
            print_r($_FILES);
        }
    }

?>
<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link rel="stylesheet" href="../styles/index.css">
	<link rel="stylesheet" href="../styles/form.css">
    <script defer src="../scripts/form.js"></script>

</head>
<body onload="initForm()">
    <div class="mist"></div>
    <div id="bodyContainer" class="flexCenter">
        <div id="formContainer">
            <form action="" method="post" enctype="multipart/form-data">
                <div id="formLogoContainer" class="flexCenter">
                    <h1>Nuevo Personaje</h1>
                </div>
                <div id="formFieldsContainer">
                    <div id="charImgContainer">
                        <!-- <img src="../resources/imgs/cave.jpg" alt=""> -->
                    </div>
                    <div class="formSecondDiv">
                        <div id="nameRaceContainer">
                            <h2 id="nameField">Nombre</h2><h2> / </h2><h2 id="raceField">Raza</h2>
                        </div>
                        <div class="steps">
                            <div id="step1" class="charStep">
                                <!-- <div id="nameRaceContainer">
                                    <h2 id="nameField">Nombre</h2><h2> / </h2><h2 id="raceField">Raza</h2>
                                </div> -->
                                <div class="formInputContainer">
                                    <?php if(isset($errores['nombrePersonaje'])){ ?>
                                        <p class='errorMessage'> <?=$errores['nombrePersonaje']?></p> 
                                    <?php } ?>
                                    <span>Nombre del Personaje</span>
                                    <input type="text" id="formName" class="formTextField"  name="nombrePersonaje" value="<?php if(isset($_POST["nombrePersonaje"])){ echo $_POST["nombrePersonaje"]; } ?>">
                                </div>
                                    
                                <div class="formInputContainer">
                                    <?php if(isset($errores['razaPersonaje'])){ ?>
                                        <p class='errorMessage'> <?=$errores['razaPersonaje']?></p>
                                    <?php } ?>
                                    <span>Raza del Personaje</span>
                                    <input type="text" id="formRace" class="formTextField"  name="razaPersonaje" value="<?php if(isset($_POST["razaPersonaje"])){ echo $_POST["razaPersonaje"]; } ?>">
                                </div>
                                    
                            </div>
                            <div id="step2" class="charStep">
                                <div class="formInputContainer fileInputContainer">
                                    <?php if(isset($errores["imagenPerfilPersonaje"])){ ?>
                                        <p class='errorMessage'> <?=$errores['imagenPerfilPersonaje']?></p>
                                    <?php } ?>
                                    <span>Imagen de perfil del Personaje</span>
                                    <input type="file" id="formName" class="formTextField"  name="imagenPerfilPersonaje" placeholder="Imagen del Personaje" value>
                                </div>
                            </div>

                            <div id="step3" class="charStep">
                                <div class="formInputContainer fileInputContainer">
                                    <?php if(isset($errores["imagenCompletaPersonaje"])){ ?>
                                        <p class='errorMessage'> <?=$errores['imagenCompletaPersonaje']?></p>
                                    <?php } ?>
                                    <span>Imagen completa del Personaje</span>
                                    <input type="file" id="formName" class="formTextField"  name="imagenCompletaPersonaje" placeholder="Imagen Completa del Personaje">
                                </div>
                            </div>
                            <div id="step4" class="charStep">
                                <div class="formInputContainer fileInputContainer inputFicha">
                                    <?php if(isset($errores["fichaPersonaje"])){ ?>
                                        <p class='errorMessage'> <?=$errores['fichaPersonaje']?></p>
                                    <?php } ?>
                                    <span>Ficha del Personaje</span>
                                    <input type="file" id="formName" class="formTextField"  name="fichaPersonaje" placeholder="Ficha del Personaje">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="formSubmitContainer">
                    <span class="button" id="prevStep">ANTERIOR</span>
                    <span class="button" id="nextStep">SIGUIENTE</span>
                    <input type="submit" value="Crear Personaje" id="submitInput" name="submitInput">
                </div>
            </form>
        </div>
    </div>
</body>
</html>