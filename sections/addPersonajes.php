<?php

	require_once("../classes/DbConnector.php");
	require_once("../src/helper.php");
	$db = DbConector::singleton();
<<<<<<< Updated upstream
=======
    if (!$_COOKIE["logged"]) {
        header("location: login.php");
    } else {
        $idUser = $_COOKIE["logged"];
    }
    $razas = $db->getRazas();
    $razaSeleccionada = null;
    $clases = $db->getClasses();
    $claseSeleccionada = null;

    $razasJsonRaw = (array) json_decode(file_get_contents("../data/razas.json"));


    $razasJson = [];

    foreach ($razasJsonRaw as $item) {
        $key = array_key_first(get_object_vars($item)); // Enano, Elfo...
        $razasJson[$key] = $item; // conserva el contenedor: $map['Enano']->Enano...
    }

    // echo("<pre>");
    // var_dump($razasJson);
    // echo("</pre>");
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            echo "subiendo personaje";
            print_r($_POST);
            print_r($_FILES);
=======
=======
>>>>>>> Stashed changes
            
            print_r($_POST);
            print_r($razasJson[$_POST["razaPersonaje"]]);
            createFicha($_POST);
            // $db->createChar(
            //     $idUser,
            //     $_POST["nombrePersonaje"],
            //     $_POST["razaPersonaje"],
            //     "imagenPequeña".getFileExtension(basename($_FILES["imagenPerfilPersonaje"]["name"])),
            //     "imagenGeneral".getFileExtension(basename($_FILES["imagenPerfilPersonaje"]["name"]))
            // );
            // mkdir("../resources/chars/".$_POST["nombrePersonaje"]);
            // move_uploaded_file($_FILES["imagenPerfilPersonaje"]["tmp_name"], $targetSmallImageFile);
            // move_uploaded_file($_FILES["imagenCompletaPersonaje"]["tmp_name"], $targetBigImageFile);
            // move_uploaded_file($_FILES["fichaPersonaje"]["tmp_name"], $targetPdfFile);
        } else {
            print_r($errores);
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
        }
    }

?>
<<<<<<< Updated upstream
=======

<script>
    var clases = <?=json_encode($clases)?>;
    var razas = <?=json_encode($razas)?>;

    const razasJson = <?=json_encode($razasJson, JSON_UNESCAPED_UNICODE)?>;
    console.log("RAZAS: "+razasJson.Enano.subrazas[0].descripcion)

    console.log(<?=json_encode($razasJson, JSON_UNESCAPED_UNICODE)?>);

</script>

>>>>>>> Stashed changes
<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-32">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link rel="stylesheet" href="../styles/form.css">
<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
=======
>>>>>>> Stashed changes
	<link rel="stylesheet" href="../styles/addChar.css">
    <script defer src="../scripts/form.js"></script>
    <script src="https://kit.fontawesome.com/e0b95331d1.js" crossorigin="anonymous"></script>
>>>>>>> Stashed changes
</head>
<body>
    <div id="bodyContainer" class="flexCenter">
        <div id="formContainer">
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            <form action="" method="post" enctype="multipart/form-data" >
                <div id="formLogoContainer" class="flexCenter">
                    <img id="formLogo" src="../resources/imgs/logo.png" alt="">
=======
=======
>>>>>>> Stashed changes
            <form action="" method="post" enctype="multipart/form-data">
                <div id="formTitleContainer" class="flexCenter">
                    <h1>Nuevo Personaje</h1>
>>>>>>> Stashed changes
                </div>
                <div id="formFieldsContainer">
                    <div class="formInputContainer">
                        <fieldset>
                            <legend>Nombre del Personaje</legend>
                            <input type="text" id="formName" class="formTextField"  name="nombrePersonaje" value="<?php if(isset($_POST["nombrePersonaje"])){ echo $_POST["nombrePersonaje"]; } ?>">
