document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-compendium]").forEach((catalog) => {
        const entryList = catalog.querySelector("[data-entry-list]");
        const entries = Array.from(catalog.querySelectorAll("[data-entry]"));
        const searchInput = catalog.querySelector("[data-search]");
        const clearSearchButton = catalog.querySelector("[data-clear-search]");
        const filters = Array.from(catalog.querySelectorAll("[data-filter]"));
        const sortSelect = catalog.querySelector("[data-sort]");
        const resultCount = catalog.querySelector("[data-result-count]");
        const emptyState = catalog.querySelector("[data-empty-state]");
        const resetButtons = Array.from(catalog.querySelectorAll("[data-reset-filters]"));
        let renderFrame = 0;

        if (searchInput && !searchInput.value) {
            const initialQuery = new URLSearchParams(window.location.search).get("q");
            if (initialQuery) {
                searchInput.value = initialQuery.slice(0, 80);
            }
        }

        function normalise(value) {
            return String(value || "")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLocaleLowerCase("es")
                .trim();
        }

        function filterValues(entry, key) {
            return String(entry.dataset[key] || "")
                .split("|")
                .map(normalise)
                .filter(Boolean);
        }

        function compareEntries(first, second) {
            const sortMode = sortSelect?.value || "name";
            if (sortMode === "challenge-asc" || sortMode === "challenge-desc") {
                const direction = sortMode === "challenge-asc" ? 1 : -1;
                const firstChallenge = Number.parseFloat(first.dataset.challenge || "0");
                const secondChallenge = Number.parseFloat(second.dataset.challenge || "0");
                const difference = (firstChallenge - secondChallenge) * direction;
                if (difference !== 0) return difference;
            }

            if (sortMode === "variants-desc") {
                const difference = Number(second.dataset.variants || 0)
                    - Number(first.dataset.variants || 0);
                if (difference !== 0) return difference;
            }

            return String(first.dataset.name || "").localeCompare(
                String(second.dataset.name || ""),
                "es",
                { sensitivity: "base" }
            );
        }

        function renderCatalog() {
            const query = normalise(searchInput?.value);
            const activeFilters = filters
                .map((filter) => ({
                    key: filter.dataset.filter,
                    value: normalise(filter.value),
                }))
                .filter((filter) => filter.key && filter.value);

            const sortedEntries = [...entries].sort(compareEntries);
            let visibleCount = 0;

            sortedEntries.forEach((entry) => {
                const searchableText = normalise(
                    `${entry.dataset.name || ""} ${entry.dataset.searchText || ""}`
                );
                const matchesSearch = !query || searchableText.includes(query);
                const matchesFilters = activeFilters.every((filter) =>
                    filterValues(entry, filter.key).includes(filter.value)
                );
                const isVisible = matchesSearch && matchesFilters;

                entry.hidden = !isVisible;
                if (isVisible) visibleCount++;
                entryList?.appendChild(entry);
            });

            if (resultCount) resultCount.textContent = String(visibleCount);
            if (emptyState) emptyState.hidden = visibleCount !== 0;
            if (entryList) entryList.hidden = visibleCount === 0;
            if (clearSearchButton) clearSearchButton.hidden = !searchInput?.value;
        }

        function scheduleRender() {
            window.cancelAnimationFrame(renderFrame);
            renderFrame = window.requestAnimationFrame(renderCatalog);
        }

        function resetCatalog() {
            if (searchInput) searchInput.value = "";
            filters.forEach((filter) => {
                filter.value = "";
            });
            if (sortSelect) sortSelect.value = "name";
            catalog.querySelectorAll("details[open]").forEach((details) => {
                details.open = false;
            });
            renderCatalog();
            searchInput?.focus({ preventScroll: true });
        }

        searchInput?.addEventListener("input", scheduleRender);
        clearSearchButton?.addEventListener("click", () => {
            if (!searchInput) return;
            searchInput.value = "";
            renderCatalog();
            searchInput.focus();
        });
        filters.forEach((filter) => filter.addEventListener("change", renderCatalog));
        sortSelect?.addEventListener("change", renderCatalog);
        resetButtons.forEach((button) => button.addEventListener("click", resetCatalog));

        renderCatalog();
    });
});
