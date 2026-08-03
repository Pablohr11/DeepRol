<?php
    require_once __DIR__ . "/classes/DbConnector.php";

    $db = DbConector::singleton();
    $errorMessage = "";
    $requestedMode = $_POST["mode"] ?? $_GET["mode"] ?? "login";
    $formMode = $requestedMode === "register" ? "register" : "login";
    $submittedUser = trim($_POST["user"] ?? "");

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $password = $_POST["password"] ?? "";

        if ($submittedUser === "" || $password === "") {
            $errorMessage = "Completa el nombre de usuario y la contraseña.";
        } else {
            try {
                if ($formMode === "register") {
                    $created = $db->createUser($submittedUser, $password);
                    $checkLoginResult = $created
                        ? $db->checkLogin(strtoupper($submittedUser), $password)
                        : 0;
                } else {
                    $checkLoginResult = $db->checkLogin(strtoupper($submittedUser), $password);
                }

                if ((int) $checkLoginResult > 0) {
                    $cookieExpires = time() + 60 * 60 * 24 * 30;
                    $isSecureRequest = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
                    $cookieOptions = [
                        "expires" => $cookieExpires,
                        "path" => "/",
                        "secure" => $isSecureRequest,
                        "httponly" => true,
                        "samesite" => "Lax",
                    ];

                    setcookie("logged", (string) $checkLoginResult, $cookieOptions);
                    setcookie("userInitial", strtoupper(substr($submittedUser, 0, 1)), $cookieOptions);
                    header("Location: index.php");
                    exit;
                }

                $errorMessage = $formMode === "register"
                    ? "No se ha podido crear la cuenta. Prueba con otro nombre."
                    : "El usuario o la contraseña no son correctos.";
            } catch (Throwable $exception) {
                $errorMessage = $formMode === "register"
                    ? "Ese usuario ya existe o no se ha podido crear la cuenta."
                    : "No se ha podido iniciar sesión en este momento.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080711">
    <title>Acceso · DeepRol</title>
    <script src="scripts/theme.js"></script>
    <link rel="stylesheet" href="styles/login.css">
    <link rel="stylesheet" href="styles/theme.css" data-deeprol-theme>
    <script defer src="scripts/login.js"></script>
</head>
<body>
    <main class="authShell">
        <a class="authBrand" href="index.php" aria-label="Volver a DeepRol">
            <img src="resources/logos/logo_no_bg.png" alt="DeepRol">
        </a>
        <button
            class="appearanceModeButton authModeButton"
            type="button"
            data-color-mode-toggle
            title="Activar tema claro"
            aria-label="Activar tema claro"
            aria-pressed="false"
        >
            <span data-color-mode-icon aria-hidden="true">☀</span>
            <strong data-color-mode-label>Tema claro</strong>
        </button>

        <section class="authStory" aria-labelledby="storyTitle">
            <div class="storyContent">
                <p class="storyKicker"><span></span> Tu aventura continúa</p>
                <h1 id="storyTitle">Regresa a la mesa.<br><em>La historia te espera.</em></h1>
                <p>
                    Recupera tus personajes, tus apuntes y el grimorio completo
                    de tu campaña.
                </p>
            </div>

            <blockquote>
                “No son los dados quienes escriben la leyenda, sino las decisiones que tomas después.”
            </blockquote>
        </section>

        <section
            class="authPanel"
            data-initial-mode="<?= htmlspecialchars($formMode, ENT_QUOTES, "UTF-8") ?>"
            aria-labelledby="authTitle"
        >
            <div class="authRune" aria-hidden="true">✦</div>
            <p id="authEyebrow" class="authEyebrow">Bienvenido de nuevo</p>
            <h2 id="authTitle">Iniciar sesión</h2>
            <p id="authIntro" class="authIntro">Introduce tus credenciales para volver a tu campaña.</p>

            <?php if ($errorMessage !== ""): ?>
                <div class="formMessage" role="alert">
                    <span aria-hidden="true">!</span>
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <input id="formMode" type="hidden" name="mode" value="<?= htmlspecialchars($formMode, ENT_QUOTES, "UTF-8") ?>">

                <div class="formInputContainer">
                    <label id="userLabel" for="formName">Nombre de usuario</label>
                    <span class="fieldControl">
                        <i aria-hidden="true">♙</i>
                        <input
                            type="text"
                            id="formName"
                            name="user"
                            value="<?= htmlspecialchars($submittedUser, ENT_QUOTES, "UTF-8") ?>"
                            placeholder="Escribe tu nombre"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </span>
                </div>

                <div class="formInputContainer">
                    <label for="formPwd">Contraseña</label>
                    <span class="fieldControl">
                        <i aria-hidden="true">⌁</i>
                        <input
                            type="password"
                            id="formPwd"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button id="togglePassword" type="button" aria-label="Mostrar contraseña">◉</button>
                    </span>
                </div>

                <button id="submitInput" class="submitButton" type="submit">
                    <span>Entrar en DeepRol</span>
                    <i aria-hidden="true">→</i>
                </button>
            </form>

            <div class="modeSwitch">
                <span id="modePrompt">¿Aún no formas parte de la aventura?</span>
                <button id="changeFormButton" type="button">Crear una cuenta</button>
            </div>

            <div class="authDivider"><span>o explora primero</span></div>
            <a class="guestAccess" href="index.php?guest=1">Continuar como invitado</a>
        </section>
    </main>
</body>
</html>
