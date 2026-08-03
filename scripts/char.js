const characterScriptUrl = document.currentScript?.src || window.location.href;
const localPdfModuleUrl = new URL("vendor/pdfjs/pdf.min.js", characterScriptUrl).href;
const localPdfWorkerUrl = new URL("vendor/pdfjs/pdf.worker.min.js", characterScriptUrl).href;
let pdfLibraryPromise = null;

window.addEventListener("load", () => {
    document.querySelectorAll(".spellCounter").forEach((counter) => {
        const input = counter.querySelector("input");
        const spaces = Array.from(counter.querySelectorAll(".spellSpace"));
        const addButton = counter.querySelector(".add-counter");
        const minusButton = counter.querySelector(".minus-counter");
        const summary = counter.querySelector(":scope > small");
        const totalSlots = spaces.length;

        function renderSpellSlots(value) {
            spaces.forEach((space, index) => {
                const isSpent = index < value;
                space.classList.toggle("checked", isSpent);
                space.setAttribute("aria-pressed", String(isSpent));
                space.setAttribute(
                    "aria-label",
                    `${isSpent ? "Recuperar" : "Gastar"} espacio ${index + 1}`
                );
            });

            if (summary) {
                summary.textContent = `${value} de ${totalSlots} gastados`;
            }
        }

        spaces.forEach((space, index) => {
            space.addEventListener("click", () => {
                const selectedValue = index + 1;
                const currentValue = Number.parseInt(input.value, 10) || 0;
                input.value = currentValue === selectedValue ? index : selectedValue;
                renderSpellSlots(Number(input.value));
            });
        });

        addButton?.addEventListener("click", () => {
            const currentValue = Number.parseInt(input.value, 10) || 0;
            input.value = Math.min(totalSlots, currentValue + 1);
            renderSpellSlots(Number(input.value));
        });

        minusButton?.addEventListener("click", () => {
            const currentValue = Number.parseInt(input.value, 10) || 0;
            input.value = Math.max(0, currentValue - 1);
            renderSpellSlots(Number(input.value));
        });

        renderSpellSlots(Number.parseInt(input.value, 10) || 0);
    });

    const journalTabs = Array.from(document.querySelectorAll(".tabsSelectorH2"));
    journalTabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            journalTabs.forEach((item) => item.classList.toggle("selected", item === tab));
            document.querySelectorAll(".tabContainer").forEach((container) => {
                container.style.display = container.id === tab.getAttribute("for") ? "block" : "none";
            });
        });
    });

    document.getElementById("showPdfButton")?.addEventListener("click", () => {
        const embedContainer = document.getElementById("embedContainer");
        if (embedContainer) {
            embedContainer.style.display = "block";
        }
    });

    document.getElementById("closeEmbed")?.addEventListener("click", () => {
        const embedContainer = document.getElementById("embedContainer");
        if (embedContainer) {
            embedContainer.style.display = "none";
        }
    });

    document.querySelectorAll(".spellSpan").forEach((spell) => {
        spell.addEventListener("click", () => {
            showEmbededSpell(spell.getAttribute("data-idspell"));
        });
    });

    const levelTabs = Array.from(document.querySelectorAll(".tabs .tab[data-level]"));
    const spellLists = Array.from(document.querySelectorAll(".spellList"));

    function activateLevel(level) {
        const targetLevel = String(level);
        const targetList = spellLists.find((list) => list.dataset.level === targetLevel);
        if (!targetList) return;

        levelTabs.forEach((tab) => {
            tab.classList.toggle("active", tab.dataset.level === targetLevel);
        });
        spellLists.forEach((list) => {
            const isActive = list === targetList;
            list.classList.toggle("active", isActive);
            list.classList.toggle("fade-in", isActive);
        });

        localStorage.setItem("activeCharacterSpellLevel", targetLevel);
    }

    levelTabs.forEach((tab) => {
        tab.addEventListener("click", () => activateLevel(tab.dataset.level));
    });

    const savedLevel = localStorage.getItem("activeCharacterSpellLevel");
    const initialLevel = spellLists.some((list) => list.dataset.level === savedLevel)
        ? savedLevel
        : spellLists[0]?.dataset.level;
    if (initialLevel !== undefined) {
        activateLevel(initialLevel);
    }
});

const supportedCharacterFields = new Set([
    "STR", "DEX", "CON", "INT", "WIS", "CHA", "ST Charisma", "Passive",
    "ProfBonus", "HPMax", "AC", "ST Strength", "ST Dexterity",
    "ST Constitution", "ST Intelligence", "ST Wisdom", "Acrobatics",
    "Animal", "Arcana", "Athletics", "Deception ", "History ", "Insight",
    "Intimidation", "Investigation ", "Medicine", "Nature", "Perception ",
    "Performance", "Persuasion", "Religion", "SleightofHand", "Stealth ",
    "Survival", "ClassLevel", "Race ", "Background",
]);

function updateCharacterSheetStatus(state, label) {
    const status = document.getElementById("sheetStatus");
    if (!status) return;

    status.classList.remove("isLoading", "isReady", "isFallback", "isError");
    status.classList.add(state);

    const labelElement = status.querySelector("span");
    if (labelElement) {
        labelElement.textContent = label;
    }
}

