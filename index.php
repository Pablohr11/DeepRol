<?php
    $isAuthenticated = isset($_COOKIE["logged"]) && (int) $_COOKIE["logged"] > 0;
    $hasGuestAccess = isset($_COOKIE["guestAccess"]) && $_COOKIE["guestAccess"] === "1";

    if (!$isAuthenticated && isset($_GET["guest"]) && $_GET["guest"] === "1") {
        $isSecureRequest = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";

        setcookie("guestAccess", "1", [
            "path" => "/",
            "secure" => $isSecureRequest,
            "httponly" => true,
            "samesite" => "Lax",
        ]);

        header("Location: index.php");
        exit;
    }

    if (!$isAuthenticated && !$hasGuestAccess) {
        require __DIR__ . "/sections/landing.php";
        exit;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#080712">
    <title>DeepRol · Tu mesa de aventuras</title>
    <script src="scripts/theme.js"></script>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/leftColumn.css">
    <link rel="stylesheet" href="styles/theme.css" data-deeprol-theme>
    <script defer src="scripts/header.js"></script>
    <script defer src="scripts/index.js"></script>
</head>
<body class="appRoot">
    <aside id="leftBar" class="open" aria-label="Navegación principal">
        <?php include("sections/_partials/leftColumn.php"); ?>
    </aside>

    <div id="appShell">
        <?php include("sections/_partials/header.php"); ?>

        <main id="mainContent">
            <iframe
                src="sections/home.php"
                title="Contenido de DeepRol"
                name="mainIframe"
                id="mainIframe"
            ></iframe>
        </main>
    </div>
</body>
</html>
