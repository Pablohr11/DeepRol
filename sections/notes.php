<?php
<<<<<<< Updated upstream
    require_once("../classes/DbConnector.php");
    //var_dump($_POST);
    $db = DbConector::singleton();

    if (!$_COOKIE["logged"]) {
        header("Location: ../login.php");
    } else {
        $userId = $_COOKIE["logged"];
    }

    $notes = $db->getNotes($_COOKIE["logged"]);
=======
require_once __DIR__ . '/../src/bootstrap.php';
//var_dump($_POST);
$db = DbConector::singleton();

$userId = require_login();

$notes = $db->getNotes($userId);
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
    <h1>Apuntes</h1>
=======
    
    <div class="stickyDiv" <?php if ($framed === 'true') {?> style="position:absolute; padding: 0 0px 10px; width:100%"  <?php } ?>>
        <div id="notesHeader">
            <h1>Apuntes</h1>
            <form action="" id="notesFilter">
                <input type="text" name="nameFilter">
                <div class="notesFilterDiv">    
                    <?php foreach ($notesChars as  $noteCharName) { ?>
                        <input type="radio" id="<?=$noteCharName["name"]?>" value="<?=$noteCharName["id_char"]?>" name="classFilter" <?php //if($classFilter == "paladin") echo("checked") ?>>
                        <label for="<?=$noteCharName[0]["name"]?>"><?=$noteCharName[0]["name"]?></label>
                    <?php }  ?>
                    
                </div>
                <input type="submit" value="Filtrar" name="submit">
        </form>
        </div>
    </div>
>>>>>>> Stashed changes
    <div id="notesContainer">
        <span class="charName"><img class="charImg" src="../resources/chars/<?=$notes[0]["name"]?>/<?=$notes[0]["image_path"]?>"><?=($notes[0]["name"]) ?></span>
        <?php foreach ($notes as $key => $note) { ?>
            <?php if ($currentChar != $note["RelatedChar"]) { $currentChar = $note["RelatedChar"]; ?>
                <span class="charName"><img class="charImg" src="../resources/chars/<?=$note["name"]?>/<?=$note["image_path"]?>"><?=($note["name"]) ?></span>
            <?php }  ?>
            <a href="note.php?id=<?=$note["ID"]?>" class="noteTitle"><?=$note["Nombre"]?> - <?= $db->getCharName($note["RelatedChar"])?> - <?=$note["Date"]?></a>
        <?php } ?>
<<<<<<< Updated upstream
        <a href="newNote.php?uid=<?=$userId?>">Nueva nota</a>
=======
        
        </div>
        <a href="newNote.php">Nueva nota</a>
>>>>>>> Stashed changes
    </div>
</body>
</html>
