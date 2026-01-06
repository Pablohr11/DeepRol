<?php 

    require_once("../classes/DbConnector.php");
    $db = DbConector::singleton();
    $noteId = $_POST["noteId"];
    $noteValue = $_POST["value"];
    $db->saveNote($noteId, $noteValue);

    // echo($db->saveNote($noteId, $noteValue));


    return json_encode($db->saveNote($noteId, $noteValue))
?>