<?php

require_once("../classes/DbConnector.php");

$db = DbConector::singleton();

$charId = $_GET["charId"];

$db->addSpell($charId, $_GET["id_spell"]);

$prevUrl = $_GET["prevPath"];

if (strpos($prevUrl, "--") != -1) {
    $prevUrl = str_replace("--", "&", $prevUrl);
}
if (substr($prevUrl,  13, length: 1) == "&") {
    // $prevUrl = "?".substr($prevUrl, 1);
    $prevUrl[13]='?'; 
}

// echo($prevUrl);

header("Location: ".$prevUrl);

?>