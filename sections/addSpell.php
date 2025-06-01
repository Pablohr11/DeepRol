<?php

require_once("../classes/DbConnector.php");

$db = DbConector::singleton();

$charId = $_GET["charId"];

$db->addSpell($charId, $_GET["id_spell"]);


$prevUrl = $_GET["prevPath"];

if (strpos($prevUrl, "--") != -1) {
    $prevUrl = str_replace("--", "&", $prevUrl);
}

// echo($prevUrl);

// header("Location: ".$prevUrl);

?>