<<<<<<< Updated upstream
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
=======
                        </div>
                        <!-- //? Imagen Perfil -->
                        <div class="inputFileContainer">
                            <div class="subtitleContainer"><i class="fa-regular fa-image fa-l"></i><h5>Imagen de perfil de personaje</h5></div>
                                <div class="formInputContainer fileInputContainer">
                                <?php if(isset($errores["imagenPerfilPersonaje"])){ ?>
                                    <p class='errorMessage'> <?=$errores['imagenPerfilPersonaje']?></p>
                                <?php } ?>
                                <label  for="formSmallImage" class="custom-file-upload">Seleccionar imagen de perfil</label>
                                <input type="file" id="formSmallImage" class="formTextField"  name="imagenPerfilPersonaje" placeholder="Imagen del Personaje" value>
                            </div>
                            <img id="formImage1" class="formImage" src="" alt="">
                        </div>

                        <!-- //? Imagen Generica -->
                        <div class="inputFileContainer">

                            <div class="subtitleContainer"><i class="fa-regular fa-image fa-l"></i><h5>Imagen de general de personaje</h5></div>
                            <div class="formInputContainer fileInputContainer">
                                <?php if(isset($errores["imagenCompletaPersonaje"])){ ?>
                                    <p class='errorMessage'> <?=$errores['imagenCompletaPersonaje']?></p>
                                <?php } ?>
                                <label  for="formBodyImage" class="custom-file-upload">Seleccionar imagen general</label>
                                <input type="file"  id="formBodyImage" class="formTextField"  name="imagenCompletaPersonaje" placeholder="Imagen Completa del Personaje">
                            </div>
                            <img id="formImage2" class="formImage" src="" alt="">
                        </div>

                        <!-- //? Imagen Generica -->
                        <div class="inputFileContainer">

                            <div class="subtitleContainer"><i class="fa-regular fa-image fa-l"></i><h5>Ficha de personaje</h5></div>
                            <div class="formInputContainer fileInputContainer">
                                <?php if(isset($errores["fichaPersonaje"])){ ?>
                                    <p class='errorMessage'> <?=$errores['imagenCompletaPersonaje']?></p>
                                <?php } ?>
                                <label  for="formFicha" class="custom-file-upload">Seleccionar ficha</label>
                                <input type="file"  id="formFicha" class="formTextField"  name="fichaPersonaje" placeholder="Ficha del Personaje">
                            </div>
                        </div>
                            
                    </div>
                    
                    <div id="lowerCharStepContainer">
                        <div  class="charStep razaStep">
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
                                <div id="razasInfo"></div>
                                    
                            </div>
                            <div id="subRazasStep">
                                 <div class="formInputContainer">

                                <h3>Subraza</h3>
                                <div id="subRazasList" class="selecting">
                                                  
                                </div>
                                <div id="subrazasInfo"></div>
                                    
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
                        
                    </div>  
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                </div>
                <input type="hidden" id="razaPersonaje" name="razaPersonaje">
                <div class="formSubmitContainer">
                    <input type="submit" value="Darle Vida" id="submitInput" name="submitInput">
                </div>
            </form>
        </div>
    </div>
