document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-search-result]").forEach((link) => {
        link.addEventListener("click", (event) => {
            const parentNavigation = window.parent?.DeepRolNavigation;
            if (window.parent === window || !parentNavigation?.navigate) {
                return;
            }

            event.preventDefault();
            const path = new URL(link.href, window.location.href);
            const sectionPath = `sections/${path.pathname.split("/").pop()}${path.search}${path.hash}`;
            parentNavigation.navigate(sectionPath, link.dataset.page || "");
        });
    });
});
