<?php

	

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
	<link rel="stylesheet" type="text/css" href="styles/login.css">
    <script defer src="scripts/login.js"></script>
</head>
<body onload="init()">
    <?php 
        include("sections/_partials/header.php")
    ?>
	<div id="formContainer">
		<div class="loginOption">
			<form>
				<img id="formLogo" src="./resources/logos/logo_no_bg.png" alt="">
				<div class="formInputContainer">
					<span>Usuario</span>
					<input type="text" id="formName" class="formTextField"  name="user">
				</div>
				<div class="formInputContainer">
					<span>Contraseña</span>		
					<input type="text" id="formPwd" class="formTextField" name="password">		
				</div>
				<div class="formLinkContainer">
					<a href="#">Crear cuenta</a>
				</div>
				<div class="formSubmitContainer">
					<input type="submit" value="Iniciar Sesion">
				</div>
			</form>
		</div>
		<div class="loginDecorate"></div>
	</div>
    <div id="footer">
        <?php 
            include("sections/_partials/footer.php")
        ?>
    </div>
</body>
</html>