<<<<<<< Updated upstream
=======
    <script>
        const input = document.getElementById('formSmallImage');
        const input2 = document.getElementById('formBodyImage');
        const preview = document.getElementById('formImage1');
        const preview2 = document.getElementById('formImage2');

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
            alert('Please select a valid image file. '+file.type);
        }
        });

        input2.addEventListener('change', () => {
        const file = input2.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview2.src = e.target.result;
                preview2.parentElement.classList.add("charImgClean");
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            alert('Please select a valid image file. '+file.type);
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
    const subrazasList = document.getElementById("subRazasList");
    const subrazasInfo = document.getElementById("subrazasInfo");

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
                
                console.log(option.querySelector("strong").innerText)
                document.getElementById("razaPersonaje").value=option.querySelector("strong").innerText;
                option.style.display = "block";
                radio.checked = true;
                classesList.classList.remove("selecting");

                // Ejemplo de mostrar información (ajustalo a tu lógica real)
                const nombreRaza = option.querySelector("strong").innerText;
                const filtrado = razas.find(item => item[1] === nombreRaza); // `clases` debe estar definido en JS
                console.log("FILTRADO: "+filtrado.Nombre)
                if (filtrado) {
                    razasInfo.innerHTML = filtrado.rasgos_raza;
                    
                    subrazas = razasJson[filtrado.Nombre].subrazas;

                    subrazasList.innerHTML = "";

                    subrazas.forEach(subraza => {
                    // const checked = subraza.nombre === seleccionada ? 'checked' : '';
                    // const selectedClass = checked ? 'selected' : '';

                        subrazasList.innerHTML += `
                            <label class="subraza-option">
                                <input type="radio" name="subraza_id" value="${subraza.nombre}">
                                <strong>${subraza.nombre}</strong><br>
                                <small>${subraza.descripcion}</small>
                            </label>
                        `;
                    });

                    var subrazasElements = document.querySelectorAll(".subraza-option");

                    subrazasElements.forEach(option => {
                        const radio = option.querySelector('input[type="radio"]');

                        option.addEventListener('click', function(e) {
                            // Previene que el label active el radio automáticamente
                            e.preventDefault();

                            if (option.classList.contains("selected")) {
                                // Deselecciona
                                option.classList.remove("selected");
                                radio.checked = false;
                                subrazasInfo.innerHTML = "";
                                razasList.classList.add("selecting");

                                // Mostrar todas las opcionesR
                                subrazasElements.forEach(opt => opt.style.display = "block");
                            } else {
                                // Selecciona esta opción
                                subrazasElements.forEach(opt => {
                                    opt.classList.remove("selected");
                                    opt.style.display = "none";
                                    opt.querySelector('input[type="radio"]').checked = false;
                                });

                                option.classList.add("selected");
                                
                                console.log(option.querySelector("strong").innerText)

                                var selectedSubRaza = option.querySelector("strong").innerText;

                                document.getElementById("razaPersonaje").value=selectedSubRaza;
                                option.style.display = "block";
                                radio.checked = true;
                                classesList.classList.remove("selecting");
                                console.log(razasJson[filtrado.Nombre].subrazas.find(item => item.nombre === selectedSubRaza));
                                
                                generateSubrazaInfo(razasJson[filtrado.Nombre].subrazas.find(item => item.nombre === selectedSubRaza))
                                // Ejemplo de mostrar información (ajustalo a tu lógica real)
                                // const nombreRaza = option.querySelector("strong").innerText;
                                // const filtrado = razas.find(item => item[1] === nombreRaza); // `clases` debe estar definido en JS
                                // console.log("FILTRADO: "+filtrado.Nombre)
                                // if (filtrado) {
                                //     razasInfo.innerHTML = filtrado.rasgos_raza;                                    
                                // }
                            }
                        });
                    });

                }
            }
        });
    });
});



    </script>

    <script>
        function generateSubrazaInfo(subraza) {

            
            const subrazasInfo = document.getElementById("subrazasInfo");

            var competenciasAdicionalesDiv = document.createElement("div");
            competenciasAdicionalesDiv.classList.add("caMainDiv")

            var mainh1 = document.createElement("h1");

            var catH2_1 = document.createElement("h2");
            var catH2_2 = document.createElement("h2");
            var catH2_3 = document.createElement("h2");
            var catH2_4 = document.createElement("h2");
            var catH2_5 = document.createElement("h2");

            var mainDesc = document.createElement("p");

            var iterableVar = document.createElement("a");

            mainh1.innerText = "Rasgos "+subraza.nombre;
            subrazasInfo.appendChild(mainh1);
            
            mainDesc.classList.add("subrazaDesc")
            mainDesc.innerText = subraza.descripcion;
            subrazasInfo.appendChild(mainDesc);

            
            iterableVar = document.createElement("div");
            iterableVar.classList.add("caDiv")
            iterableVar.innerHTML = "<label class='caTitle'>Armas</label>";
            if (subraza.competencias_adicionales.armas.length > 0) {
                subraza.competencias_adicionales.armas.forEach(element => {
                    iterableVar.innerHTML += "<label class='caData'>"+element+"</label>";
                })
            }  else {
                    iterableVar.innerHTML += "<label class='caData'>----</label>";
            }
            competenciasAdicionalesDiv.appendChild(iterableVar)

            iterableVar = document.createElement("div");
            iterableVar.classList.add("caDiv")
            iterableVar.innerHTML = "<label class='caTitle'>Armaduras</label>";
            if (subraza.competencias_adicionales.armaduras.length > 0) {
                subraza.competencias_adicionales.armaduras.forEach(element => {
                    iterableVar.innerHTML += "<label class='caData'>"+element+"</label>";
                })
            } else {
                    iterableVar.innerHTML += "<label class='caData'>----</label>";
            }
            competenciasAdicionalesDiv.appendChild(iterableVar)

            iterableVar = document.createElement("div");
            iterableVar.classList.add("caDiv")
            iterableVar.innerHTML = "<label class='caTitle'>Habilidades</label>";
            if (subraza.competencias_adicionales.habilidades.length > 0) {
                subraza.competencias_adicionales.habilidades.forEach(element => {
                    iterableVar.innerHTML += "<label class='caData'>"+element+"</label>";
                })
            } else {
                    iterableVar.innerHTML += "<label class='caData'>----</label>";
            }
            competenciasAdicionalesDiv.appendChild(iterableVar)

            iterableVar = document.createElement("div");
            iterableVar.classList.add("caDiv")
            iterableVar.innerHTML = "<label class='caTitle'>Herramientas</label>";
            if (subraza.competencias_adicionales.herramientas.length > 0) {
                subraza.competencias_adicionales.herramientas.forEach(element => {
                    iterableVar.innerHTML += "<label class='caData'>"+element+"</label>";
                })
            } else {
                iterableVar.innerHTML += "<label class='caData'>----</label>";
            }
            competenciasAdicionalesDiv.appendChild(iterableVar)
            
            subrazasInfo.appendChild(competenciasAdicionalesDiv)
        }
    </script>
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
</body>
</html>