function applyCharacterFields(fields) {
    const entries = Array.isArray(fields)
        ? fields.map((field) => [field.name, field.value])
        : Object.entries(fields || {});
    let appliedFields = 0;

    if (window.DeepRolCharacterSheet) {
        window.DeepRolCharacterSheet.fields = {
            ...(window.DeepRolCharacterSheet.fields || {}),
            ...Object.fromEntries(entries),
        };
    }

    entries
        .filter(([name]) => (
            supportedCharacterFields.has(name)
            || String(name).includes("mod")
        ))
        .forEach(([name, value]) => {
            const normalisedName = String(name).trim();
            const elementId = normalisedName.replace(/\s+/g, "-");
            const target = document.getElementById(elementId);

            if (target) {
                target.textContent = value ?? "";
                target.setAttribute("dataframe-name", String(name));
                appliedFields += 1;
            }

            if (
                normalisedName === "ClassLevel"
                && value
                && window.DeepRolTheme
            ) {
                window.DeepRolTheme.useCharacterClass(value);
            }
        });

    return appliedFields;
}

async function getPdfLibrary() {
    if (window.pdfjsLib?.getDocument) {
        return window.pdfjsLib;
    }

    if (!pdfLibraryPromise) {
        pdfLibraryPromise = import(localPdfModuleUrl).then((pdfLibrary) => {
            if (pdfLibrary.GlobalWorkerOptions) {
                pdfLibrary.GlobalWorkerOptions.workerSrc = localPdfWorkerUrl;
            }

            window.pdfjsLib = pdfLibrary;
            return pdfLibrary;
        });
    }

    return pdfLibraryPromise;
}

async function extractPdfFormFields(pdf) {
    const fields = {};
    const checkboxes = [];

    for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
        const page = await pdf.getPage(pageNumber);
        const annotations = await page.getAnnotations();

        annotations.forEach((annotation) => {
            if (annotation.subtype !== "Widget" || !annotation.fieldName) {
                return;
            }

            const value = annotation.fieldValue ?? annotation.buttonValue ?? "";
            const isCheckbox = annotation.fieldType === "Btn"
                && Boolean(annotation.checkBox);
            if (isCheckbox) {
                if (value && value !== "Off") {
                    checkboxes.push(String(annotation.fieldName));
                }
                return;
            }

            fields[String(annotation.fieldName)] = Array.isArray(value)
                ? value.join(", ")
                : String(value ?? "");
        });
    }

    return {
        fields,
        checkboxes: Array.from(new Set(checkboxes)),
    };
}

async function setPdfFields(pdfPath, fallbackFields = {}) {
    const fallbackFieldCount = applyCharacterFields(fallbackFields);

    if (!pdfPath) {
        updateCharacterSheetStatus(
            fallbackFieldCount > 0 ? "isFallback" : "isError",
            fallbackFieldCount > 0
                ? "Datos básicos disponibles"
                : "Ficha PDF no disponible"
        );
        return;
    }

    updateCharacterSheetStatus(
        fallbackFieldCount > 0 ? "isFallback" : "isLoading",
        fallbackFieldCount > 0 ? "Datos locales disponibles" : "Cargando ficha"
    );

    try {
        const pdfLibrary = await getPdfLibrary();
        const pdf = await pdfLibrary.getDocument(pdfPath).promise;
        const extracted = await extractPdfFormFields(pdf);
        const loadedFieldCount = applyCharacterFields(extracted.fields);

        if (window.DeepRolCharacterSheet && extracted.checkboxes.length > 0) {
            window.DeepRolCharacterSheet.fields._pdfCheckboxes = extracted.checkboxes;
        }

        updateCharacterSheetStatus(
            loadedFieldCount > 0 ? "isReady" : "isFallback",
            loadedFieldCount > 0
                ? "Ficha sincronizada"
                : fallbackFieldCount > 0
                    ? "Datos locales disponibles"
                    : "Ficha sin campos compatibles"
        );
    } catch (error) {
        console.warn("No se pudieron leer los campos de la ficha PDF.", error);
        updateCharacterSheetStatus(
            fallbackFieldCount > 0 ? "isFallback" : "isError",
            fallbackFieldCount > 0
                ? "Datos locales disponibles"
                : "No se pudo leer la ficha"
        );
    }
}

