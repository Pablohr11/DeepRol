<?php

<<<<<<< Updated upstream
  require_once("../classes/DbConnector.php");
//var_dump($_POST);
  $db = DbConector::singleton();
=======
require_once __DIR__ . '/../src/bootstrap.php';
//var_dump($_POST);
$db = DbConector::singleton();

$userId = require_login();
$noteId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$noteInfo = $noteId ? $db->getNoteForUser($noteId, $userId) : false;
if (!$noteInfo) { http_response_code(404); exit('Nota no encontrada.'); }
$relatedCharName = $db->getCharName($noteInfo["RelatedChar"]);


$framed = ($_GET["framed"] ?? 'false') === 'true' ? 'true' : 'false';
>>>>>>> Stashed changes

  $noteInfo = $db->getNote($_GET["id"]);
  $relatedCharName = $db->getCharName($noteInfo["RelatedChar"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-32">
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
  
  <div class="mist"></div>
    <div id="editorContainer">
      <div class="subDiv">
          <h1><?=$noteInfo["Nombre"]?> - <?=$relatedCharName?></h1>
          <div style="display: grid;align-content: center;">
              <a href="notes.php">← VOLVER</a>
          </div>
        </div>
        <div id="toolbar"></div>
        <div id="editor">
            <?=$noteInfo["Value"]?>
        </div>
        <!-- <button onclick="console.log(document.querySelector('.ql-editor').innerHTML)">Guardar</button> -->
    </div>

</body>
<script>

const toolbarOptions = [
    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
    [{ 'font': [] }],
  ['bold', 'italic', 'underline', 'strike', 'blockquote'],        // toggled buttons
  ['link', 'image'],

  [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
  [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript


  [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
  ['clean']                                         // remove formatting button
];
  const quill = new Quill('#editor', {
    modules: {
      toolbar: toolbarOptions
    },
    theme: 'snow'
  });

      // Crear botón personalizado
    const customButton = document.createElement('button');
    customButton.innerHTML = 'Guardar';
    customButton.setAttribute('type', 'button');
    customButton.classList.add('ql-custom-button');

    // Agregar el botón al toolbar
    const toolbar = document.querySelector('div[role=toolbar]');
    const wrapper = document.createElement('span');
    wrapper.classList.add('ql-formats');
    wrapper.appendChild(customButton);
    toolbar.appendChild(wrapper);

    // Lógica del botón
    customButton.addEventListener('click', function () {
        saveNote();
    });

    function saveNote() {
      const noteId = <?=$_GET["id"]?>;
      const value = document.querySelector('.ql-editor').innerHTML;
      console.log(`nota: noteId=${encodeURIComponent(noteId)}&value=${encodeURIComponent(value)}`);

<<<<<<< Updated upstream
      fetch('/src/saveNote.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `noteId=${encodeURIComponent(noteId)}&value=${encodeURIComponent(value)}`
      })
      .then(response => {
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        console.log(response);
        return response.text(); // o .json() si esperas JSON
      })
      .then(data => {
        console.log(data);
      })
      .catch(error => {
        console.error('Error al guardar la nota:', error);
      });
    }
=======
  fetch('../src/saveNote.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `noteId=${encodeURIComponent(noteId)}&value=${encodeURIComponent(value)}`
  })
  .then(response => {
    if (!response.ok) throw new Error('Error en la respuesta del servidor');
    return response.text(); // o .json() si esperas JSON
  })
  .then(data => {
    console.log('Nota guardada exitosamente:', data);
  })
  .catch(error => {
    console.error('Error al guardar la nota:', error);
  });
}
>>>>>>> Stashed changes
</script>
</html>
