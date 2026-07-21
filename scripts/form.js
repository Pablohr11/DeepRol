function initForm() {

    var inputs = document.querySelectorAll(".formInputContainer input");
    inputs.forEach((node) => {
        node.addEventListener('focus', function(){
            node.parentElement.querySelector("span")?.classList.add("focused");
        })
        node.addEventListener('blur', function(){
            if (node.value == "") {
                node.parentElement.querySelector("span")?.classList.remove("focused");
            }
        })
    });

    var nameInput = document.getElementById("formName");
    var raceInput = document.getElementById("formRace");
    var nameField = document.getElementById("nameField");
    var raceField = document.getElementById("raceField");

    nameInput?.addEventListener('change', function(event) {
        nameField.innerText = event.currentTarget.value;
    })

    
    raceInput?.addEventListener('change', function(event) {
        raceField.innerText = event.currentTarget.value;
    })

    var index = 1;
    var nextStep = document.getElementById("nextStep");
    var prevStep = document.getElementById("prevStep");
    var submitButton = document.getElementById("submitInput");

    nextStep?.addEventListener("click", function() {
        index++;
        hideSteps();
        document.querySelector("#step"+index).style.display = "grid";

        updateButtons(index);
    });

    prevStep?.addEventListener("click", function() {
        index--;
        hideSteps();
        document.querySelector("#step"+index).style.display = "grid";

        updateButtons(index);
    });
    
    function updateButtons(index) {
        console.log(index);
        if (index > 1) {
            prevStep.style.display = "grid";
        } else {
            prevStep.style.display = "none";
        }

        if (index < 4 ) {
            nextStep.style.display = "grid";
        } else {
            nextStep.style.display = "none";
        }

        if (index == 4) {
            submitButton.style.display = "grid";
        } else {
            submitButton.style.display = "none";
        }
    }
}

function hideSteps() {
    var steps = document.querySelectorAll(".charStep");
    steps.forEach((node) => {
        node.style.display = "none";
    }); 
}

