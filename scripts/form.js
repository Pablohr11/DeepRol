document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("characterForm");
    if (!form) return;

    const steps = Array.from(form.querySelectorAll(".formStep"));
    const progressButtons = Array.from(document.querySelectorAll("[data-step-target]"));
    const previousButton = document.getElementById("previousStep");
    const nextButton = document.getElementById("nextStep");
    const submitButton = document.getElementById("submitInput");
    const stepStatus = document.getElementById("stepStatus");
    const creationOverlay = document.getElementById("creationOverlay");
    const generatedPdfInput = document.getElementById("generatedPdf");
    const classSelect = document.getElementById("classSelect");
    const classHint = document.getElementById("classHint");
    const classLevelRows = document.getElementById("classLevelRows");
    const addClassButton = document.getElementById("addClassButton");
    const totalLevelInput = document.getElementById("totalLevel");
    const totalLevelPreview = document.getElementById("totalLevelPreview");
    const raceSelect = document.getElementById("raceSelect");
    const subraceField = document.getElementById("subraceField");
    const subraceSelect = document.getElementById("subraceSelect");
    let characterOptions = { classes: [], races: [] };
    try {
        characterOptions = JSON.parse(
            document.getElementById("characterOptions")?.textContent || "{}"
        );
    } catch (error) {
        console.error("No se pudo leer el catálogo de personajes.", error);
    }
    let currentStep = 0;
    let highestVisitedStep = 0;
    let isGenerating = false;
    let savingThrowsTouched = false;

    const abilityNames = ["str", "dex", "con", "int", "wis", "cha"];
    const savingThrowFields = {
        str: { field: "ST Strength", checkbox: "Check Box 11" },
        dex: { field: "ST Dexterity", checkbox: "Check Box 18" },
        con: { field: "ST Constitution", checkbox: "Check Box 19" },
        int: { field: "ST Intelligence", checkbox: "Check Box 20" },
        wis: { field: "ST Wisdom", checkbox: "Check Box 21" },
        cha: { field: "ST Charisma", checkbox: "Check Box 22" },
    };
    const skillFields = {
        acrobatics: { ability: "dex", field: "Acrobatics", checkbox: "Check Box 23" },
        animal: { ability: "wis", field: "Animal", checkbox: "Check Box 24" },
        arcana: { ability: "int", field: "Arcana", checkbox: "Check Box 25" },
        athletics: { ability: "str", field: "Athletics", checkbox: "Check Box 26" },
        deception: { ability: "cha", field: "Deception ", checkbox: "Check Box 27" },
        history: { ability: "int", field: "History ", checkbox: "Check Box 28" },
        insight: { ability: "wis", field: "Insight", checkbox: "Check Box 29" },
        intimidation: { ability: "cha", field: "Intimidation", checkbox: "Check Box 30" },
        investigation: { ability: "int", field: "Investigation ", checkbox: "Check Box 31" },
        medicine: { ability: "wis", field: "Medicine", checkbox: "Check Box 32" },
        nature: { ability: "int", field: "Nature", checkbox: "Check Box 33" },
        perception: { ability: "wis", field: "Perception ", checkbox: "Check Box 34" },
        performance: { ability: "cha", field: "Performance", checkbox: "Check Box 35" },
        persuasion: { ability: "cha", field: "Persuasion", checkbox: "Check Box 36" },
        religion: { ability: "int", field: "Religion", checkbox: "Check Box 37" },
        sleight_of_hand: { ability: "dex", field: "SleightofHand", checkbox: "Check Box 38" },
        stealth: { ability: "dex", field: "Stealth ", checkbox: "Check Box 39" },
        survival: { ability: "wis", field: "Survival", checkbox: "Check Box 40" },
    };
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
        Artifice: "int",
        Bardo: "cha",
        Brujo: "cha",
        Clerigo: "wis",
        Druida: "wis",
        Explorador: "wis",
        Hechicero: "cha",
        Mago: "int",
        Paladin: "cha",
    };

    function numberValue(name, fallback = 0) {
        const parsed = Number.parseInt(form.elements[name]?.value, 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function textValue(name) {
        return String(form.elements[name]?.value || "").trim();
    }

    function abilityModifier(score) {
        return Math.floor((score - 10) / 2);
    }

    function proficiencyBonus(level) {
        return 2 + Math.floor((Math.max(1, level) - 1) / 4);
    }

    function formatModifier(value) {
        return `${value >= 0 ? "+" : ""}${value}`;
    }

    function pdfSafeText(value) {
        return String(value ?? "")
            .normalize("NFKC")
            .replace(
                /[^\u0009\u000A\u000D\u0020-\u007E\u00A0-\u00FF\u0152\u0153\u0160\u0161\u0178\u017D\u017E\u0192\u02C6\u02DC\u2013\u2014\u2018\u2019\u201A\u201C\u201D\u201E\u2020\u2021\u2022\u2026\u2030\u2039\u203A\u20AC\u2122]/g,
                "?"
            );
    }

    function catalogOption(collection, name) {
        return (Array.isArray(collection) ? collection : [])
            .find((option) => option?.name === name);
    }

    function populateSubtypeSelect(select, options, placeholder, preferredValue = "") {
        if (!select) return;

        select.replaceChildren(new Option(placeholder, ""));
        options.forEach((option) => {
            const label = option.source
                ? `${option.name} · ${option.source}`
                : option.name;
            select.add(new Option(label, option.name));
        });

        if (options.some((option) => option.name === preferredValue)) {
            select.value = preferredValue;
        }
    }

    function classRows() {
        return Array.from(form.querySelectorAll("[data-class-row]"));
    }

    function classEntries() {
        return classRows().map((row) => {
            const selected = row.querySelector("[data-class-select]");
            const subclass = row.querySelector("[data-subclass-select]");
            const level = row.querySelector("[data-class-level]");
            const classOption = catalogOption(characterOptions.classes, selected?.value || "");
            return {
                name: selected?.value || "",
                label: classOption?.label || selected?.value || "",
                subclass: subclass?.disabled ? "" : (subclass?.value || ""),
                level: Math.max(1, Math.min(20, Number(level?.value || 1))),
            };
        });
    }

    function updateTotalLevel() {
        const rows = classRows();
        const total = classEntries().reduce((sum, entry) => sum + entry.level, 0);
        if (totalLevelInput) totalLevelInput.value = String(Math.max(1, total));
        if (totalLevelPreview) totalLevelPreview.textContent = String(total);

        rows.forEach((row, index) => {
            const level = row.querySelector("[data-class-level]");
            const classField = row.querySelector("[data-class-select]");
            if (!level) return;
            level.setCustomValidity(
                total > 20 && index === 0
                    ? "La suma de los niveles de clase no puede superar 20."
                    : ""
            );
            if (classField) {
                const duplicate = classField.value !== "" && rows.some((otherRow) => (
                    otherRow !== row
                    && otherRow.querySelector("[data-class-select]")?.value === classField.value
                ));
                classField.setCustomValidity(
                    duplicate ? "Esta clase ya está incluida en el personaje." : ""
                );
            }
        });
        updateCalculatedValues();
    }

    function updateSubclassRow(row, preferredValue = "") {
        const selected = row?.querySelector("[data-class-select]");
        const level = row?.querySelector("[data-class-level]");
        const subclassSelect = row?.querySelector("[data-subclass-select]");
        const subclassField = row?.querySelector("[data-subclass-field]");
        const subclassHint = row?.querySelector("[data-subclass-hint]");
        if (!selected || !level || !subclassSelect || !subclassField) return;
        const classOption = catalogOption(
            characterOptions.classes,
            selected.value || ""
        );
        const subclasses = Array.isArray(classOption?.subclasses)
            ? classOption.subclasses
            : [];
        const subclassLevel = Math.max(1, Number(classOption?.subclassLevel || 1));
        const canChooseSubclass = Boolean(classOption)
            && subclasses.length > 0
            && Number(level.value || 1) >= subclassLevel;

        subclassField.hidden = !classOption;
        subclassSelect.disabled = !canChooseSubclass;
        subclassSelect.required = canChooseSubclass;

        populateSubtypeSelect(
            subclassSelect,
            subclasses,
            canChooseSubclass
                ? "Selecciona una subclase"
                : `Disponible en el nivel ${subclassLevel}`,
            canChooseSubclass ? preferredValue : ""
        );

        if (subclassHint) {
            subclassHint.textContent = canChooseSubclass
                ? `Subclase obtenida en el nivel ${subclassLevel}.`
                : `Esta clase elige subclase al alcanzar el nivel ${subclassLevel}.`;
        }
    }

    function updateSubclassOptions(preferredValue = "") {
        const primaryRow = classRows()[0];
        updateSubclassRow(
            primaryRow,
            preferredValue || primaryRow?.querySelector("[data-subclass-select]")?.value || ""
        );
    }

    function syncInitialSavingThrows() {
        if (savingThrowsTouched) return;
        const defaults = {
            Artifice: ["con", "int"],
            Barbaro: ["str", "con"],
            Bardo: ["dex", "cha"],
            Brujo: ["wis", "cha"],
            Clerigo: ["wis", "cha"],
            Druida: ["int", "wis"],
            Explorador: ["str", "dex"],
            Guerrero: ["str", "con"],
            Hechicero: ["con", "cha"],
            Mago: ["int", "wis"],
            Monje: ["str", "dex"],
            Paladin: ["wis", "cha"],
            Picaro: ["dex", "int"],
        };
        const selected = new Set(defaults[classSelect?.value] || []);
        form.querySelectorAll("input[name='saving_throw_proficiencies[]']").forEach((input) => {
            input.checked = selected.has(input.value);
        });
        updateCalculatedValues();
    }

    function addClassRow() {
        const firstRow = classRows()[0];
        if (!firstRow || classRows().length >= characterOptions.classes.length) return;

        const row = firstRow.cloneNode(true);
        row.querySelectorAll("[id]").forEach((element) => element.removeAttribute("id"));
        row.querySelectorAll(".fieldError").forEach((element) => element.remove());
        const label = row.querySelector("[data-class-select]")?.closest(".field")?.querySelector("span");
        if (label) label.textContent = "Clase adicional";
        const classField = row.querySelector("[data-class-select]");
        const levelField = row.querySelector("[data-class-level]");
        const subclassField = row.querySelector("[data-subclass-select]");
        if (classField) classField.value = "";
        if (levelField) levelField.value = "1";
        if (subclassField) {
            subclassField.replaceChildren(new Option("Sin subclase", ""));
            subclassField.disabled = true;
            subclassField.required = false;
        }

        let removeButton = row.querySelector("[data-remove-class]");
        if (!removeButton) {
            removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.className = "removeClassButton";
            removeButton.dataset.removeClass = "";
            removeButton.setAttribute("aria-label", "Eliminar esta clase");
            removeButton.textContent = "×";
            row.append(removeButton);
        }

        classLevelRows?.append(row);
        updateSubclassRow(row);
        updateTotalLevel();
        classField?.focus();
    }

    function updateSubraceOptions(preferredValue = subraceSelect?.value || "") {
        if (!subraceField || !subraceSelect) return;

        const raceOption = catalogOption(
            characterOptions.races,
            raceSelect?.value || ""
        );
        const subraces = Array.isArray(raceOption?.subraces)
            ? raceOption.subraces
            : [];
        const hasSubraces = subraces.length > 0;

        subraceField.hidden = !hasSubraces;
        subraceSelect.disabled = !hasSubraces;
        subraceSelect.required = hasSubraces;
        populateSubtypeSelect(
            subraceSelect,
            subraces,
            "Selecciona una subraza o variante",
            preferredValue
        );
    }

    function getAbilityModifiers() {
        return Object.fromEntries(
            abilityNames.map((ability) => [
                ability,
                abilityModifier(numberValue(`ability_${ability}`, 10)),
            ])
        );
    }

    function showStep(index, options = {}) {
        currentStep = Math.max(0, Math.min(index, steps.length - 1));
        if (options.markVisited !== false) {
            highestVisitedStep = Math.max(highestVisitedStep, currentStep);
        }

        steps.forEach((step, stepIndex) => {
            const isCurrent = stepIndex === currentStep;
            step.hidden = !isCurrent;
            step.classList.toggle("isActive", isCurrent);
        });

        progressButtons.forEach((button, buttonIndex) => {
            button.classList.toggle("isActive", buttonIndex === currentStep);
            button.classList.toggle("isComplete", buttonIndex < currentStep);
            button.disabled = buttonIndex > highestVisitedStep;
            button.setAttribute("aria-current", buttonIndex === currentStep ? "step" : "false");
        });

        previousButton.hidden = currentStep === 0;
        nextButton.hidden = currentStep === steps.length - 1;
        submitButton.hidden = currentStep !== steps.length - 1;
        stepStatus.textContent = `Paso ${currentStep + 1} de ${steps.length}`;

        if (options.focusHeading) {
            steps[currentStep].querySelector("h2")?.focus({ preventScroll: true });
        }

        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    function firstInvalidField(step) {
        return Array.from(step.querySelectorAll("input, select, textarea"))
            .find((field) => !field.disabled && !field.checkValidity());
    }

    function validateStep(step) {
        const invalidField = firstInvalidField(step);
        if (!invalidField) return true;

        invalidField.closest(".field, .uploadCard, .abilityField")?.classList.add("hasError");
        invalidField.reportValidity();
        invalidField.focus({ preventScroll: true });
        invalidField.scrollIntoView({ behavior: "smooth", block: "center" });
        return false;
    }

    function updateClassHint() {
        const option = classSelect?.selectedOptions?.[0];
        const description = option?.dataset.description || "";
        classHint.textContent = description;
        classHint.hidden = description === "";

        const className = classSelect?.value || "";
        if (className) {
            window.DeepRolTheme?.useCharacterClass(className);
        }
    }

    function updateCalculatedValues() {
        const modifiers = getAbilityModifiers();
        const level = numberValue("level", 1);
        const profBonus = proficiencyBonus(level);

        abilityNames.forEach((ability) => {
            const target = document.querySelector(`[data-modifier-for="${ability}"]`);
            if (target) target.textContent = formatModifier(modifiers[ability]);

            const saveInput = form.querySelector(
                `input[name="saving_throw_proficiencies[]"][value="${ability}"]`
            );
            const savePreview = document.querySelector(`[data-save-preview="${ability}"]`);
            if (savePreview) {
                savePreview.textContent = formatModifier(
                    modifiers[ability] + (saveInput?.checked ? profBonus : 0)
                );
            }
        });

        const proficiencyPreview = document.getElementById("proficiencyPreview");
        if (proficiencyPreview) {
            proficiencyPreview.textContent = formatModifier(profBonus);
        }

        Object.entries(skillFields).forEach(([skill, skillInfo]) => {
            const proficiencyLevel = numberValue(`skill_${skill}`, 0);
            const preview = document.querySelector(`[data-skill-preview="${skill}"]`);
            if (preview) {
                preview.textContent = formatModifier(
                    modifiers[skillInfo.ability] + (profBonus * proficiencyLevel)
                );
            }
        });
    }

    function setupUploadPreviews() {
        form.querySelectorAll(".uploadCard input[type='file']").forEach((input) => {
            input.addEventListener("change", () => {
                const file = input.files?.[0];
                const preview = input.closest(".uploadCard")?.querySelector("[data-upload-preview]");
                const image = preview?.querySelector("img");
                const icon = preview?.querySelector("b");
                if (!preview || !image || !icon) return;

                const previousUrl = image.dataset.objectUrl;
                if (previousUrl) URL.revokeObjectURL(previousUrl);

                if (!file || !file.type.startsWith("image/")) {
                    image.hidden = true;
                    image.removeAttribute("src");
                    icon.hidden = false;
                    return;
                }

                const objectUrl = URL.createObjectURL(file);
                image.src = objectUrl;
                image.dataset.objectUrl = objectUrl;
                image.alt = `Vista previa de ${file.name}`;
                image.hidden = false;
                icon.hidden = true;
            });
        });
    }

    function buildPdfData() {
        const classes = classEntries();
        const className = classes[0]?.name || "";
        const raceName = textValue("race_name");
        const subraceName = textValue("subrace_name");
        const raceLabel = catalogOption(characterOptions.races, raceName)?.label
            || raceName;
        const level = numberValue("level", 1);
        const profBonus = proficiencyBonus(level);
        const modifiers = getAbilityModifiers();
        const classSummary = classes
            .map((entry) => `${entry.label}${entry.subclass ? ` · ${entry.subclass}` : ""}/${entry.level}`)
            .join(" / ");
        const hitDice = classes
            .map((entry) => `${entry.level}d${hitDiceByClass[entry.name] || 8}`)
            .join(" + ");
        const languages = Array.from(
            form.querySelectorAll("input[name='languages[]']:checked")
        ).map((input) => input.value);
        String(form.elements.custom_languages?.value || "")
            .split(/[,;\n]+/)
            .map((language) => language.trim())
            .filter(Boolean)
            .forEach((language) => {
                if (!languages.some((current) => current.toLocaleLowerCase("es") === language.toLocaleLowerCase("es"))) {
                    languages.push(language);
                }
            });
        const otherProficiencies = textValue("proficiencies_languages");
        const proficienciesAndLanguages = [
            otherProficiencies,
            languages.length ? `Idiomas: ${languages.join(", ")}` : "",
        ].filter(Boolean).join("\n\n");
        const savingThrows = new Set(
            Array.from(
                form.querySelectorAll("input[name='saving_throw_proficiencies[]']:checked")
            ).map((input) => input.value)
        );
        const checkboxes = [];
        const modifierFields = {
            str: "STRmod",
            dex: "DEXmod ",
            con: "CONmod",
            int: "INTmod",
            wis: "WISmod",
            cha: "CHamod",
        };
        const fields = {
            "CharacterName": textValue("character_name"),
            "CharacterName 2": textValue("character_name"),
            "ClassLevel": classSummary,
            "Background": textValue("background"),
            "PlayerName": textValue("player_name"),
            "Race ": `${raceLabel}${subraceName ? ` · ${subraceName}` : ""}`,
            "Alignment": textValue("alignment"),
            "XP": String(Math.max(0, numberValue("experience", 0))),
            "Inspiration": textValue("inspiration"),
            "ProfBonus": formatModifier(profBonus),
            "AC": String(numberValue("armor_class", 10)),
            "Initiative": textValue("initiative") !== ""
                ? formatModifier(numberValue("initiative"))
                : formatModifier(modifiers.dex),
            "Speed": String(numberValue("speed", 30)),
            "HPMax": String(numberValue("hp_max", 1)),
            "HPCurrent": String(numberValue("hp_current", numberValue("hp_max", 1))),
            "HPTemp": String(Math.max(0, numberValue("hp_temp", 0))),
            "HDTotal": String(level),
            "HD": hitDice,
            "PersonalityTraits ": textValue("personality_traits"),
            "Ideals": textValue("ideals"),
            "Bonds": textValue("bonds"),
            "Flaws": textValue("flaws"),
            "AttacksSpellcasting": textValue("attacks_spellcasting"),
            "ProficienciesLang": proficienciesAndLanguages,
            "Equipment": textValue("equipment"),
            "Features and Traits": textValue("features_traits"),
            "CP": String(Math.max(0, numberValue("coins_cp"))),
            "SP": String(Math.max(0, numberValue("coins_sp"))),
            "EP": String(Math.max(0, numberValue("coins_ep"))),
            "GP": String(Math.max(0, numberValue("coins_gp"))),
            "PP": String(Math.max(0, numberValue("coins_pp"))),
            "Age": textValue("age"),
            "Height": textValue("height"),
            "Weight": textValue("weight"),
            "Eyes": textValue("eyes"),
            "Skin": textValue("skin"),
            "Hair": textValue("hair"),
            "Backstory": textValue("backstory"),
            "Allies": textValue("allies"),
            "FactionName": textValue("faction_name"),
            "Feat+Traits": textValue("additional_features"),
            "Treasure": textValue("treasure"),
        };

        abilityNames.forEach((ability) => {
            const pdfAbility = ability.toUpperCase();
            fields[pdfAbility] = String(numberValue(`ability_${ability}`, 10));
            fields[modifierFields[ability]] = formatModifier(modifiers[ability]);

            const saveValue = modifiers[ability] + (savingThrows.has(ability) ? profBonus : 0);
            fields[savingThrowFields[ability].field] = formatModifier(saveValue);
            if (savingThrows.has(ability)) {
                checkboxes.push(savingThrowFields[ability].checkbox);
            }
        });

        Object.entries(skillFields).forEach(([skill, skillInfo]) => {
            const proficiencyLevel = numberValue(`skill_${skill}`, 0);
            const value = modifiers[skillInfo.ability] + (profBonus * proficiencyLevel);
            fields[skillInfo.field] = formatModifier(value);
            if (proficiencyLevel > 0) checkboxes.push(skillInfo.checkbox);
        });

        fields.Passive = String(10 + Number.parseInt(fields["Perception "], 10));

        const weaponFields = {
            1: ["Wpn Name", "Wpn1 AtkBonus", "Wpn1 Damage"],
            2: ["Wpn Name 2", "Wpn2 AtkBonus ", "Wpn2 Damage "],
            3: ["Wpn Name 3", "Wpn3 AtkBonus  ", "Wpn3 Damage "],
        };
        Object.entries(weaponFields).forEach(([index, pdfFields]) => {
            fields[pdfFields[0]] = textValue(`weapon_${index}_name`);
            fields[pdfFields[1]] = textValue(`weapon_${index}_bonus`);
            fields[pdfFields[2]] = textValue(`weapon_${index}_damage`);
        });

        const spellcastingClass = classes.find((entry) => spellcastingAbilityByClass[entry.name]);
        const spellcastingAbility = spellcastingAbilityByClass[spellcastingClass?.name];
        if (spellcastingAbility && spellcastingClass) {
            const spellModifier = modifiers[spellcastingAbility];
            fields["Spellcasting Class 2"] = spellcastingClass.label;
            fields["SpellcastingAbility 2"] = spellcastingAbility.toUpperCase();
            fields["SpellSaveDC  2"] = String(8 + profBonus + spellModifier);
            fields["SpellAtkBonus 2"] = formatModifier(profBonus + spellModifier);
        }

        return { fields, checkboxes };
    }

    async function addCharacterImage(pdfDocument, pdfForm) {
        const imageFile = form.elements.full_body_image?.files?.[0];
        if (!imageFile) return;

        const bitmap = await createImageBitmap(imageFile);
        const scale = Math.min(1, 1400 / bitmap.width, 1800 / bitmap.height);
        const canvas = document.createElement("canvas");
        canvas.width = Math.max(1, Math.round(bitmap.width * scale));
        canvas.height = Math.max(1, Math.round(bitmap.height * scale));
        const context = canvas.getContext("2d");
        context.fillStyle = "#ffffff";
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        bitmap.close();

        const imageBlob = await new Promise((resolve, reject) => {
            canvas.toBlob(
                (blob) => blob ? resolve(blob) : reject(new Error("No se pudo preparar la imagen.")),
                "image/jpeg",
                0.88
            );
        });
        const embeddedImage = await pdfDocument.embedJpg(await imageBlob.arrayBuffer());

        try {
            pdfForm.getButton("CHARACTER IMAGE").setImage(embeddedImage);
        } catch (error) {
            console.warn("No se pudo añadir la imagen a la ficha PDF.", error);
        }
    }

    async function generateCharacterPdf() {
        if (!window.PDFLib?.PDFDocument) {
            throw new Error("La herramienta de generación PDF no está disponible.");
        }

        const templateUrl = form.dataset.pdfTemplate;
        const response = await fetch(templateUrl, { cache: "no-store" });
        if (!response.ok) {
            throw new Error("No se pudo abrir la plantilla de personaje.");
        }

        const pdfDocument = await window.PDFLib.PDFDocument.load(
            await response.arrayBuffer()
        );
        const pdfForm = pdfDocument.getForm();
        const { fields, checkboxes } = buildPdfData();

        Object.entries(fields).forEach(([name, value]) => {
            try {
                pdfForm.getTextField(name).setText(pdfSafeText(value));
            } catch (error) {
                console.warn(`Campo PDF no disponible: ${name}`, error);
            }
        });

        checkboxes.forEach((name) => {
            try {
                pdfForm.getCheckBox(name).check();
            } catch (error) {
                console.warn(`Casilla PDF no disponible: ${name}`, error);
            }
        });

        await addCharacterImage(pdfDocument, pdfForm);
        pdfForm.updateFieldAppearances();

        return pdfDocument.save({
            addDefaultPage: false,
            useObjectStreams: false,
            updateFieldAppearances: false,
        });
    }

    function showGenerationError(message) {
        let alert = form.querySelector(".generationError");
        if (!alert) {
            alert = document.createElement("div");
            alert.className = "formAlert generationError";
            alert.setAttribute("role", "alert");
            form.insertBefore(alert, form.querySelector(".formActions"));
        }
        alert.textContent = message;
        alert.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    nextButton.addEventListener("click", () => {
        if (!validateStep(steps[currentStep])) return;
        showStep(currentStep + 1, { focusHeading: true });
    });

    previousButton.addEventListener("click", () => {
        showStep(currentStep - 1, { focusHeading: true });
    });

    progressButtons.forEach((button, index) => {
        button.addEventListener("click", () => {
            if (index > currentStep && !validateStep(steps[currentStep])) return;
            showStep(index, { focusHeading: true });
        });
    });

    form.querySelectorAll("input, select, textarea").forEach((field) => {
        field.addEventListener("input", () => {
            field.closest(".hasError")?.classList.remove("hasError");
        });
    });

    form.querySelectorAll(
        "[name^='ability_'], [name^='skill_'], input[name='saving_throw_proficiencies[]']"
    ).forEach((field) => {
        field.addEventListener("input", updateCalculatedValues);
        field.addEventListener("change", updateCalculatedValues);
    });

    form.querySelectorAll("input[name='saving_throw_proficiencies[]']").forEach((field) => {
        if (field.checked) savingThrowsTouched = true;
        field.addEventListener("change", () => {
            savingThrowsTouched = true;
        });
    });

    classLevelRows?.addEventListener("change", (event) => {
        const row = event.target.closest("[data-class-row]");
        if (!row) return;
        if (event.target.matches("[data-class-select]")) {
            updateSubclassRow(row, "");
            if (row === classRows()[0]) {
                updateClassHint();
                savingThrowsTouched = false;
                syncInitialSavingThrows();
            }
        }
        updateTotalLevel();
    });
    classLevelRows?.addEventListener("input", (event) => {
        const row = event.target.closest("[data-class-row]");
        if (!row || !event.target.matches("[data-class-level]")) return;
        updateSubclassRow(
            row,
            row.querySelector("[data-subclass-select]")?.value || ""
        );
        updateTotalLevel();
    });
    classLevelRows?.addEventListener("click", (event) => {
        const removeButton = event.target.closest("[data-remove-class]");
        if (!removeButton) return;
        removeButton.closest("[data-class-row]")?.remove();
        updateTotalLevel();
    });
    addClassButton?.addEventListener("click", addClassRow);

    raceSelect?.addEventListener("change", () => {
        updateSubraceOptions("");
    });
    setupUploadPreviews();
    updateClassHint();
    classRows().forEach((row) => {
        updateSubclassRow(
            row,
            row.querySelector("[data-subclass-select]")?.value || ""
        );
    });
    if (
        classSelect?.value
        && !form.querySelector("input[name='saving_throw_proficiencies[]']:checked")
    ) {
        savingThrowsTouched = false;
        syncInitialSavingThrows();
    }
    updateSubraceOptions(form.dataset.selectedSubrace || "");
    updateTotalLevel();

    const serverErrorStep = steps.findIndex((step) => step.querySelector(".fieldError"));
    if (serverErrorStep >= 0) {
        highestVisitedStep = serverErrorStep;
        showStep(serverErrorStep, { markVisited: false });
    } else {
        showStep(0, { markVisited: false });
    }

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        if (isGenerating) return;

        const invalidStep = steps.findIndex((step) => firstInvalidField(step));
        if (invalidStep >= 0) {
            highestVisitedStep = Math.max(highestVisitedStep, invalidStep);
            showStep(invalidStep);
            validateStep(steps[invalidStep]);
            return;
        }

        isGenerating = true;
        submitButton.disabled = true;
        creationOverlay.hidden = false;
        form.querySelector(".generationError")?.remove();

        try {
            const pdfBytes = await generateCharacterPdf();
            const transfer = new DataTransfer();
            transfer.items.add(
                new File([pdfBytes], "ficha.pdf", { type: "application/pdf" })
            );
            generatedPdfInput.files = transfer.files;
            HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
            console.error("No se pudo generar la ficha de personaje.", error);
            showGenerationError(
                "No se pudo generar la ficha PDF. Revisa los datos y vuelve a intentarlo."
            );
            creationOverlay.hidden = true;
            submitButton.disabled = false;
            isGenerating = false;
        }
    });
});
