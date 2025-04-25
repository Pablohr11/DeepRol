
window.addEventListener("load", (event) => {

    document.getElementById("showPdfButton").addEventListener('click', function() {
        document.getElementById("embedContainer").style.display = "block";
    
    })
    
    document.getElementById("closeEmbed").addEventListener('click', function() {
        document.getElementById("embedContainer").style.display = "none"
    })
    
    const tabs = document.querySelectorAll('.tab');
    const lists = document.querySelectorAll('.spellList');
    
    function activateTab(index) {
        tabs.forEach(tab => tab.classList.remove('active'));
        lists.forEach(list => list.classList.remove('active'));
    
        tabs[index].classList.add('active');
        lists[index].classList.add('active');
    }
    
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => activateTab(i));
    });
    
    // Activar la primera pestaña por defecto
    activateTab(0);
});

function setPdfFields(pdfPath) {
    const url = pdfPath;

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
        var modsArray = ["STR","DEX","CON","INT","WIS","CHA", "ST Charisma", "Passive"]
        // console.log("📝 Campos del formulario:");
        // console.log(formFields.filter(item => item.valor.includes("+1")));
        console.log(formFields);

        var fields = formFields.filter(item => modsArray.includes(item.nombreCampo) || item.nombreCampo.includes("mod"));
        console.log(fields);
        fields.forEach(field => {
            if (document.getElementById(field.nombreCampo)) {
                document.getElementById(field.nombreCampo).innerText = field.valor
            };
        });

    });
}