window.setPdfFields = setPdfFields;

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("characterUpdateModal");
    const openButton = document.getElementById("updatePdfButton");
    const fieldsForm = document.getElementById("characterFieldsForm");
    const pdfForm = document.getElementById("characterPdfForm");
    if (!modal || !openButton || !fieldsForm || !pdfForm) return;

    const sheet = window.DeepRolCharacterSheet || {};
    const updateConfig = sheet.update || {};
    const catalog = updateConfig.catalog || { classes: [], races: [] };
    const tabButtons = Array.from(modal.querySelectorAll("[data-update-tab]"));
    const panels = Array.from(modal.querySelectorAll("[data-update-panel]"));
    const classSelect = document.getElementById("updateClassSelect");
    const classRowsContainer = document.getElementById("updateClassRows");
    const addClassButton = document.getElementById("updateAddClass");
    const totalLevelDisplay = document.getElementById("updateTotalLevel");
    const raceSelect = document.getElementById("updateRaceSelect");
    const subraceSelect = document.getElementById("updateSubraceSelect");
    const pdfInput = document.getElementById("updatedCharacterPdf");
    const pdfFileName = document.getElementById("characterPdfFileName");
    const pdfImportStatus = document.getElementById("pdfImportStatus");
    const initialMetadata = { ...(updateConfig.metadata || {}) };
    let lastFocusedElement = null;
    let pendingPdfFields = {};

    const hitDiceByClass = {
        Artifice: 8,
        Barbaro: 12,
        Bardo: 8,
        Brujo: 8,
        Clerigo: 8,
        Druida: 8,
        Explorador: 10,
        Guerrero: 10,
        Hechicero: 6,
        Mago: 6,
        Monje: 8,
        Paladin: 10,
        Picaro: 8,
    };
    const spellcastingAbilityByClass = {
        Artifice: "INT",
        Bardo: "CHA",
        Brujo: "CHA",
        Clerigo: "WIS",
        Druida: "WIS",
        Explorador: "WIS",
        Hechicero: "CHA",
        Mago: "INT",
        Paladin: "CHA",
    };
    const modifierFields = {
        STR: "STRmod",
        DEX: "DEXmod ",
        CON: "CONmod",
        INT: "INTmod",
        WIS: "WISmod",
        CHA: "CHamod",
    };
    const savingThrowFields = {
        str: { ability: "STR", field: "ST Strength", checkbox: "Check Box 11" },
        dex: { ability: "DEX", field: "ST Dexterity", checkbox: "Check Box 18" },
        con: { ability: "CON", field: "ST Constitution", checkbox: "Check Box 19" },
        int: { ability: "INT", field: "ST Intelligence", checkbox: "Check Box 20" },
        wis: { ability: "WIS", field: "ST Wisdom", checkbox: "Check Box 21" },
        cha: { ability: "CHA", field: "ST Charisma", checkbox: "Check Box 22" },
    };
    const skillFields = {
        acrobatics: { ability: "DEX", field: "Acrobatics", checkbox: "Check Box 23" },
        animal: { ability: "WIS", field: "Animal", checkbox: "Check Box 24" },
        arcana: { ability: "INT", field: "Arcana", checkbox: "Check Box 25" },
        athletics: { ability: "STR", field: "Athletics", checkbox: "Check Box 26" },
        deception: { ability: "CHA", field: "Deception ", checkbox: "Check Box 27" },
        history: { ability: "INT", field: "History ", checkbox: "Check Box 28" },
        insight: { ability: "WIS", field: "Insight", checkbox: "Check Box 29" },
        intimidation: { ability: "CHA", field: "Intimidation", checkbox: "Check Box 30" },
        investigation: { ability: "INT", field: "Investigation ", checkbox: "Check Box 31" },
        medicine: { ability: "WIS", field: "Medicine", checkbox: "Check Box 32" },
        nature: { ability: "INT", field: "Nature", checkbox: "Check Box 33" },
        perception: { ability: "WIS", field: "Perception ", checkbox: "Check Box 34" },
        performance: { ability: "CHA", field: "Performance", checkbox: "Check Box 35" },
        persuasion: { ability: "CHA", field: "Persuasion", checkbox: "Check Box 36" },
        religion: { ability: "INT", field: "Religion", checkbox: "Check Box 37" },
        sleight_of_hand: { ability: "DEX", field: "SleightofHand", checkbox: "Check Box 38" },
        stealth: { ability: "DEX", field: "Stealth ", checkbox: "Check Box 39" },
        survival: { ability: "WIS", field: "Survival", checkbox: "Check Box 40" },
    };
    const pdfFontSizeByField = {
        ClassLevel: 7,
        Background: 9,
        PlayerName: 9,
        "Race ": 8,
        Alignment: 9,
        "PersonalityTraits ": 8,
        Ideals: 8,
        Bonds: 8,
        Flaws: 8,
        AttacksSpellcasting: 7,
        ProficienciesLang: 7,
        Equipment: 7,
        "Features and Traits": 7,
        Backstory: 8,
        Allies: 8,
        FactionName: 9,
        "Feat+Traits": 8,
        Treasure: 8,
    };

    function normalizedText(value) {
        return String(value || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, " ")
            .trim();
    }

    function catalogOption(collection, name) {
        return (Array.isArray(collection) ? collection : [])
            .find((option) => option?.name === name);
    }

    function populateSelect(select, options, placeholder, preferredValue) {
        if (!select) return;
        select.replaceChildren(new Option(placeholder, ""));

        (Array.isArray(options) ? options : []).forEach((option) => {
            const label = option.source
                ? `${option.name} · ${option.source}`
                : option.name;
            select.add(new Option(label, option.name));
        });

        if (Array.from(select.options).some((option) => option.value === preferredValue)) {
            select.value = preferredValue;
        }
    }

    function classRows() {
        return Array.from(fieldsForm.querySelectorAll("[data-update-class-row]"));
    }

    function selectedClasses() {
        return classRows().map((row) => {
            const selected = row.querySelector("[data-update-class-select]");
            const subclass = row.querySelector("[data-update-subclass-select]");
            const classOption = catalogOption(catalog.classes, selected?.value || "");
            return {
                name: selected?.value || "",
                label: classOption?.label || selected?.selectedOptions?.[0]?.textContent?.trim() || "",
                subclass: subclass?.disabled ? "" : (subclass?.value || ""),
                level: Math.max(1, Math.min(20, Number(
                    row.querySelector("[data-update-class-level]")?.value || 1
                ))),
            };
        });
    }

    function updateSubclassRow(row, preferredValue = "") {
        const selected = row?.querySelector("[data-update-class-select]");
        const subclassSelect = row?.querySelector("[data-update-subclass-select]");
        const subclassHint = row?.querySelector("[data-update-subclass-hint]");
        const subclassField = row?.querySelector("[data-update-subclass-field]");
        const levelInput = row?.querySelector("[data-update-class-level]");
        if (!selected || !subclassSelect || !subclassField || !levelInput) return;

        const classOption = catalogOption(catalog.classes, selected.value);
        const subclasses = classOption?.subclasses || [];
        const subclassLevel = Math.max(1, Number(classOption?.subclassLevel || 1));
        const currentLevel = Math.max(1, Number(levelInput?.value || 1));
        const available = subclasses.length > 0 && currentLevel >= subclassLevel;

        populateSelect(
            subclassSelect,
            available ? subclasses : [],
            available ? "Sin subclase" : `Disponible en nivel ${subclassLevel}`,
            available ? preferredValue : ""
        );
        if (subclassSelect) subclassSelect.disabled = !available;
        subclassSelect.required = available;
        subclassField.hidden = !classOption;
        if (subclassHint) {
            subclassHint.textContent = available
                ? `La clase obtiene subclase en el nivel ${subclassLevel}.`
                : `Podrás elegirla al alcanzar el nivel ${subclassLevel}.`;
        }
    }

    function updateClassProgression() {
        const rows = classRows();
        const total = selectedClasses().reduce((sum, entry) => sum + entry.level, 0);
        if (totalLevelDisplay) totalLevelDisplay.textContent = String(total);

        rows.forEach((row, index) => {
            const levelInput = row.querySelector("[data-update-class-level]");
            const classInput = row.querySelector("[data-update-class-select]");
            levelInput?.setCustomValidity(
                total > 20 && index === 0
                    ? "La suma de niveles de clase no puede superar 20."
                    : ""
            );
            if (classInput) {
                const duplicate = classInput.value !== "" && rows.some((otherRow) => (
                    otherRow !== row
                    && otherRow.querySelector("[data-update-class-select]")?.value === classInput.value
                ));
                classInput.setCustomValidity(
                    duplicate ? "Esta clase ya está incluida." : ""
                );
            }
        });
        updateAbilityPreviews();
    }

    function addClassRow() {
        const firstRow = classRows()[0];
        if (!firstRow || classRows().length >= catalog.classes.length) return;

        const row = firstRow.cloneNode(true);
        row.querySelectorAll("[id]").forEach((element) => element.removeAttribute("id"));
        const heading = row.querySelector("[data-update-class-select]")?.closest(".updateField")?.querySelector("span");
        if (heading) heading.textContent = "Clase adicional";
        const selected = row.querySelector("[data-update-class-select]");
        const levelInput = row.querySelector("[data-update-class-level]");
        const subclass = row.querySelector("[data-update-subclass-select]");
        if (selected) selected.value = catalog.classes.find((option) => (
            !selectedClasses().some((entry) => entry.name === option.name)
        ))?.name || "";
        if (levelInput) levelInput.value = "1";
        if (subclass) subclass.value = "";

        let removeButton = row.querySelector("[data-update-remove-class]");
        if (!removeButton) {
            removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.className = "removeClassButton";
            removeButton.dataset.updateRemoveClass = "";
            removeButton.setAttribute("aria-label", "Eliminar esta clase");
            removeButton.textContent = "×";
            row.append(removeButton);
        }

        classRowsContainer?.append(row);
        updateSubclassRow(row, "");
        updateClassProgression();
        selected?.focus();
    }

    function updateSubraceOptions(preferredValue = subraceSelect?.value || "") {
        const raceOption = catalogOption(catalog.races, raceSelect?.value);
        const subraces = raceOption?.subraces || [];

        populateSelect(
            subraceSelect,
            subraces,
            subraces.length > 0 ? "Sin subraza" : "Esta raza no tiene variantes",
            preferredValue
        );
        if (subraceSelect) subraceSelect.disabled = subraces.length === 0;
    }

    function formatModifier(value) {
        return `${value >= 0 ? "+" : ""}${value}`;
    }

    function updateAbilityPreviews() {
        const modifiers = {};
        fieldsForm.querySelectorAll("[data-update-ability-modifier]").forEach((preview) => {
            const ability = preview.dataset.updateAbilityModifier;
            const input = fieldsForm.querySelector(`[data-sheet-field="${ability}"]`);
            const score = Number.parseInt(input?.value, 10);
            modifiers[ability] = Number.isFinite(score)
                ? Math.floor((score - 10) / 2)
                : 0;
            preview.textContent = Number.isFinite(score)
                ? formatModifier(modifiers[ability])
                : "—";
        });

        const totalLevel = Math.max(
            1,
            selectedClasses().reduce((sum, entry) => sum + entry.level, 0)
        );
        const proficiency = 2 + Math.floor((totalLevel - 1) / 4);
        Object.entries(savingThrowFields).forEach(([ability, definition]) => {
            const preview = fieldsForm.querySelector(`[data-update-save-preview="${ability}"]`);
            const checked = fieldsForm.querySelector(
                `input[name="saving_throw_proficiencies[]"][value="${ability}"]`
            )?.checked;
            if (preview) {
                preview.textContent = formatModifier(
                    (modifiers[definition.ability] || 0) + (checked ? proficiency : 0)
                );
            }
        });
        Object.entries(skillFields).forEach(([skill, definition]) => {
            const preview = fieldsForm.querySelector(`[data-update-skill-preview="${skill}"]`);
            const level = Number(fieldsForm.querySelector(
                `[data-update-skill="${skill}"]`
            )?.value || 0);
            if (preview) {
                preview.textContent = formatModifier(
                    (modifiers[definition.ability] || 0) + (proficiency * level)
                );
            }
        });
    }

    function syncEditorFromSheet() {
        const fields = sheet.fields || {};
        fieldsForm.querySelectorAll("[data-sheet-field]").forEach((input) => {
            const fieldName = input.dataset.sheetField;
            if (Object.prototype.hasOwnProperty.call(fields, fieldName)) {
                input.value = fields[fieldName] ?? "";
            }
        });
        updateAbilityPreviews();
    }

    function activateUpdateTab(tabName) {
        tabButtons.forEach((button) => {
            const active = button.dataset.updateTab === tabName;
            button.classList.toggle("isActive", active);
            button.setAttribute("aria-selected", String(active));
        });

        panels.forEach((panel) => {
            const active = panel.dataset.updatePanel === tabName;
            panel.classList.toggle("isActive", active);
            panel.hidden = !active;
        });

        fieldsForm.hidden = tabName === "pdf";
        if (tabName !== "pdf") {
            const selectedPanel = fieldsForm.querySelector(`[data-update-panel="${tabName}"]`);
            if (selectedPanel) selectedPanel.hidden = false;
        }
    }

    function openModal() {
        lastFocusedElement = document.activeElement;
        syncEditorFromSheet();
        modal.hidden = false;
        document.body.classList.add("isCharacterUpdateOpen");
        activateUpdateTab("general");
        requestAnimationFrame(() => {
            modal.classList.add("isOpen");
            modal.querySelector(".characterUpdateClose")?.focus();
        });
    }

    function closeModal() {
        modal.classList.remove("isOpen");
        document.body.classList.remove("isCharacterUpdateOpen");
        modal.hidden = true;
        lastFocusedElement?.focus?.();
    }

    function collectFields() {
        return Object.fromEntries(
            Array.from(fieldsForm.querySelectorAll("[data-sheet-field]"))
                .map((input) => [input.dataset.sheetField, input.value.trim()])
        );
    }

    function selectedMetadata() {
        const classes = selectedClasses();
        const primaryClass = classes[0] || {
            name: "",
            label: "",
            subclass: "",
            level: 1,
        };
        return {
            className: primaryClass.name,
            classLabel: primaryClass.label,
            subclassName: primaryClass.subclass,
            classes,
            raceName: raceSelect?.value || "",
            raceLabel: raceSelect?.selectedOptions?.[0]?.textContent?.trim() || "",
            subraceName: subraceSelect?.disabled ? "" : (subraceSelect?.value || ""),
            level: Math.max(
                1,
                Math.min(20, classes.reduce((sum, entry) => sum + entry.level, 0))
            ),
        };
    }

    function fieldsForPdf(fields) {
        const metadata = selectedMetadata();
        const classSummary = metadata.classes
            .map((entry) => `${entry.label}${entry.subclass ? ` · ${entry.subclass}` : ""}/${entry.level}`)
            .join(" / ");
        const hitDice = metadata.classes
            .map((entry) => `${entry.level}d${hitDiceByClass[entry.name] || 8}`)
            .join(" + ");
        const output = {
            ...(sheet.fields || {}),
            ...fields,
            "ClassLevel": classSummary,
            "Race ": `${metadata.raceLabel}${metadata.subraceName ? ` · ${metadata.subraceName}` : ""}`,
            "CharacterName 2": sheet.fields?.CharacterName || "",
        };
        const proficiency = 2 + Math.floor((metadata.level - 1) / 4);
        output.ProfBonus = formatModifier(proficiency);
        output.HDTotal = String(metadata.level);
        output.HD = hitDice;

        const modifiers = {};
        Object.entries(modifierFields).forEach(([ability, modifierField]) => {
            const score = Number.parseInt(output[ability], 10);
            if (Number.isFinite(score)) {
                modifiers[ability] = Math.floor((score - 10) / 2);
                output[modifierField] = formatModifier(modifiers[ability]);
            }
        });

        const managedCheckboxes = [
            ...Object.values(savingThrowFields),
            ...Object.values(skillFields),
        ].map((definition) => definition.checkbox);
        const checkboxes = Array.from(new Set(
            (Array.isArray(output._pdfCheckboxes) ? output._pdfCheckboxes : [])
                .filter((checkbox) => !managedCheckboxes.includes(checkbox))
        ));
        Object.entries(savingThrowFields).forEach(([ability, definition]) => {
            const proficient = fieldsForm.querySelector(
                `input[name="saving_throw_proficiencies[]"][value="${ability}"]`
            )?.checked;
            output[definition.field] = formatModifier(
                (modifiers[definition.ability] || 0) + (proficient ? proficiency : 0)
            );
            if (proficient) checkboxes.push(definition.checkbox);
        });
        Object.entries(skillFields).forEach(([skill, definition]) => {
            const proficiencyLevel = Number(fieldsForm.querySelector(
                `[data-update-skill="${skill}"]`
            )?.value || 0);
            output[definition.field] = formatModifier(
                (modifiers[definition.ability] || 0) + (proficiency * proficiencyLevel)
            );
            if (proficiencyLevel > 0) checkboxes.push(definition.checkbox);
        });
        output._pdfCheckboxes = Array.from(new Set(checkboxes));
        output.Passive = String(10 + Number.parseInt(output["Perception "], 10));

        const languages = Array.from(
            fieldsForm.querySelectorAll("input[name='languages[]']:checked")
        ).map((input) => input.value);
        String(fieldsForm.elements.custom_languages?.value || "")
            .split(/[,;\n]+/)
            .map((language) => language.trim())
            .filter(Boolean)
            .forEach((language) => {
                if (!languages.some((current) => normalizedText(current) === normalizedText(language))) {
                    languages.push(language);
                }
            });
        const otherProficiencies = String(
            fieldsForm.elements.other_proficiencies?.value || ""
        ).trim();
        output.ProficienciesLang = [
            otherProficiencies,
            languages.length ? `Idiomas: ${languages.join(", ")}` : "",
        ].filter(Boolean).join("\n\n");
        output._languages = languages;
        output._otherProficiencies = otherProficiencies;

        const spellcastingClass = metadata.classes.find(
            (entry) => spellcastingAbilityByClass[entry.name]
        );
        const spellAbility = spellcastingAbilityByClass[spellcastingClass?.name];
        const spellScore = Number.parseInt(output[spellAbility], 10);
        if (spellAbility && spellcastingClass && Number.isFinite(spellScore)) {
            const spellModifier = Math.floor((spellScore - 10) / 2);
            output["Spellcasting Class 2"] = spellcastingClass.label;
            output["SpellcastingAbility 2"] = spellAbility;
            output["SpellSaveDC  2"] = String(8 + proficiency + spellModifier);
            output["SpellAtkBonus 2"] = formatModifier(proficiency + spellModifier);
        }

        return output;
    }

    function pdfSafeText(value) {
        return String(value ?? "")
            .normalize("NFKC")
            .replace(
                /[^\u0009\u000A\u000D\u0020-\u007E\u00A0-\u00FF\u0152\u0153\u0160\u0161\u0178\u017D\u017E\u0192\u02C6\u02DC\u2013\u2014\u2018\u2019\u201A\u201C\u201D\u201E\u2020\u2021\u2022\u2026\u2030\u2039\u203A\u20AC\u2122]/g,
                "?"
            );
    }

    async function rewritePdf(source, fields) {
        if (!window.PDFLib?.PDFDocument) {
            throw new Error("No está disponible la herramienta local para actualizar el PDF.");
        }

        const pdfDocument = await window.PDFLib.PDFDocument.load(source);
        const pdfForm = pdfDocument.getForm();
        Object.entries(fields).forEach(([name, value]) => {
            if (name.startsWith("_")) return;
            try {
                const textField = pdfForm.getTextField(name);
                textField.setText(pdfSafeText(value));
                if (pdfFontSizeByField[name]) {
                    textField.setFontSize(pdfFontSizeByField[name]);
                }
            } catch (error) {
                // Algunas plantillas no contienen todos los campos de la ficha.
            }
        });
        const selectedCheckboxes = new Set(fields._pdfCheckboxes || []);
        [
            ...Object.values(savingThrowFields),
            ...Object.values(skillFields),
        ].forEach((definition) => {
            try {
                const checkbox = pdfForm.getCheckBox(definition.checkbox);
                if (selectedCheckboxes.has(definition.checkbox)) {
                    checkbox.check();
                } else {
                    checkbox.uncheck();
                }
            } catch (error) {
                // La plantilla puede no incluir todas las casillas de competencia.
            }
        });
        pdfForm.updateFieldAppearances();

        const bytes = await pdfDocument.save({
            addDefaultPage: false,
            useObjectStreams: false,
            updateFieldAppearances: false,
        });
        return new Blob([bytes], { type: "application/pdf" });
    }

    async function currentPdfBlob(fields) {
        if (!updateConfig.hasPdf || !sheet.pdfUrl) return null;
        const response = await fetch(sheet.pdfUrl, { cache: "no-store" });
        if (!response.ok) {
            throw new Error("No se ha podido abrir la ficha PDF actual.");
        }
        return rewritePdf(await response.arrayBuffer(), fieldsForPdf(fields));
    }

    function appendMetadata(formData) {
        const metadata = selectedMetadata();
        formData.delete("class_names[]");
        formData.delete("subclass_names[]");
        formData.delete("class_levels[]");
        metadata.classes.forEach((entry) => {
            formData.append("class_names[]", entry.name);
            formData.append("subclass_names[]", entry.subclass);
            formData.append("class_levels[]", String(entry.level));
        });
        formData.set("race_name", metadata.raceName);
        formData.set("subrace_name", metadata.subraceName);
        formData.set("level", String(metadata.level));

        formData.delete("saving_throw_proficiencies[]");
        formData.set("saving_throw_proficiencies_present", "1");
        fieldsForm.querySelectorAll(
            "input[name='saving_throw_proficiencies[]']:checked"
        ).forEach((input) => {
            formData.append("saving_throw_proficiencies[]", input.value);
        });
        Object.keys(skillFields).forEach((skill) => {
            formData.set(
                `skill_proficiencies[${skill}]`,
                fieldsForm.querySelector(`[data-update-skill="${skill}"]`)?.value || "0"
            );
        });

        formData.delete("languages[]");
        fieldsForm.querySelectorAll("input[name='languages[]']:checked").forEach((input) => {
            formData.append("languages[]", input.value);
        });
        formData.set(
            "custom_languages",
            String(fieldsForm.elements.custom_languages?.value || "")
        );
        formData.set(
            "other_proficiencies",
            String(fieldsForm.elements.other_proficiencies?.value || "")
        );
    }

    function setResult(element, message, state = "") {
        if (!element) return;
        element.textContent = message;
        element.classList.toggle("isError", state === "error");
        element.classList.toggle("isSuccess", state === "success");
    }

    function setFormBusy(form, busy) {
        form.classList.toggle("isBusy", busy);
        form.setAttribute("aria-busy", String(busy));
        form.querySelectorAll("button").forEach((control) => {
            if (busy) {
                control.dataset.wasDisabled = String(control.disabled);
                control.disabled = true;
            } else {
                control.disabled = control.dataset.wasDisabled === "true";
                delete control.dataset.wasDisabled;
            }
        });
    }

    async function sendUpdate(formData) {
        const response = await fetch(updateConfig.endpoint || formData.action, {
            method: "POST",
            body: formData,
            credentials: "same-origin",
            headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const responseText = await response.text();
        let payload = null;
        try {
            payload = JSON.parse(responseText);
        } catch (error) {
            /*
             * Algunos entornos PHP heredados imprimen avisos de subida antes del
             * JSON. El servidor ya intenta limpiarlos, pero conservamos esta
             * recuperación para no informar de un fallo después de haber guardado.
             */
            const jsonStart = responseText.indexOf('{"ok":');
            if (jsonStart >= 0) {
                try {
                    payload = JSON.parse(responseText.slice(jsonStart));
                } catch (nestedError) {
                    payload = null;
                }
            }
        }
        if (!response.ok || !payload?.ok) {
            throw new Error(payload?.message || "No se ha podido actualizar la ficha.");
        }
        return payload;
    }

    function metadataChanged() {
        const current = selectedMetadata();
        const initialClasses = Array.isArray(initialMetadata.classes)
            ? initialMetadata.classes.map((entry) => ({
                name: entry.class_name || entry.name || "",
                subclass: entry.subclass_name || entry.subclass || "",
                level: Number(entry.level || entry.class_level || 1),
            }))
            : [];
        const currentClasses = current.classes.map((entry) => ({
            name: entry.name,
            subclass: entry.subclass,
            level: entry.level,
        }));
        return JSON.stringify(currentClasses) !== JSON.stringify(initialClasses)
            || current.raceName !== initialMetadata.raceName
            || current.subraceName !== initialMetadata.subraceName;
    }

    async function extractFieldsFromFile(file) {
        const pdfLibrary = await getPdfLibrary();
        const bytes = new Uint8Array(await file.arrayBuffer());
        const pdf = await pdfLibrary.getDocument({ data: bytes }).promise;
        return extractPdfFormFields(pdf);
    }

    function detectIdentityFromPdf(fields) {
        const classLevel = String(fields.ClassLevel || "");
        const classPart = classLevel.split("/")[0];
        const classText = normalizedText(classPart);
        const levelMatch = classLevel.match(/(?:\/|\()\s*(\d{1,2})/);
        const matchedClass = (catalog.classes || [])
            .find((option) => {
                const names = [option.name, option.label].map(normalizedText);
                return names.some((name) => name && classText.includes(name));
            });

        if (matchedClass && classSelect) {
            classSelect.value = matchedClass.name;
            const subclass = (matchedClass.subclasses || [])
                .find((option) => classText.includes(normalizedText(option.name)));
            const primaryRow = classRows()[0];
            const primaryLevel = primaryRow?.querySelector("[data-update-class-level]");
            if (levelMatch && primaryLevel) {
                primaryLevel.value = String(Math.max(1, Math.min(20, Number(levelMatch[1]))));
            }
            updateSubclassRow(primaryRow, subclass?.name || "");
            updateClassProgression();
        }

        const raceText = normalizedText(fields["Race "] || "");
        let matchedRace = null;
        let matchedSubrace = "";
        (catalog.races || []).some((race) => {
            const subrace = (race.subraces || []).find((option) => (
                raceText === normalizedText(option.name)
                || raceText.includes(normalizedText(option.name))
            ));
            if (subrace) {
                matchedRace = race;
                matchedSubrace = subrace.name;
                return true;
            }

            const names = [race.name, race.label].map(normalizedText);
            if (names.some((name) => name && (raceText === name || raceText.startsWith(`${name} `)))) {
                matchedRace = race;
            }
            return false;
        });

        if (matchedRace && raceSelect) {
            raceSelect.value = matchedRace.name;
            updateSubraceOptions(matchedSubrace);
        }
    }

    openButton.addEventListener("click", openModal);
    modal.querySelectorAll("[data-close-character-update]").forEach((button) => {
        button.addEventListener("click", closeModal);
    });
    tabButtons.forEach((button) => {
        button.addEventListener("click", () => activateUpdateTab(button.dataset.updateTab));
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !modal.hidden) closeModal();
    });

    classRowsContainer?.addEventListener("change", (event) => {
        const row = event.target.closest("[data-update-class-row]");
        if (!row) return;
        if (event.target.matches("[data-update-class-select]")) {
            updateSubclassRow(row, "");
        }
        updateClassProgression();
    });
    classRowsContainer?.addEventListener("input", (event) => {
        const row = event.target.closest("[data-update-class-row]");
        if (!row || !event.target.matches("[data-update-class-level]")) return;
        updateSubclassRow(
            row,
            row.querySelector("[data-update-subclass-select]")?.value || ""
        );
        updateClassProgression();
    });
    classRowsContainer?.addEventListener("click", (event) => {
        const removeButton = event.target.closest("[data-update-remove-class]");
        if (!removeButton) return;
        removeButton.closest("[data-update-class-row]")?.remove();
        updateClassProgression();
    });
    addClassButton?.addEventListener("click", addClassRow);
    raceSelect?.addEventListener("change", () => updateSubraceOptions(""));
    fieldsForm.querySelectorAll("[data-update-ability-modifier]").forEach((preview) => {
        const field = fieldsForm.querySelector(
            `[data-sheet-field="${preview.dataset.updateAbilityModifier}"]`
        );
        field?.addEventListener("input", updateAbilityPreviews);
    });
    fieldsForm.querySelectorAll(
        "input[name='saving_throw_proficiencies[]'], [data-update-skill]"
    ).forEach((field) => {
        field.addEventListener("change", updateAbilityPreviews);
    });
    classRows().forEach((row) => {
        updateSubclassRow(
            row,
            row.querySelector("[data-update-subclass-select]")?.value || ""
        );
    });
    updateSubraceOptions(initialMetadata.subraceName || "");
    updateClassProgression();

    fieldsForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (!fieldsForm.reportValidity()) return;

        const result = document.getElementById("characterUpdateResult");
        const fields = collectFields();
        setResult(result, updateConfig.hasPdf
            ? "Actualizando datos y regenerando el PDF…"
            : "Guardando los datos de la ficha…");
        setFormBusy(fieldsForm, true);

        try {
            const formData = new FormData(fieldsForm);
            formData.set("fields", JSON.stringify(fields));
            appendMetadata(formData);
            const pdfBlob = await currentPdfBlob(fields);
            if (pdfBlob) {
                formData.set("generated_pdf", pdfBlob, "ficha-actualizada.pdf");
            }

            const payload = await sendUpdate(formData);
            sheet.fields = payload.fields || fieldsForPdf(fields);
            applyCharacterFields(sheet.fields);
            setResult(result, payload.message, "success");
            window.setTimeout(() => window.location.reload(), metadataChanged() ? 350 : 650);
        } catch (error) {
            setResult(result, error.message, "error");
            setFormBusy(fieldsForm, false);
        }
    });

    pdfInput?.addEventListener("change", async () => {
        const file = pdfInput.files?.[0];
        pendingPdfFields = {};
        if (!file) {
            if (pdfFileName) pdfFileName.textContent = "Ningún archivo seleccionado";
            if (pdfImportStatus) pdfImportStatus.textContent = "La ficha se analizará antes de subirla";
            return;
        }

        if (pdfFileName) {
            pdfFileName.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`;
        }
        if (file.size > 20 * 1024 * 1024) {
            if (pdfImportStatus) pdfImportStatus.textContent = "El archivo supera el máximo de 20 MB";
            return;
        }

        if (pdfImportStatus) pdfImportStatus.textContent = "Analizando campos rellenables…";
        try {
            const extracted = await extractFieldsFromFile(file);
            pendingPdfFields = {
                ...extracted.fields,
                _pdfCheckboxes: extracted.checkboxes,
            };
            detectIdentityFromPdf(extracted.fields);
            if (pdfImportStatus) {
                const fieldCount = Object.keys(extracted.fields).length;
                pdfImportStatus.textContent = fieldCount > 0
                    ? `${fieldCount} campos detectados y listos para importar`
                    : "PDF válido sin campos rellenables; se conservarán los datos locales";
            }
        } catch (error) {
            if (pdfImportStatus) {
                pdfImportStatus.textContent = "No se ha podido leer este PDF";
            }
        }
    });

    pdfForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (!pdfForm.reportValidity()) return;

        const result = document.getElementById("characterPdfResult");
        const file = pdfInput?.files?.[0];
        if (!file) return;
        if (file.size > 20 * 1024 * 1024) {
            setResult(result, "La ficha PDF debe ocupar menos de 20 MB.", "error");
            return;
        }

        setResult(result, "Preparando e importando la ficha…");
        setFormBusy(pdfForm, true);
        try {
            const finalFields = fieldsForPdf({
                ...(sheet.fields || {}),
                ...pendingPdfFields,
            });
            const normalizedPdf = await rewritePdf(
                await file.arrayBuffer(),
                finalFields
            );
            const formData = new FormData(pdfForm);
            formData.set("updated_pdf", normalizedPdf, file.name);
            formData.set("fields", JSON.stringify(pendingPdfFields));
            appendMetadata(formData);

            const payload = await sendUpdate(formData);
            setResult(result, payload.message, "success");
            window.setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            setResult(result, error.message, "error");
            setFormBusy(pdfForm, false);
        }
    });
});
