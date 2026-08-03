const notesFilterForm = document.getElementById("notesFilter");
const noteSearch = document.getElementById("noteSearch");
const characterFilters = Array.from(document.querySelectorAll('input[name="characterFilter"]'));
const noteGroups = Array.from(document.querySelectorAll(".charGroupedNotes"));
const notesNoResults = document.getElementById("notesNoResults");

function filterNotes() {
    const query = noteSearch?.value.trim().toLocaleLowerCase("es") || "";
    const selectedCharacter = document.querySelector('input[name="characterFilter"]:checked')?.value || "all";
    let visibleNotes = 0;

    noteGroups.forEach((group) => {
        const characterMatches = selectedCharacter === "all" || group.dataset.characterId === selectedCharacter;
        let visibleInGroup = 0;

        group.querySelectorAll(".noteCard").forEach((card) => {
            const searchMatches = !query || card.dataset.noteSearch.includes(query);
            const visible = characterMatches && searchMatches;
            card.hidden = !visible;
            if (visible) visibleInGroup += 1;
        });

        group.hidden = visibleInGroup === 0;
        visibleNotes += visibleInGroup;
    });

    if (notesNoResults) {
        notesNoResults.hidden = visibleNotes > 0;
    }
}

notesFilterForm?.addEventListener("submit", (event) => event.preventDefault());
noteSearch?.addEventListener("input", filterNotes);
characterFilters.forEach((filter) => filter.addEventListener("change", filterNotes));
