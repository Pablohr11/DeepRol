const sidebar = document.getElementById("leftBar");
const sidebarToggle = document.getElementById("toogleSideBarButton");
const mainIframe = document.getElementById("mainIframe");
const navItems = Array.from(document.querySelectorAll(".navItem[data-src]"));
const themeStylesheetUrl = new URL("styles/theme.css", window.location.href).href;

function syncFrameTheme() {
    try {
        const frameDocument = mainIframe.contentDocument;
        const frameRoot = frameDocument?.documentElement;

        if (!frameDocument || !frameRoot) return;

        const characterClass = frameRoot.dataset.characterClass || "";
        let themeState = window.DeepRolTheme?.getState() || {
            theme: "arcano",
            className: "",
        };

        if (characterClass && window.DeepRolTheme) {
            const characterTheme = window.DeepRolTheme.resolveClassTheme(characterClass);

            if (
                themeState.theme !== characterTheme
                || themeState.className !== characterClass
            ) {
                themeState = window.DeepRolTheme.useCharacterClass(characterClass);
            }
        }

        frameRoot.dataset.theme = themeState.theme;
        frameRoot.dataset.colorMode = window.DeepRolTheme?.getColorMode() || "dark";

        if (themeState.className) {
            frameRoot.dataset.themeClass = themeState.className;
        }

        if (!frameDocument.querySelector("link[data-deeprol-theme]")) {
            const themeStylesheet = frameDocument.createElement("link");
            themeStylesheet.rel = "stylesheet";
            themeStylesheet.href = themeStylesheetUrl;
            themeStylesheet.dataset.deeprolTheme = "";
            frameDocument.head.appendChild(themeStylesheet);
        }
    } catch (error) {
        // Las vistas externas, si las hubiera, conservan sus propios estilos.
    }
}

mainIframe.addEventListener("load", syncFrameTheme);
document.addEventListener("deeprol:themechange", syncFrameTheme);
document.addEventListener("deeprol:colormodechange", syncFrameTheme);

if (mainIframe.contentDocument?.readyState === "complete") {
    syncFrameTheme();
}

function setSidebarState(open) {
    sidebar.classList.toggle("open", open);
    sidebarToggle.setAttribute("aria-expanded", String(open));
    sidebarToggle.setAttribute("aria-label", open ? "Contraer menú" : "Expandir menú");
    sidebarToggle.querySelector("span").textContent = open ? "‹" : "›";
}

sidebarToggle.addEventListener("click", () => {
    setSidebarState(!sidebar.classList.contains("open"));
});

function loadPage(item) {
    if (!item?.dataset.src) return;

    navigateTo(item.dataset.src, item.dataset.page);
}

function navigateTo(src, page = "") {
    if (!src) return;

    mainIframe.src = src;
    navItems.forEach((navItem) => {
        navItem.classList.toggle(
            "active",
            page !== "" && navItem.dataset.page === page
        );
    });

    if (window.innerWidth <= 760) {
        setSidebarState(false);
    }
}

navItems.forEach((item) => {
    item.addEventListener("click", () => loadPage(item));
});

window.changeMain = function changeMain(id) {
    const item = navItems.find((navItem) => navItem.dataset.page === id);
    if (item) loadPage(item);
};

window.DeepRolNavigation = Object.freeze({
    navigate: navigateTo,
});

const diceRoller = document.getElementById("diceRoller");
const diceResult = document.getElementById("diceResult");
const diceHint = document.getElementById("diceHint");

diceRoller.addEventListener("click", () => {
    diceRoller.classList.remove("rolling");
    void diceRoller.offsetWidth;
    diceRoller.classList.add("rolling");

    const result = Math.floor(Math.random() * 20) + 1;
    window.setTimeout(() => {
        diceResult.textContent = result;
        diceHint.textContent = result === 20
            ? "¡Crítico natural!"
            : result === 1
                ? "Pifia..."
                : `Resultado: ${result}`;
    }, 180);
});

if (window.matchMedia("(max-width: 980px)").matches) {
    setSidebarState(false);
}
