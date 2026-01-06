<?php
    require_once("../classes/DbConnector.php");
    //var_dump($_POST);
    $db = DbConector::singleton();

    if (!$_COOKIE["logged"]) {
        header("Location: ../login.php");
    } else {
        $userId = $_COOKIE["logged"];
    }

    $notes = $db->getNotes($_COOKIE["logged"]);

    $currentChar = ($notes[0]["RelatedChar"]);

    // echo("<pre>");
    // print_r($notes);
    // echo("</pre>");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-32">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <link rel="stylesheet" href="../styles/index.css">
    <link rel="stylesheet" href="../styles/notes.css">
</head>
<body>
    <h1>Apuntes</h1>
    <div id="notesContainer">
        <span class="charName"><img class="charImg" src="../resources/chars/<?=$notes[0]["name"]?>/<?=$notes[0]["image_path"]?>"><?=($notes[0]["name"]) ?></span>
        <?php foreach ($notes as $key => $note) { ?>
            <?php if ($currentChar != $note["RelatedChar"]) { $currentChar = $note["RelatedChar"]; ?>
                <span class="charName"><img class="charImg" src="../resources/chars/<?=$note["name"]?>/<?=$note["image_path"]?>"><?=($note["name"]) ?></span>
            <?php }  ?>
            <a href="note.php?id=<?=$note["ID"]?>" class="noteTitle"><?=$note["Nombre"]?> - <?= $db->getCharName($note["RelatedChar"])?> - <?=$note["Date"]?></a>
        <?php } ?>
        <a href="newNote.php?uid=<?=$userId?>">Nueva nota</a>
    </div>
</body>
</html>