
window.addEventListener("load", () => {
    // Botones PDF
    document.getElementById("showPdfButton").addEventListener('click', function () {
        document.getElementById("embedContainer").style.display = "block";
    });

    document.getElementById("closeEmbed").addEventListener('click', function () {
        document.getElementById("embedContainer").style.display = "none";
    });

    // Tabs de conjuros
    const tabs = document.querySelectorAll('.tab');
    const lists = document.querySelectorAll('.spellList');

    function activateTab(index) {
        const current = document.querySelector('.spellList.active');
        const next = lists[index];
        if (!next) return;

        // Marcar tab activa
        tabs.forEach(tab => tab.classList.remove('active'));
        tabs[index].classList.add('active');

        // Si se está cambiando a una pestaña diferente
        if (current && current !== next) {
            current.classList.remove('fade-in');
            current.classList.add('fade-out');

            setTimeout(() => {
                current.classList.remove('active', 'fade-out');
                next.classList.add('active', 'fade-in');
            }, 300);

        } else if (!current) {
            // Primera vez que se activa algo
            next.classList.add('active', 'fade-in');
        } else if (current === next) {
            // Se clickeó la misma pestaña (opcionalmente refrescar animación)
            current.classList.remove('fade-in');
            void current.offsetWidth; // fuerza reflow
            current.classList.add('fade-in');
        }
    }

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => {
          // efecto visual de destello
          tab.classList.add('spark');
          setTimeout(() => tab.classList.remove('spark'), 400);
      
          localStorage.setItem('activeTabIndex', i);
          activateTab(i);
        });
      });
      
    
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
        var modsArray = ["STR","DEX","CON","INT","WIS","CHA", "ST Charisma", "Passive", "ProfBonus", "HPMax", "AC", "ST Strength", "ST Dexterity", "ST Constitution", "ST Intelligence", "ST Wisdom", "ST Charisma", "Acrobatics", "Animal", "Arcana", "Athletics", "Deception ", "History ", "Insight", "Intimidation", "Investigation ", "Medicine", "Nature", "Perception ", "Performance", "Persuasion", "Religion", "SleightofHand", "Stealth ", "Survival", "ClassLevel", "Race ","Background"]

        // console.log(formFields.filter(item => item.nombreCampo.includes("mod")));
        console.log(formFields);

        var fields = formFields.filter(item => modsArray.includes(item.nombreCampo) || item.nombreCampo.includes("mod"));
        // console.log(fields);
        var originalName = "";

        fields.forEach(field => {
            originalName = field.nombreCampo;
            field.nombreCampo = field.nombreCampo.trim();
            field.nombreCampo = field.nombreCampo.replace(" ", "-");
            // console.log(field);
            if (document.getElementById(field.nombreCampo)) {
                document.getElementById(field.nombreCampo).innerText = field.valor;
                document.getElementById(field.nombreCampo).setAttribute("dataframe-name", originalName)
            } else {
                console.error("no ta"+field.nombreCampo)
            };
        });
        //updateSTChecks(formFields.filter(item => item.nombreCampo.includes("mod")));

    });

    
}

function updateSTChecks(fields) {
    var STs = ["ST-Strength", "ST-Dexterity", "ST-Constitution", "ST-Intelligence", "ST-Wisdom", "ST-Charisma"];
    // console.log(fields)

    fields.forEach((statMod, index) => {
        // console.log(document.getElementById(STs[index]));
        // console.log(statMod.valor);
        if (document.getElementById(STs[index]).innerText != statMod.valor) {
            stDiv = document.getElementById(STs[index]);
            stDiv.parentElement.querySelector('input[type="radio"]').disabled = false;
            stDiv.parentElement.querySelector('input[type="radio"]').checked = true;
            stDiv.parentElement.querySelector('input[type="radio"]').disabled = true;
        }
    });
}