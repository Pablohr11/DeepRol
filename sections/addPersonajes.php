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
	<link rel="stylesheet" href="../styles/form.css">
</head>
<body>
    <div id="bodyContainer" class="flexCenter">
        <div id="formContainer">
            <form action="" method="post" enctype="multipart/form-data" >
                <div id="formLogoContainer" class="flexCenter">
                    <img id="formLogo" src="../resources/imgs/logo.png" alt="">
                </div>
                <div id="formFieldsContainer">
                    <div class="formInputContainer">
                        <fieldset>
                            <legend>Nombre del Personaje</legend>
                            <input type="text" id="formName" class="formTextField"  name="nombrePersonaje" value="<?php if(isset($_POST["nombrePersonaje"])){ echo $_POST["nombrePersonaje"]; } ?>">
                        </fieldset>
                        <?php if(isset($errores['nombrePersonaje'])){ ?>
                            <p class='errorMessage'> <?=$errores['nombrePersonaje']?></p> 
                        <?php } ?>
                    </div>

                    <div class="formInputContainer">
                        <fieldset>
                            <legend>Raza del Personaje</legend>
                            <input type="text" id="formName" class="formTextField"  name="razaPersonaje" value="<?php if(isset($_POST["razaPersonaje"])){ echo $_POST["razaPersonaje"]; } ?>">
                        </fieldset>
                        <?php if(isset($errores['razaPersonaje'])){ ?>
                            <p class='errorMessage'> <?=$errores['razaPersonaje']?></p>
                        <?php } ?>
                    </div>

                    <div class="formInputContainer fileInputContainer">
                        <fieldset>
                            <legend>Imagen de perfil del Personaje</legend>
                            <input type="file" id="formName" class="formTextField"  name="imagenPerfilPersonaje" placeholder="Imagen del Personaje" value>
                        </fieldset>
                        <?php if(isset($errores["imagenPerfilPersonaje"])){ ?>
                            <p class='errorMessage'> <?=$errores['imagenPerfilPersonaje']?></p>
                        <?php } ?>
                    </div>

                    <div class="formInputContainer fileInputContainer">
                        <fieldset>
                            <legend>Imagen completa del Personaje</legend>
                            <input type="file" id="formName" class="formTextField"  name="imagenCompletaPersonaje" placeholder="Imagen Completa del Personaje">
                        </fieldset>
                        <?php if(isset($errores["imagenCompletaPersonaje"])){ ?>
                            <p class='errorMessage'> <?=$errores['imagenCompletaPersonaje']?></p>
                        <?php } ?>
                    </div>

                    <div class="formInputContainer fileInputContainer inputFicha">
                        <fieldset>
                            <legend>Ficha del Personaje</legend>
                            <input type="file" id="formName" class="formTextField"  name="fichaPersonaje" placeholder="Ficha del Personaje">
                        </fieldset>
                        <?php if(isset($errores["fichaPersonaje"])){ ?>
                            <p class='errorMessage'> <?=$errores['fichaPersonaje']?></p>
                        <?php } ?>
                    </div>
                </div>
                <div class="formSubmitContainer">
                    <input type="submit" value="Darle Vida" id="submitInput" name="submitInput">
                </div>
            </form>
        </div>
    </div>
</body>
</html>