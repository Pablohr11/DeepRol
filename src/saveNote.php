<?php 

    require_once("../classes/DbConnector.php");
    $db = DbConector::singleton();
    $noteId = $_POST["noteId"];
    $noteValue = $_POST["value"];

    $db->saveNote($noteId, $noteValue);
?>