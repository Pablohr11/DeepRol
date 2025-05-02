<?php

	require_once("../classes/DbConnector.php");
	$db = DbConector::singleton();

    if(isset($_POST["submitInput"])){
        $errores = [];

        if(!isset($_POST["nombrePersonaje"]) || trim($_POST["nombrePersonaje"]) == ""){
            $errores['nombre'] = "El nombre no puede estar vacio";
        }else{
            $nombre = $_POST["nombrePersonaje"];
        }

        if(!isset($_POST["razaPersonaje"]) || trim($_POST["razaPersonaje"]) == ""){
            $errores['raza'] = "La raza no puede estar vacia";
        }else{
            $raza = $_POST["razaPersonaje"];
        }

        if(!isset($_POST["imagenPerfilPersonaje"])){
            $errores['imagenPerfil'] = "La imagen de perfil no puede estar vacia";
        }else{
            $imagenPerfil = $_POST["imagenPerfilPersonaje"];
        }

        if(!isset($_POST["imagenCompletaPersonaje"])){
            $errores['imagenCompleta'] = "La imagen completa del personaje no puede estar vacia";
        }else{
            $imagenCompleta = $_POST["imagenCompletaPersonaje"];
        }

        if(!isset($_POST["fichaPersonaje"])){
            $errores['fichaPersonaje'] = "La ficha no puede estar vacia";
        }else{
            $ficha = $_POST["fichaPersonaje"];
        }

        if(empty($errores)){
            echo "subiendo personaje";
            print_r($_POST);
            print_r($_FILES);
        }else{
            echo "<script>alert('Error: " . implode(", ", $errores) . "');</script>";
            print_r($_POST);
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
            <form action="" method="post">
                <div id="formLogoContainer" class="flexCenter">
                    <img id="formLogo" src="../resources/imgs/logo.png" alt="">
                </div>
                <div id="formFieldsContainer">
                    <div class="formInputContainer">
                        <input type="text" id="formName" class="formTextField"  name="nombrePersonaje" placeholder="Nombre del Personaje">
                    </div>

                    <div class="formInputContainer">
                        <input type="text" id="formName" class="formTextField"  name="razaPersonaje" placeholder="Raza del Personaje">
                    </div>

                    <div class="formInputContainer fileInputContainer">
					    <span>Imagen de perfil del Personaje</span>
                        <input type="file" id="formName" class="formTextField"  name="imagenPerfilPersonaje" placeholder="Imagen del Personaje">
                    </div>

                    <div class="formInputContainer fileInputContainer">
                        <span>Imagen completa del Personaje</span>
                        <input type="file" id="formName" class="formTextField"  name="imagenCompletaPersonaje" placeholder="Imagen Completa del Personaje">
                    </div>

                    <div class="formInputContainer fileInputContainer inputFicha">
                        <span>Ficha del Personaje</span>
                        <input type="file" id="formName" class="formTextField"  name="fichaPersonaje" placeholder="Ficha del Personaje">
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