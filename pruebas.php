<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editor PDF en Línea</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
  <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    #pdf-container {
      position: relative;
      width: 100%;
      max-width: 900px;
      margin-top: 20px;
    }
    canvas {
      border: 1px solid #aaa;
      width: 100%;
    }
    .field {
      position: absolute;
      border: 1px solid #666;
      background: rgba(255,255,255,0.8);
      padding: 2px;
      font-size: 14px;
    }
    #download-link {
      display: none;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <h1>Editor de Ficha D&D (Demo)</h1>
  <input type="file" id="pdf-upload" accept="application/pdf" />
  <div id="pdf-container">
    <canvas id="pdf-canvas"></canvas>
    <!-- Posiciona tus campos según la estructura del PDF -->
    <input id="CharacterName" class="field" placeholder="Nombre" style="top: 100px; left: 90px; width: 180px;">
    <input id="ClassLevel" class="field" placeholder="Clase/Nivel" style="top: 100px; left: 280px; width: 140px;">
    <input id="STR" class="field" placeholder="Fuerza" style="top: 200px; left: 70px; width: 40px;">
  </div>
  <button id="save-btn">Guardar Cambios</button>
  <a id="download-link">Descargar PDF editado</a>

  <script>
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    let originalPdfBytes;
    let pdfDoc;
    const fields = ['CharacterName', 'ClassLevel', 'STR'];

    document.getElementById('pdf-upload').addEventListener('change', async (e) => {
      const file = e.target.files[0];
      originalPdfBytes = await file.arrayBuffer();
      
      // Render PDF con pdf.js
      const loadingTask = pdfjsLib.getDocument({ data: originalPdfBytes });
      pdfDoc = await loadingTask.promise;
      const page = await pdfDoc.getPage(1);
      const scale = 1.5;
      const viewport = page.getViewport({ scale });
      canvas.width = viewport.width;
      canvas.height = viewport.height;
      await page.render({ canvasContext: ctx, viewport }).promise;
    });

    document.getElementById('save-btn').addEventListener('click', async () => {
      const pdfDoc = await PDFLib.PDFDocument.load(originalPdfBytes);
      const form = pdfDoc.getForm();

      for (const id of fields) {
        const value = document.getElementById(id).value;
        try {
          form.getTextField(id).setText(value);
        } catch (err) {
          console.warn(`Campo no encontrado: ${id}`);
        }
      }

      const pdfBytes = await pdfDoc.save();
      const blob = new Blob([pdfBytes], { type: 'application/pdf' });
      const url = URL.createObjectURL(blob);

      const link = document.getElementById('download-link');
      link.href = url;
      link.download = "ficha_editada.pdf";
      link.style.display = 'inline';
      link.textContent = "Descargar PDF editado";
    });
  </script>
</body>
</html>
