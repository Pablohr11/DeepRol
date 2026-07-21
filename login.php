<?php
<<<<<<< Updated upstream
	require_once("classes/DbConnector.php");
	//var_dump($_POST);
	$db = DbConector::singleton();

	
	if (isset($_POST["submitInput"])) {	
		$checkLoginResult = $db->checkLogin(strtoupper($_POST["user"]), $_POST["password"]);
		if ($_POST["submitInput"] == "Iniciar Sesion") {
			if($checkLoginResult != 0) {
				setcookie("logged", $checkLoginResult, time()+60*60*24*30);
				setcookie("userInitial", $_POST["user"][0], time()+60*60*24*30);
				header("Location: /");
				die();
			}
		} else {
			echo("b");
		}
	}
=======
require_once __DIR__ . '/src/bootstrap.php';
$db = DbConector::singleton();
$error = null;
>>>>>>> Stashed changes

if (isset($_POST['submitInput'])) {
    $user = trim($_POST['user'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($user === '' || $password === '') {
        $error = 'Completa el usuario y la contraseña.';
    } elseif (str_starts_with((string) $_POST['submitInput'], 'Iniciar')) {
        $userId = $db->checkLogin(strtoupper($user), $password);
        if ($userId) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $userId;
            $_SESSION['user_initial'] = mb_strtoupper(mb_substr($user, 0, 1));
            header('Location: ' . url());
            exit;
        }
        $error = 'Usuario o contraseña incorrectos.';
    } else {
        try {
            $db->createUser($user, $password);
            $error = 'Cuenta creada. Ya puedes iniciar sesión.';
        } catch (PDOException $e) {
            $error = 'Ese nombre de usuario ya está en uso.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-32">
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
			<form action="" method="post">
				<?php if ($error): ?><p class="formMessage"><?= h($error) ?></p><?php endif; ?>
				<img id="formLogo" src="./resources/logos/logo_no_bg.png" alt="">
				<div class="formInputContainer">
					<span id="userSpan">Usuario</span>
					<input type="text" id="formName" class="formTextField" name="user" required autocomplete="username">
				</div>
				<div class="formInputContainer">
					<span>Contraseña</span>		
					<input type="text" id="formPwd" class="formTextField" name="password">		
				</div>
				<div class="formLinkContainer">
					<span id="changeFormButton">Crear cuenta</span>
				</div>
				<div class="formSubmitContainer">
					<input type="submit" value="Iniciar sesión" id="submitInput" name="submitInput">
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
