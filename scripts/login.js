const authPanel = document.querySelector(".authPanel");
const changeFormButton = document.getElementById("changeFormButton");
const formMode = document.getElementById("formMode");
const authEyebrow = document.getElementById("authEyebrow");
const authTitle = document.getElementById("authTitle");
const authIntro = document.getElementById("authIntro");
const userLabel = document.getElementById("userLabel");
const modePrompt = document.getElementById("modePrompt");
const submitCopy = document.querySelector("#submitInput span");
const passwordInput = document.getElementById("formPwd");
const togglePassword = document.getElementById("togglePassword");

function setFormMode(mode) {
    const isRegister = mode === "register";

    formMode.value = isRegister ? "register" : "login";
    authPanel.dataset.mode = formMode.value;
    authEyebrow.textContent = isRegister ? "Comienza tu leyenda" : "Bienvenido de nuevo";
    authTitle.textContent = isRegister ? "Crear una cuenta" : "Iniciar sesión";
    authIntro.textContent = isRegister
        ? "Elige tus credenciales y prepara tu primera aventura."
        : "Introduce tus credenciales para volver a tu campaña.";
    userLabel.textContent = isRegister ? "Elige un nombre de usuario" : "Nombre de usuario";
    submitCopy.textContent = isRegister ? "Crear mi cuenta" : "Entrar en DeepRol";
    modePrompt.textContent = isRegister
        ? "¿Ya tienes una cuenta?"
        : "¿Aún no formas parte de la aventura?";
    changeFormButton.textContent = isRegister ? "Iniciar sesión" : "Crear una cuenta";
    passwordInput.autocomplete = isRegister ? "new-password" : "current-password";
}

changeFormButton.addEventListener("click", () => {
    setFormMode(formMode.value === "login" ? "register" : "login");
});

togglePassword.addEventListener("click", () => {
    const shouldShow = passwordInput.type === "password";
    passwordInput.type = shouldShow ? "text" : "password";
    togglePassword.setAttribute(
        "aria-label",
        shouldShow ? "Ocultar contraseña" : "Mostrar contraseña"
    );
    togglePassword.classList.toggle("isVisible", shouldShow);
});

setFormMode(authPanel.dataset.initialMode || "login");
