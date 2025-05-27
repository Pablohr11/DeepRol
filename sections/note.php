<?php

require_once("../classes/DbConnector.php");
//var_dump($_POST);
$db = DbConector::singleton();

$noteInfo = $db->getNote($_GET["id"]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <link rel="stylesheet" href="../styles/index.css">
    <link rel="stylesheet" href="../styles/note.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.0/classic/ckeditor.js"></script>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.1.0/ckeditor5.css" />
</head>
<body>
    <h1><?=$noteInfo["Nombre"]?></h1>

    <div id="editor">
        <?=$noteInfo["Value"]?>
    </div>

    <button onclick="console.log(editor.getData())">Guardar</button>
</body>
<script>
    var editor;
    ClassicEditor
        .create( document.querySelector( '#editor' ) )
        .then( newEditor => {
        editor = newEditor;
        } )
        .catch( error => {
            console.error( error );
        } );


</script>
</html>