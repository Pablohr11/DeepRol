<?php
require_once("../classes/DbConnector.php");
//var_dump($_POST);
$db = DbConector::singleton();

if (!$_COOKIE["logged"]) {
    header("Location: ../login.php");
}

$notes = $db->getNotes($_COOKIE["logged"]);

// var_dump($notes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <link rel="stylesheet" href="../styles/index.css">
    <link rel="stylesheet" href="../styles/notes.css">
</head>
<body>
    <h1>Apuntes</h1>

    <div id="notesContainer">
        <?php foreach ($notes as $key => $note) { ?>
            <a href="note.php?id=<?=$note["ID"]?>" class="noteTitle"><?=$note["Nombre"]?> - <?=$note["Date"]?></a>
        <?php } ?>
    </div>
</body>
</html>