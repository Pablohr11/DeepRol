<?php

	require_once("../classes/DbConnector.php");
	$db = DbConector::singleton();
    if (!$_COOKIE["logged"]) {
        header("location: login.php");
    } else {
        $idUser = $_COOKIE["logged"];
    }
    $razas = $db->getRazas();
    $razaSeleccionada = null;
    $clases = $db->getClasses();
    $claseSeleccionada = null;
    // echo("<pre><div id='helper'");
    // htmlspecialchars(print_r($classes), ENT_QUOTES, 'UTF-8');
    // echo("</div></pre>");

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

<script>
    var clases = <?=json_encode($clases)?>;
    var razas = <?=json_encode($razas)?>;
    console.log(clases);
</script>

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

                    <div class="charStep">
                        <!-- //? NOMBRE -->
                        <div class="formInputContainer">
                            <?php if(isset($errores['nombrePersonaje'])){ ?>
                                <p class='errorMessage'> <?=$errores['nombrePersonaje']?></p> 
                            <?php } ?>
                            <h5>Nombre del Personaje</h5>
                            <input type="text" id="formName" class="formTextField"  name="nombrePersonaje" value="<?php if(isset($_POST["nombrePersonaje"])){ echo $_POST["nombrePersonaje"]; } ?>">
                        </div>
                        <!-- //? Imagen Perfil -->
                        <div class="inputFileContainer">
                            
                            <div class="subtitleContainer"><i class="fa-regular fa-image fa-xl"></i><h5>Imagen de perfil de personaje</h5></div>
                                <div class="formInputContainer fileInputContainer">
                                <?php if(isset($errores["imagenPerfilPersonaje"])){ ?>
                                    <p class='errorMessage'> <?=$errores['imagenPerfilPersonaje']?></p>
                                <?php } ?>
                                <label  for="formSmallImage" class="custom-file-upload">Seleccionar imagen de perfil</label>
                                <input type="file" id="formSmallImage" class="formTextField"  name="imagenPerfilPersonaje" placeholder="Imagen del Personaje" value>
                            </div>
                        </div>

                        <!-- //? Imagen Generica -->
                        <div class="inputFileContainer">

                            <div class="subtitleContainer"><i class="fa-regular fa-image fa-xl"></i><h5>Imagen de general de personaje</h5></div>
                            <div class="formInputContainer fileInputContainer">
                                <?php if(isset($errores["imagenCompletaPersonaje"])){ ?>
                                    <p class='errorMessage'> <?=$errores['imagenCompletaPersonaje']?></p>
                                <?php } ?>
                                <label  for="formBodyImage" class="custom-file-upload">Seleccionar imagen general</label>
                                <input type="file"  id="formBodyImage" class="formTextField"  name="imagenCompletaPersonaje" placeholder="Imagen Completa del Personaje">
                            </div>
                        </div>
                            
                    </div>
                    <div  class="charStep">
                        <div class="formInputContainer">

                            <h3>Clase</h3>
                            <div id="classesList" class="selecting">
                                
                            <?php foreach ($clases as $clase): ?>
                                <?php
                                    $checked = ($claseSeleccionada == $clase['id']) ? 'checked' : '';
                                    $selectedClass = ($claseSeleccionada == $clase['id']) ? 'selected' : '';
                                ?>
                                <label class="clase-option <?= $selectedClass ?>">
                                    <input type="radio" name="clase_id" value="<?= $clase['id'] ?>" <?= $checked ?> >
                                    <strong><?= htmlspecialchars($clase['Nombre']) ?></strong><br>
                                    <small><?= htmlspecialchars($clase['short_desc']) ?></small>
                                </label>
                            <?php endforeach; ?>
                            
                            </div>
                            <div id="classInfo">

                            </div>
                        </div>
                    </div>

                    <div  class="charStep">
                        <div class="formInputContainer">

                            <h3>Raza</h3>
                            <div id="razasList" class="selecting">
                                
                            <?php foreach ($razas as $raza): ?>
                                <?php
                                    $checked = ($razaSeleccionada == $raza['id']) ? 'checked' : '';
                                    $selectedClass = ($razaSeleccionada == $raza['id']) ? 'selected' : '';
                                ?>
                                <label class="raza-option <?= $selectedClass ?>">
                                    <input type="radio" name="raza_id" value="<?= $raza['id'] ?>" <?= $checked ?> >
                                    <strong><?= htmlspecialchars($raza['Nombre']) ?></strong><br>
                                    <small><?= htmlspecialchars($raza['descr']) ?></small>
                                </label>
                            <?php endforeach; ?>
                            
                            </div>
                            <div id="razasInfo">

                            </div>
                        </div>
                    </div>              
                </div>
                <div class="formSubmitContainer">
                    <!-- <span class="button" id="prevStep">ANTERIOR</span>
                    <span class="button" id="nextStep">SIGUIENTE</span> -->
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
    <script>
        

document.addEventListener("DOMContentLoaded", function() {
    const opciones = document.querySelectorAll('.clase-option');
    const classInfo = document.getElementById("classInfo");
    const classesList = document.getElementById("classesList");

    opciones.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');

        option.addEventListener('click', function(e) {
            // Previene que el label active el radio automáticamente
            e.preventDefault();

            if (option.classList.contains("selected")) {
                // Deselecciona
                option.classList.remove("selected");
                radio.checked = false;
                classInfo.innerHTML = "";
                classesList.classList.add("selecting");

                // Mostrar todas las opciones
                opciones.forEach(opt => opt.style.display = "block");
            } else {
                // Selecciona esta opción
                opciones.forEach(opt => {
                    opt.classList.remove("selected");
                    opt.style.display = "none";
                    opt.querySelector('input[type="radio"]').checked = false;
                });

                option.classList.add("selected");
                option.style.display = "block";
                radio.checked = true;
                classesList.classList.remove("selecting");

                // Ejemplo de mostrar información (ajustalo a tu lógica real)
                const nombreClase = option.querySelector("strong").innerText;
                const filtrado = clases.find(item => item[1] === nombreClase); // `clases` debe estar definido en JS

                if (filtrado) {
                    classInfo.innerHTML = filtrado.rasgos_clase;
                }
            }
        });
    });

    const opcionesR = document.querySelectorAll('.raza-option');
    const razasInfo = document.getElementById("razasInfo");
    const razasList = document.getElementById("razasList");

    opcionesR.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');

        option.addEventListener('click', function(e) {
            // Previene que el label active el radio automáticamente
            e.preventDefault();

            if (option.classList.contains("selected")) {
                // Deselecciona
                option.classList.remove("selected");
                radio.checked = false;
                razasInfo.innerHTML = "";
                razasList.classList.add("selecting");

                // Mostrar todas las opcionesR
                opcionesR.forEach(opt => opt.style.display = "block");
            } else {
                // Selecciona esta opción
                opcionesR.forEach(opt => {
                    opt.classList.remove("selected");
                    opt.style.display = "none";
                    opt.querySelector('input[type="radio"]').checked = false;
                });

                option.classList.add("selected");
                option.style.display = "block";
                radio.checked = true;
                classesList.classList.remove("selecting");

                // Ejemplo de mostrar información (ajustalo a tu lógica real)
                const nombreRaza = option.querySelector("strong").innerText;
                const filtrado = razas.find(item => item[1] === nombreRaza); // `clases` debe estar definido en JS

                if (filtrado) {
                    razasInfo.innerHTML = filtrado.rasgos_raza;
                }
            }
        });
    });
});



    </script>
</body>
</html>