<?php

	require_once("../classes/DbConnector.php");
	$db = DbConector::singleton();
    if (!$_COOKIE["logged"]) {
        header("location: login.php");
    } else {
        $idUser = $_COOKIE["logged"];
    }


    if(isset($_POST["submitInput"])){
        $errores = [];

        $targetDir = "../resources/chars/".$_POST["nombrePersonaje"]."/";
        $targetSmallImageFile = $targetDir . "imagenPequeña".getFileExtension(basename($_FILES["imagenPerfilPersonaje"]["name"]));
        $targetBigImageFile = $targetDir . "imagenGeneral".getFileExtension(basename($_FILES["imagenPerfilPersonaje"]["name"]));
        $targetPdfFile = $targetDir . "ficha.pdf";
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
            // print_r($_POST);
            $db->createChar(
                $idUser,
                $_POST["nombrePersonaje"],
                $_POST["razaPersonaje"],
                "imagenPequeña".getFileExtension(basename($_FILES["imagenPerfilPersonaje"]["name"])),
                "imagenGeneral".getFileExtension(basename($_FILES["imagenPerfilPersonaje"]["name"]))
            );
            mkdir("../resources/chars/".$_POST["nombrePersonaje"]);
            move_uploaded_file($_FILES["imagenPerfilPersonaje"]["tmp_name"], $targetSmallImageFile);
            move_uploaded_file($_FILES["imagenCompletaPersonaje"]["tmp_name"], $targetBigImageFile);
            move_uploaded_file($_FILES["fichaPersonaje"]["tmp_name"], $targetPdfFile);
        }
    }

    function getFileExtension($fileName) {
        $extensionDot = strpos($fileName ,".");
        $extension = substr($fileName, $extensionDot);
        return $extension;
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
    <script src="https://kit.fontawesome.com/e0b95331d1.js" crossorigin="anonymous"></script>
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
                        <img src="" alt="" id="formImage">
                    </div>
                    <div class="formSecondDiv">
                        <div id="nameRaceContainer">
                            <i class="fa-regular fa-user fa-xl"></i><h2 id="nameField">Nombre</h2><h2> / </h2><h2 id="raceField">Raza</h2>
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
                                <div class="subtitleContainer"><i class="fa-regular fa-image fa-xl"></i><h3>Imagen de perfil de personaje</h3></div>
                                <div class="formInputContainer fileInputContainer">
                                    <?php if(isset($errores["imagenPerfilPersonaje"])){ ?>
                                        <p class='errorMessage'> <?=$errores['imagenPerfilPersonaje']?></p>
                                    <?php } ?>
                                    <label  for="formSmallImage" class="custom-file-upload">Seleccionar imagen de perfil</label>
                                    <input type="file" id="formSmallImage" class="formTextField"  name="imagenPerfilPersonaje" placeholder="Imagen del Personaje" value>
                                </div>
                            </div>

                            <div id="step3" class="charStep">
                                <div class="subtitleContainer"><i class="fa-regular fa-image fa-xl"></i><h3>Imagen de general de personaje</h3></div>
                                <div class="formInputContainer fileInputContainer">
                                    <?php if(isset($errores["imagenCompletaPersonaje"])){ ?>
                                        <p class='errorMessage'> <?=$errores['imagenCompletaPersonaje']?></p>
                                    <?php } ?>
                                    <label  for="formBodyImage" class="custom-file-upload">Seleccionar imagen general</label>
                                    <input type="file"  id="formBodyImage" class="formTextField"  name="imagenCompletaPersonaje" placeholder="Imagen Completa del Personaje">
                                </div>
                            </div>
                            <div id="step4" class="charStep">
                                <div class="subtitleContainer"><i class="fa-regular fa-file fa-xl"></i><h3>Ficha de personaje</h3></div>
                                <div class="formInputContainer fileInputContainer inputFicha">
                                    <?php if(isset($errores["fichaPersonaje"])){ ?>
                                        <p class='errorMessage'> <?=$errores['fichaPersonaje']?></p>
                                    <?php } ?>
                                    <label for="formPdf" class="custom-file-upload">Seleccionar ficha del Personaje</label>
                                    <input type="file" id="formPdf" class="formTextField"  name="fichaPersonaje" placeholder="Ficha del Personaje">
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
    <script>
        const input = document.getElementById('formSmallImage');
        const input2 = document.getElementById('formBodyImage');
        const preview = document.getElementById('formImage');

        input.addEventListener('change', () => {
        const file = input.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.parentElement.classList.add("charImgClean");
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            alert('Please select a valid image file.');
        }
        });

        input2.addEventListener('change', () => {
        const file = input2.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.parentElement.classList.add("charImgClean");
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            alert('Please select a valid image file.');
        }
        });
    </script>
</body>
</html>