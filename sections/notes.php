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

$notesChars = $db->getNoteChars($userId);
// $notesCharsGrouped = $db->getNoteChars($userId, true);

$currentName = "";

if ($notes != null) {
    $currentName = $notesChars[key($notesChars  )][0]["name"];
}
// echo("<pre>");
// // var_dump($notesChars);
// echo("</pre>");

$framed = "false";

if (isset($_GET["framed"]) && $_GET["framed"] == "true") {
  $framed = "true";
}

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
    
    <div class="stickyDiv" <?php if ($framed) {?> style="position:absolute; padding: 0 0px 10px; width:100%"  <?php } ?>>
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
    <div id="notesContainer">
        <div class="noteList">
            
        <?php foreach ($notes as $key => $note) { ?>            
            <div class="charGroupedNotes">
                <div class="charGroupedNotesInnerSupDiv">
                    <img class="charListImage" src="../resources/chars/<?=$notesChars[$key][0]["name"]?>/<?=$notesChars[$key][0]["image_path"]?>">
                    <h2><?=$notesChars[$key][0]["name"]?></h2>
                </div>
                <div class="actualNotes">
                    <?php foreach ($note as $key => $actualNote) { ?>
                        <dt>
                            <a href="note.php?id=<?=$actualNote["ID"]?>&framed=<?=$framed?>" class="actualNoteTitle"><span><?=$actualNote["Nombre"]?></span> - <?=$actualNote["Date"]?></a>
                        </dt>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        
        </div>
        <a href="newNote.php?uid=<?=$userId?>">Nueva nota</a>
    </div>
</body>
</html>