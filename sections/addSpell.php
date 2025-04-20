<?php

require_once("../classes/DbConnector.php");

$db = DbConector::singleton();

$db->addSpell(1, $_GET["id_spell"]);

$prevUrl = $_GET["prevPath"];


// echo($prevUrl);

header("Location: ".$prevUrl);

?>