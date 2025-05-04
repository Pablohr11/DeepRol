<?php

    $account = '<a href="login.php"><img src="../../resources/imgs/account.png"></a>';

    if (isset($_COOKIE["logged"]) && $_COOKIE["logged"] == true ) {
        $account = strtoupper( $_COOKIE["userInitial"]);
    } 

?>

<div id="header" class="flicker">
    <div></div>
    <div id="centerPanel"><a href="/"><img id="logo" src="../../resources/logos/logo_no_bg.png"></a></div>
    <div id="rightPanel">
        <div id="accountButton"><?= $account ?></div>
    </div>
</div>