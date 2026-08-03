const globalSearch = document.getElementById("globalSearch");
const globalSearchInput = document.getElementById("globalSearchInput");
const globalSearchPanel = document.getElementById("globalSearchPanel");
const globalSearchStatus = document.getElementById("globalSearchStatus");
const globalSearchResults = document.getElementById("globalSearchResults");
const globalSearchAll = document.getElementById("globalSearchAll");

if (
    globalSearch
    && globalSearchInput
    && globalSearchPanel
    && globalSearchStatus
    && globalSearchResults
    && globalSearchAll
) {
    let searchTimer = 0;
    let searchRequest = null;
    let activeResultIndex = -1;
    let resultSerial = 0;

    function resultLinks() {
        return Array.from(globalSearchResults.querySelectorAll("[data-global-result]"));
    }

    function setPanelOpen(open) {
        globalSearchPanel.hidden = !open;
        globalSearchInput.setAttribute("aria-expanded", String(open));
        if (!open) {
            activeResultIndex = -1;
            globalSearchInput.removeAttribute("aria-activedescendant");
        }
    }

    function setStatus(message, state = "") {
        globalSearchStatus.textContent = message;
        globalSearchStatus.dataset.state = state;
        globalSearchStatus.hidden = false;
    }

    function navigate(path, page = "") {
        const safePath = String(path || "");
        if (!safePath || safePath.includes("..") || /^[a-z]+:/i.test(safePath)) {
            return;
        }

        const sectionPath = `sections/${safePath.replace(/^\/+/, "")}`;
        if (window.DeepRolNavigation?.navigate) {
            window.DeepRolNavigation.navigate(sectionPath, page);
        } else {
            const iframe = document.getElementById("mainIframe");
            if (iframe) iframe.src = sectionPath;
        }

        setPanelOpen(false);
        globalSearchInput.blur();
    }

    function activateResult(index) {
        const links = resultLinks();
        if (!links.length) {
            activeResultIndex = -1;
            return;
        }

        activeResultIndex = (index + links.length) % links.length;
        links.forEach((link, linkIndex) => {
            const active = linkIndex === activeResultIndex;
            link.classList.toggle("isActive", active);
            link.setAttribute("aria-selected", String(active));
            if (active) {
                globalSearchInput.setAttribute("aria-activedescendant", link.id);
                link.scrollIntoView({ block: "nearest" });
            }
        });
    }

    function createResult(result, group) {
        const link = document.createElement("a");
        link.className = "globalSearchResult";
        link.href = `sections/${String(result.path || "home.php")}`;
        link.dataset.globalResult = "";
        link.dataset.path = String(result.path || "home.php");
        link.dataset.page = String(result.page || group.page || "");
        link.id = `global-search-result-${++resultSerial}`;
        link.setAttribute("role", "option");
        link.setAttribute("aria-selected", "false");

        const badge = document.createElement("span");
        badge.className = "globalSearchResultBadge";
        badge.textContent = String(result.badge || "DR");

        const copy = document.createElement("span");
        copy.className = "globalSearchResultCopy";

        const title = document.createElement("strong");
        title.textContent = String(result.title || "Resultado");
        copy.appendChild(title);

        if (result.meta) {
            const meta = document.createElement("small");
            meta.textContent = String(result.meta);
            copy.appendChild(meta);
        }

        const arrow = document.createElement("span");
        arrow.className = "globalSearchResultArrow";
        arrow.setAttribute("aria-hidden", "true");
        arrow.textContent = "→";

        link.append(badge, copy, arrow);
        link.addEventListener("click", (event) => {
            event.preventDefault();
            navigate(link.dataset.path, link.dataset.page);
        });
        link.addEventListener("mouseenter", () => {
            const links = resultLinks();
            activateResult(links.indexOf(link));
        });

        return link;
    }

    function renderResults(payload) {
        globalSearchResults.replaceChildren();
        activeResultIndex = -1;
        resultSerial = 0;
        globalSearchInput.removeAttribute("aria-activedescendant");

        if (payload.error) {
            setStatus(payload.error, "error");
            globalSearchAll.hidden = true;
            return;
        }

        if (!payload.groups?.length) {
            setStatus(`No hay resultados para «${payload.query}».`, "empty");
            globalSearchAll.hidden = true;
            return;
        }

        globalSearchStatus.hidden = true;
        payload.groups.forEach((group) => {
            const section = document.createElement("section");
            section.className = "globalSearchGroup";

            const heading = document.createElement("h2");
            heading.textContent = String(group.label || "Resultados");
            section.appendChild(heading);

            (group.results || []).forEach((result) => {
                section.appendChild(createResult(result, group));
            });
            globalSearchResults.appendChild(section);
        });

        globalSearchAll.hidden = false;
    }

    async function runSearch() {
        const query = globalSearchInput.value.trim();
        if (query.length < 2) {
            searchRequest?.abort();
            globalSearchResults.replaceChildren();
            globalSearchAll.hidden = true;
            setStatus("Escribe al menos dos caracteres.");
            setPanelOpen(globalSearchInput === document.activeElement && query.length > 0);
            return;
        }

        searchRequest?.abort();
        searchRequest = new AbortController();
        setPanelOpen(true);
        globalSearchResults.replaceChildren();
        globalSearchAll.hidden = true;
        setStatus("Buscando en DeepRol...", "loading");

        try {
            const response = await fetch(
                `src/globalSearch.php?q=${encodeURIComponent(query)}`,
                {
                    headers: { Accept: "application/json" },
                    cache: "no-store",
                    signal: searchRequest.signal,
                }
            );
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();
            if (globalSearchInput.value.trim() !== query) return;
            renderResults(payload);
        } catch (error) {
            if (error.name === "AbortError") return;
            globalSearchResults.replaceChildren();
            globalSearchAll.hidden = true;
            setStatus("No se pudo consultar el archivo. Inténtalo de nuevo.", "error");
        }
    }

    function submitSearch() {
        const links = resultLinks();
        if (activeResultIndex >= 0 && links[activeResultIndex]) {
            links[activeResultIndex].click();
            return;
        }

        const query = globalSearchInput.value.trim();
        if (query.length < 2) {
            setPanelOpen(true);
            setStatus("Escribe al menos dos caracteres.");
            return;
        }

        navigate(`search.php?q=${encodeURIComponent(query)}`);
    }

    globalSearch.addEventListener("submit", (event) => {
        event.preventDefault();
        submitSearch();
    });

    globalSearchInput.addEventListener("input", () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(runSearch, 180);
    });

    globalSearchInput.addEventListener("focus", () => {
        if (globalSearchInput.value.trim().length >= 2) {
            runSearch();
        }
    });

    globalSearchInput.addEventListener("keydown", (event) => {
        if (event.key === "ArrowDown") {
            event.preventDefault();
            setPanelOpen(true);
            activateResult(activeResultIndex + 1);
        } else if (event.key === "ArrowUp") {
            event.preventDefault();
            setPanelOpen(true);
            const links = resultLinks();
            activateResult(activeResultIndex < 0 ? links.length - 1 : activeResultIndex - 1);
        } else if (event.key === "Escape") {
            event.preventDefault();
            setPanelOpen(false);
        } else if (event.key === "Enter") {
            event.preventDefault();
            submitSearch();
        }
    });

    document.addEventListener("keydown", (event) => {
        const target = event.target;
        const isTyping = target instanceof HTMLInputElement
            || target instanceof HTMLTextAreaElement
            || target?.isContentEditable;
        if (
            (event.ctrlKey || event.metaKey)
            && event.key.toLocaleLowerCase("es") === "k"
        ) {
            event.preventDefault();
            globalSearchInput.focus();
            globalSearchInput.select();
        } else if (event.key === "/" && !isTyping) {
            event.preventDefault();
            globalSearchInput.focus();
        }
    });

    document.addEventListener("pointerdown", (event) => {
        if (!globalSearch.contains(event.target)) {
            setPanelOpen(false);
        }
    });
}
