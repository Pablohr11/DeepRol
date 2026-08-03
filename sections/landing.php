<?php
    $landingAssetPrefix = basename($_SERVER["SCRIPT_NAME"] ?? "") === "landing.php" ? "../" : "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070610">
    <meta
        name="description"
        content="DeepRol reúne personajes, conjuros y apuntes para que toda tu campaña viva en un único lugar."
    >
    <title>DeepRol · Tu aventura empieza aquí</title>
    <script src="<?= $landingAssetPrefix ?>scripts/theme.js"></script>
    <link rel="stylesheet" href="<?= $landingAssetPrefix ?>styles/landing.css">
    <link rel="stylesheet" href="<?= $landingAssetPrefix ?>styles/theme.css" data-deeprol-theme>
</head>
<body>
    <div class="landingBackdrop" aria-hidden="true"></div>

    <header class="landingHeader">
        <a class="brand" href="<?= $landingAssetPrefix ?>index.php" aria-label="DeepRol, inicio">
            <img src="<?= $landingAssetPrefix ?>resources/logos/logo_no_bg.png" alt="">
        </a>

        <div class="landingHeaderActions">
            <button
                class="appearanceModeButton"
                type="button"
                data-color-mode-toggle
                title="Activar tema claro"
                aria-label="Activar tema claro"
                aria-pressed="false"
            >
                <span data-color-mode-icon aria-hidden="true">☀</span>
                <strong data-color-mode-label>Tema claro</strong>
            </button>
            <a class="headerLogin" href="<?= $landingAssetPrefix ?>login.php">
                <span>Ya tengo una cuenta</span>
                <strong>Iniciar sesión</strong>
            </a>
        </div>
    </header>

    <main class="landingMain">
        <section class="heroCopy" aria-labelledby="landingTitle">
            <p class="eyebrow"><span></span> Tu mesa digital de rol</p>
            <h1 id="landingTitle">
                Cada gran historia
                <em>empieza con una tirada.</em>
            </h1>
            <p class="heroText">
                Reúne a tu grupo, da vida a tus personajes y conserva cada
                conjuro, nota y aventura en un mismo lugar.
            </p>

            <div class="heroActions">
                <a class="primaryAction" href="<?= $landingAssetPrefix ?>index.php?guest=1">
                    Entrar como invitado
                    <span aria-hidden="true">→</span>
                </a>
                <a class="secondaryAction" href="<?= $landingAssetPrefix ?>login.php?mode=register">Crear una cuenta</a>
            </div>

            <p class="guestNote">
                Puedes explorar sin registrarte. Inicia sesión cuando quieras guardar tu propia campaña.
            </p>
        </section>

        <aside class="adventureCard" aria-label="Lo que encontrarás en DeepRol">
            <div class="cardRune" aria-hidden="true">✦</div>
            <p class="cardKicker">El compendio te espera</p>
            <h2>Todo tu mundo,<br>en un solo lugar.</h2>

            <ul>
                <li>
                    <span class="featureIcon purple" aria-hidden="true">♙</span>
                    <span><strong>Personajes</strong><small>Fichas y detalles siempre a mano</small></span>
                </li>
                <li>
                    <span class="featureIcon blue" aria-hidden="true">✧</span>
                    <span><strong>Conjuros</strong><small>Un grimorio completo y organizado</small></span>
                </li>
                <li>
                    <span class="featureIcon green" aria-hidden="true">▤</span>
                    <span><strong>Apuntes</strong><small>Cada secreto de la campaña</small></span>
                </li>
            </ul>

            <div class="cardFooter">
                <span class="partyAvatars" aria-hidden="true">
                    <i>D</i><i>R</i><i>20</i>
                </span>
                <span>Miles de historias por descubrir</span>
            </div>
        </aside>
    </main>

    <footer class="landingFooter">
        <span>DEEPROL</span>
        <p>Forja personajes. Escribe leyendas.</p>
        <span>EST. MMXXV</span>
    </footer>
</body>
</html>
