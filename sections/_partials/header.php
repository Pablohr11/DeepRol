<?php
    require_once __DIR__ . '/../../src/bootstrap.php';
    $account = '<a aria-label="Iniciar sesión" href="' . h(url('login.php')) . '"><img src="' . h(url('resources/imgs/account.png')) . '" alt="Cuenta"></a>';
    if (current_user_id()) {
        $account = h($_SESSION['user_initial'] ?? '?');
    }

?>

<div id="header">
    <div></div>
    <div id="centerPanel"><a href="<?= h(url()) ?>"><img id="logo" src="<?= h(url('resources/logos/logo_no_bg.png')) ?>" alt="DeepRol"></a></div>
    <div id="rightPanel">
        <div id="accountButton"><?= $account ?></div>
    </div>
</div>
