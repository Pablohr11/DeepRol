<?php

require_once("../classes/DbConnector.php");
//var_dump($_POST);
$db = DbConector::singleton();


if (isset($_GET["id"])) {
    $charData = $db->getChar($_GET["id"]);

    // var_dump($charData);

}

?>

<link rel="stylesheet" href="../styles/char.css">

<div id="charDiv">
    <img src="../resources/chars/draelith_cuerpo_completo.png" id="fullBodyImg" alt="">
    <div class="charInfo">
        <h2><?=$charData["name"]?></h2>
        <span id="charSubTitle"><?=$charData["raza"]?> / <?=$charData["clase"]?> (<?=$charData["nivel"]?>)</span>
    </div>
</div>