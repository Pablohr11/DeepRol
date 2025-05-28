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
    <!-- <script src="https://cdn.ckeditor.com/ckeditor5/36.0.0/classic/ckeditor.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/45.1.0/ckeditor5.umd.js"></script>
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/45.1.0/ckeditor5.css" /> -->

    <!-- Include stylesheet -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.core.css" /> -->

    <!-- Include the Quill library -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
</head>
<body>
    <h1><?=$noteInfo["Nombre"]?></h1>
    <div id="editorContainer">
        <div id="editor">
            <?=$noteInfo["Value"]?>
        </div>
    </div>

    <button onclick="console.log(document.querySelector('.ql-editor').innerHTML)">Guardar</button>
</body>
<script>
    // const {
    //     ClassicEditor,
    //     Essentials,
    //     Bold,
    //     Italic,
    //     Font,
    //     Paragraph
    // } = CKEDITOR;
    // import '../styles/editor.css';
    var editor;
    // ClassicEditor
    //     .create( document.querySelector( '#editor' ) )
    //     .then( newEditor => {
    //     editor = newEditor;
    //     } )
    //     .catch( error => {
    //         console.error( error );
    //     } );

  const quill = new Quill('#editor', {
    theme: 'snow'
  });
</script>
</html>