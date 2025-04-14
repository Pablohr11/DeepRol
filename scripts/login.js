var changeFormButton;
var formSubmitButton;
var userSpan;

function init() {

    var inputs = document.querySelectorAll(".formInputContainer input");
    inputs.forEach((node) => {
        node.addEventListener('focus', function(){
            node.parentElement.querySelector("span").classList.add("focused"); 
        })
        node.addEventListener('blur', function(){
            if (node.value == "") {
                node.parentElement.querySelector("span").classList.remove("focused"); 
            }
        })
    });

    changeFormButton = document.getElementById("changeFormButton");
    formSubmitButton = document.getElementById("submitInput");
    userSpan = document.getElementById("userSpan");

    changeFormButton.addEventListener('click', function() {
        console.log("click")
        if (changeFormButton.innerText == "CREAR CUENTA") {
            changeForm("toSignIn");
        } else {
            changeForm("toLogIn");
        }
    })

}

function changeForm(targetState) {
    if (targetState == "toSignIn") {
        changeFormButton.innerText = "Iniciar Sesión";
        userSpan.innerText = "Nuevo usuario";
        formSubmitButton.value = "Crear cuenta";
    } else if (targetState == "toLogIn") {
        changeFormButton.innerText = "Crear cuenta";
        userSpan.innerText = "Usuario";
        formSubmitButton.value = "Iniciar Sesión";
    }
}

