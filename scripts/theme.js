(function initialiseDeepRolTheme() {
    const STORAGE_KEY = "deeprol.lastCharacterTheme";
    const COLOR_MODE_STORAGE_KEY = "deeprol.colorMode";
    const DEFAULT_THEME = "arcano";
    const DEFAULT_COLOR_MODE = "dark";
    const VALID_COLOR_MODES = new Set(["dark", "light"]);
    const THEME_ALIASES = [
        { theme: "barbaro", names: ["barbaro", "barbarian", "berserker"] },
        { theme: "bardo", names: ["bardo", "bard", "colegio de"] },
        { theme: "brujo", names: ["brujo", "warlock", "archifey", "infernal"] },
        { theme: "clerigo", names: ["clerigo", "cleric", "dominio divino"] },
        { theme: "druida", names: ["druida", "druid", "circulo de"] },
        { theme: "explorador", names: ["explorador", "ranger", "cazador", "acechador"] },
        { theme: "guerrero", names: ["guerrero", "fighter", "campeon", "maestro de batalla", "caballero arcano"] },
        { theme: "hechicero", names: ["hechicero", "sorcerer", "magia salvaje", "linaje draconico"] },
        { theme: "mago", names: ["mago", "wizard", "evocador", "ilusionista", "nigromante", "abjurador", "adivino", "encantador", "transmutador", "conjurador"] },
        { theme: "monje", names: ["monje", "monk", "mano abierta", "cuatro elementos"] },
        { theme: "paladin", names: ["paladin", "juramento sagrado"] },
        { theme: "picaro", names: ["picaro", "rogue", "asesino", "ladron", "embaucador arcano"] },
        { theme: "artifice", names: ["artifice", "artificer", "alquimista", "artillero"] },
        { theme: "sangre", names: ["cazador de sangre", "blood hunter"] },
    ];
    const VALID_THEMES = new Set([
        DEFAULT_THEME,
        ...THEME_ALIASES.map((entry) => entry.theme),
    ]);
    let currentState = { theme: DEFAULT_THEME, className: "" };
    let currentColorMode = DEFAULT_COLOR_MODE;

    function normalise(value) {
        return String(value || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }

    function resolveClassTheme(characterClass) {
        const className = normalise(characterClass);
        let closestMatch = null;

        THEME_ALIASES.forEach((entry) => {
            entry.names.forEach((name) => {
                const position = className.indexOf(name);

                if (
                    position !== -1
                    && (
                        !closestMatch
                        || position < closestMatch.position
                        || (
                            position === closestMatch.position
                            && name.length > closestMatch.length
                        )
                    )
                ) {
                    closestMatch = { theme: entry.theme, position, length: name.length };
                }
            });
        });

        return closestMatch ? closestMatch.theme : DEFAULT_THEME;
    }

    function readStoredState() {
        try {
            const storedValue = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || "null");

            if (storedValue && VALID_THEMES.has(storedValue.theme)) {
                return {
                    theme: storedValue.theme,
                    className: String(storedValue.className || "").slice(0, 80),
                };
            }
        } catch (error) {
            // El tema predeterminado sigue funcionando si el almacenamiento está bloqueado.
        }

        return { theme: DEFAULT_THEME, className: "" };
    }

    function readStoredColorMode() {
        try {
            const storedMode = window.localStorage.getItem(COLOR_MODE_STORAGE_KEY);

            if (VALID_COLOR_MODES.has(storedMode)) {
                return storedMode;
            }
        } catch (error) {
            // El modo oscuro predeterminado sigue disponible sin almacenamiento.
        }

        return DEFAULT_COLOR_MODE;
    }

    function updateColorModeControls(mode = currentColorMode) {
        const nextModeLabel = mode === "dark" ? "claro" : "oscuro";

        document.querySelectorAll("[data-color-mode-toggle]").forEach((button) => {
            button.dataset.colorMode = mode;
            button.setAttribute("aria-label", `Activar tema ${nextModeLabel}`);
            button.setAttribute("title", `Activar tema ${nextModeLabel}`);
            button.setAttribute("aria-pressed", String(mode === "light"));

            const icon = button.querySelector("[data-color-mode-icon]");
            const label = button.querySelector("[data-color-mode-label]");

            if (icon) {
                icon.textContent = mode === "dark" ? "☀" : "☾";
            }

            if (label) {
                label.textContent = `Tema ${nextModeLabel}`;
            }
        });
    }

    function setColorMode(mode, persist = true) {
        currentColorMode = VALID_COLOR_MODES.has(mode)
            ? mode
            : DEFAULT_COLOR_MODE;
        document.documentElement.dataset.colorMode = currentColorMode;

        const themeColor = document.querySelector('meta[name="theme-color"]');
        if (themeColor) {
            themeColor.setAttribute(
                "content",
                currentColorMode === "light" ? "#f3efe7" : "#090d11"
            );
        }

        if (persist) {
            try {
                window.localStorage.setItem(COLOR_MODE_STORAGE_KEY, currentColorMode);
            } catch (error) {
                // El cambio sigue aplicándose aunque no pueda guardarse.
            }
        }

        updateColorModeControls();
        document.dispatchEvent(new CustomEvent("deeprol:colormodechange", {
            detail: { colorMode: currentColorMode },
        }));

        return currentColorMode;
    }

    function toggleColorMode() {
        return setColorMode(currentColorMode === "dark" ? "light" : "dark");
    }

    function bindColorModeControls() {
        document.querySelectorAll("[data-color-mode-toggle]").forEach((button) => {
            if (button.dataset.colorModeBound === "true") {
                return;
            }

            button.dataset.colorModeBound = "true";
            button.addEventListener("click", toggleColorMode);
        });

        updateColorModeControls();
    }

    function applyTheme(state, persist) {
        const safeState = {
            theme: VALID_THEMES.has(state.theme) ? state.theme : DEFAULT_THEME,
            className: String(state.className || "").slice(0, 80),
        };
        currentState = safeState;

        document.documentElement.dataset.theme = safeState.theme;

        if (safeState.className) {
            document.documentElement.dataset.themeClass = safeState.className;
        } else {
            delete document.documentElement.dataset.themeClass;
        }

        if (persist) {
            try {
                window.localStorage.setItem(STORAGE_KEY, JSON.stringify(safeState));
            } catch (error) {
                // La apariencia de la página actual no depende de poder persistirla.
            }
        }

        document.dispatchEvent(new CustomEvent("deeprol:themechange", {
            detail: safeState,
        }));

        return safeState;
    }

    function useCharacterClass(characterClass, persist = true) {
        const state = applyTheme({
            theme: resolveClassTheme(characterClass),
            className: String(characterClass || ""),
        }, persist);

        if (window.parent !== window) {
            window.parent.postMessage({
                type: "deeprol:character-theme",
                characterClass: state.className,
                theme: state.theme,
            }, window.location.origin);
        }

        return state;
    }

    const pageCharacterClass = document.documentElement.dataset.characterClass || "";
    setColorMode(readStoredColorMode(), false);

    if (pageCharacterClass) {
        useCharacterClass(pageCharacterClass);
    } else {
        applyTheme(readStoredState(), false);
    }

    window.DeepRolTheme = {
        applyTheme,
        getColorMode: () => currentColorMode,
        getState: () => ({ ...currentState }),
        resolveClassTheme,
        setColorMode,
        toggleColorMode,
        useCharacterClass,
    };

    window.addEventListener("storage", (event) => {
        if (event.key === STORAGE_KEY) {
            applyTheme(readStoredState(), false);
        }

        if (event.key === COLOR_MODE_STORAGE_KEY) {
            setColorMode(readStoredColorMode(), false);
        }
    });

    window.addEventListener("message", (event) => {
        if (
            event.origin === window.location.origin
            && event.data
            && event.data.type === "deeprol:character-theme"
        ) {
            useCharacterClass(event.data.characterClass);
        }
    });

    function updateThemeSummary(state = currentState) {
        const summary = document.querySelector(".activeThemeSummary");

        if (summary) {
            summary.textContent = state.className
                ? `Tema activo: ${state.className}`
                : "Tema grafito y latón";
        }
    }

    document.addEventListener("deeprol:themechange", (event) => {
        updateThemeSummary(event.detail);
    });

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            bindColorModeControls();
            updateThemeSummary();
        });
    } else {
        bindColorModeControls();
        updateThemeSummary();
    }

}());
