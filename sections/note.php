<?php

require_once("../classes/DbConnector.php");
//var_dump($_POST);
$db = DbConector::singleton();

$noteId = $_GET["id"];

$noteInfo = $db->getNote($noteId);
$relatedCharName = $db->getCharName($noteInfo["RelatedChar"]);


$framed = $_GET["framed"];

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
  
  <div class="mist"></div>
    <div id="editorContainer">
      <div class="subDiv">
          <h1><?=$noteInfo["Nombre"]?> - <?=$relatedCharName?></h1>
          <div style="display: grid;align-content: center;">
              <a href="notes.php?framed=<?=$framed?>">← VOLVER</a>
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

const Font = Quill.import('formats/font');

Font.whitelist = ['sans', 'serif', 'cinzel', 'uncial', 'merriweather', 'librebaskerville', 'ebgaramond'];
Quill.register(Font, true);

const toolbarOptions = [
  [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
  [{ 'size': ['small', false, 'large', 'huge'] }],
  [{ 'font': ['sans', 'serif', 'cinzel', 'uncial', 'merriweather', 'librebaskerville', 'ebgaramond'] }],
  ['bold', 'italic', 'underline', 'strike', 'blockquote'],
  ['link', 'image'],
  [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
  [{ 'script': 'sub' }, { 'script': 'super' }],
  [{ 'color': [] }, { 'background': [] }],
  ['clean']
];

const quill = new Quill('#editor', {
  modules: {
    toolbar: toolbarOptions
  },
  theme: 'snow'
});

// const fontLabels = {
//   cinzel: 'Cinzel',
//   uncial: 'Uncial Antiqua',
//   merriweather: 'Merriweather',
//   librebaskerville: 'Libre Baskerville',
//   ebgaramond: 'EB Garamond'
// };

// document.querySelectorAll('.ql-font .ql-picker-item').forEach(el => {
//   const val = el.getAttribute('data-value');
//   if (fontLabels[val]) {
//     el.innerText = fontLabels[val];
//   }
// });

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
  const noteId = <?=$noteId?>;
  const value = document.querySelector('.ql-editor').innerHTML;

  fetch('http://localhost:8080/src/savenote.php', {
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
</script>
</html>