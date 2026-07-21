
<<<<<<< Updated upstream
window.addEventListener("load", (event) => {
=======
    var buttons = document.querySelectorAll(".spellspace");
    buttons = Array.from(buttons);
    buttons.forEach(function(element) {
        // Your code here
        element.addEventListener('click', function() {
            // element.classList.toggle("checked");
            var prevValue = 0;
            var match = element.className.match(/counter-(\d+)/);
            var currentValue = match ? Number(match[1]) : 0;
            prevValue = element.parentElement.parentElement.querySelector("input").value;
            element.parentElement.parentElement.querySelector("input").value = currentValue;
            updateChecks(currentValue, prevValue, element.parentElement);
        })
    });
>>>>>>> Stashed changes

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

<<<<<<< Updated upstream
=======


function updateChecks(index, prevIndex, parentElement) {
    var targetClass = "";
    if (index == prevIndex) {
        targetClass = ".counter-"+index;
        parentElement.querySelector(targetClass)?.classList.toggle("checked");
    } else if (index > prevIndex) {
        for (var i = 1; i <= index; i++) {
            targetClass = ".counter-"+i;
            parentElement.querySelector(targetClass).classList.add("checked")
        }
    } else {
        for (var i = 4; i > index; i--) {
            targetClass = ".counter-"+i;
            parentElement.querySelector(targetClass).classList.remove("checked")
        }
    }
}

>>>>>>> Stashed changes
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
