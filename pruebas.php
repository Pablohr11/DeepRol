<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
  
const url = './resources/fichas/Pablo.pdf';

  pdfjsLib.getDocument(url).promise.then(async (pdf) => {
    const numPages = pdf.numPages;
    const formFields = [];

    for (let pageNum = 1; pageNum <= numPages; pageNum++) {
      const page = await pdf.getPage(pageNum);
      const annotations = await page.getAnnotations();

      annotations.forEach(annotation => {
        if (annotation.subtype === 'Widget') {
          formFields.push({
            nombreCampo: annotation.fieldName,
            tipo: annotation.fieldType,
            valor: annotation.fieldValue || annotation.buttonValue || '',
            pagina: pageNum
          });
        }
      });
    }

    console.log("📝 Campos del formulario:");
    console.log(formFields);
  });

</script>