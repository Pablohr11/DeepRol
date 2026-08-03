const addCharacterButton = document.getElementById("addCharButton");
const addCharacterVideo = document.getElementById("background-video");

if (addCharacterButton && addCharacterVideo) {
    addCharacterButton.addEventListener("mouseenter", () => {
        addCharacterVideo.play().catch(() => {});
    });

    addCharacterButton.addEventListener("mouseleave", () => {
        addCharacterVideo.pause();
    });
}

const searchInput = document.getElementById("characterSearch");
const characterCards = Array.from(document.querySelectorAll(".characterCard"));
const noResults = document.getElementById("noResults");

if (searchInput) {
    searchInput.addEventListener("input", () => {
        const query = searchInput.value.trim().toLocaleLowerCase("es");
        let visibleCharacters = 0;

        characterCards.forEach((card) => {
            const matches = !query || card.dataset.character.includes(query);
            card.hidden = !matches;
            if (matches) visibleCharacters += 1;
        });

        if (noResults) {
            noResults.hidden = visibleCharacters > 0;
        }
    });
}
