<?php
$sheetValue = static function (string $field, string $fallback = "") use ($sheetFallback): string {
    return (string) ($sheetFallback[$field] ?? $fallback);
};

$abilityFields = [
    "STR" => "Fuerza",
    "DEX" => "Destreza",
    "CON" => "Constitución",
    "INT" => "Inteligencia",
    "WIS" => "Sabiduría",
    "CHA" => "Carisma",
];
$saveFields = [
    "str" => "Fuerza",
    "dex" => "Destreza",
    "con" => "Constitución",
    "int" => "Inteligencia",
    "wis" => "Sabiduría",
    "cha" => "Carisma",
];
$skillFields = CharacterProgression::skills();
$storyFields = [
    "PersonalityTraits " => ["Rasgos de personalidad", 3],
    "Ideals" => ["Ideales", 3],
    "Bonds" => ["Vínculos", 3],
    "Flaws" => ["Defectos", 3],
    "AttacksSpellcasting" => ["Ataques y lanzamiento de conjuros", 4],
    "Equipment" => ["Equipo", 4],
    "Features and Traits" => ["Rasgos y características", 5],
    "Backstory" => ["Historia", 7],
    "Allies" => ["Aliados y organizaciones", 4],
    "Feat+Traits" => ["Rasgos adicionales", 4],
    "Treasure" => ["Tesoro", 4],
];
$classOptions = $characterCatalog["classes"];
$raceOptions = $characterCatalog["races"];
$languageOptions = CharacterProgression::languages();
?>

<div class="characterUpdateModal" id="characterUpdateModal" hidden>
    <button
        type="button"
        class="characterUpdateBackdrop"
        data-close-character-update
        aria-label="Cerrar actualización de ficha"
    ></button>
    <section
        class="characterUpdateDialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="characterUpdateTitle"
    >
        <header class="characterUpdateHeader">
            <div>
                <span class="panelKicker">Ficha de personaje</span>
                <h2 id="characterUpdateTitle">Actualizar <?= e($characterName) ?></h2>
                <p>Edita los datos guardados o importa una ficha PDF más reciente.</p>
            </div>
            <button
                type="button"
                class="characterUpdateClose"
                data-close-character-update
                aria-label="Cerrar"
            >×</button>
        </header>

        <nav class="characterUpdateTabs" aria-label="Opciones de actualización">
            <button type="button" class="isActive" data-update-tab="general">General y combate</button>
            <button type="button" data-update-tab="details">Competencias y detalles</button>
            <button type="button" data-update-tab="pdf">Importar PDF</button>
        </nav>

        <div class="characterUpdateBody">
            <form
                id="characterFieldsForm"
                action="../src/updateCharacterSheet.php"
                method="post"
                enctype="multipart/form-data"
            >
                <input type="hidden" name="character_id" value="<?= $charId ?>">
                <input type="hidden" name="csrf_token" value="<?= e($updateCsrfToken) ?>">
                <input type="hidden" name="action" value="fields">

                <div class="characterUpdatePanel isActive" data-update-panel="general">
                    <section class="updateFormSection">
                        <div class="updateSectionHeading">
                            <div>
                                <span>Identidad</span>
                                <h3>Clase, nivel y linaje</h3>
                            </div>
                            <p>Estos datos recalculan el tema, el bono de competencia y los espacios de conjuro.</p>
                        </div>

                        <div class="updateFormGrid">
                            <label class="updateField">
                                <span>Personaje</span>
                                <input type="text" value="<?= e($characterName) ?>" disabled>
                                <small>El nombre identifica la carpeta de la ficha y no se modifica aquí.</small>
                            </label>
                            <div class="updateField updateMulticlassBuilder">
                                <div class="updateMulticlassHeading">
                                    <span>Clases y niveles</span>
                                    <strong>Nivel total <b id="updateTotalLevel"><?= $characterLevel ?></b>/20</strong>
                                </div>
                                <div id="updateClassRows" class="updateClassRows">
                                    <?php foreach ($characterClasses as $classIndex => $characterClass): ?>
                                        <?php $rowClassOption = CharacterOptionCatalog::findClass($characterClass["class_name"]); ?>
                                        <div class="updateClassRow" data-update-class-row>
                                            <label class="updateField">
                                                <span><?= $classIndex === 0 ? "Clase inicial" : "Clase adicional" ?></span>
                                                <select
                                                    name="class_names[]"
                                                    <?= $classIndex === 0 ? 'id="updateClassSelect"' : "" ?>
                                                    data-update-class-select
                                                    required
                                                >
                                                    <?php foreach ($classOptions as $classOption): ?>
                                                        <option
                                                            value="<?= e($classOption["name"]) ?>"
                                                            <?= $classOption["name"] === $characterClass["class_name"] ? "selected" : "" ?>
                                                        ><?= e($classOption["label"] ?? $classOption["name"]) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>
                                            <label class="updateField">
                                                <span>Nivel de clase</span>
                                                <input
                                                    type="number"
                                                    name="class_levels[]"
                                                    data-update-class-level
                                                    value="<?= (int) $characterClass["level"] ?>"
                                                    min="1"
                                                    max="20"
                                                    required
                                                >
                                            </label>
                                            <label class="updateField" data-update-subclass-field>
                                                <span>Subclase</span>
                                                <select name="subclass_names[]" data-update-subclass-select>
                                                    <option value="">Sin subclase</option>
                                                    <?php foreach (($rowClassOption["subclasses"] ?? []) as $subclassOption): ?>
                                                        <option
                                                            value="<?= e($subclassOption["name"]) ?>"
                                                            <?= $subclassOption["name"] === $characterClass["subclass_name"] ? "selected" : "" ?>
                                                        ><?= e($subclassOption["name"]) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small data-update-subclass-hint></small>
                                            </label>
                                            <?php if ($classIndex > 0): ?>
                                                <button type="button" class="removeClassButton" data-update-remove-class aria-label="Eliminar esta clase">×</button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="addClassButton" id="updateAddClass">+ Añadir multiclase</button>
                                <small>Las mejoras, subclases y dados de golpe usan el nivel de cada clase.</small>
                            </div>
                            <label class="updateField">
                                <span>Raza o linaje</span>
                                <select name="race_name" id="updateRaceSelect" required>
                                    <?php foreach ($raceOptions as $raceOption): ?>
                                        <option
                                            value="<?= e($raceOption["name"]) ?>"
                                            <?= $raceOption["name"] === $characterRaceName ? "selected" : "" ?>
                                        ><?= e($raceOption["label"] ?? $raceOption["name"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="updateField">
                                <span>Subraza o variante</span>
                                <select name="subrace_name" id="updateSubraceSelect">
                                    <option value="">Sin subraza</option>
                                    <?php foreach (($characterRaceOption["subraces"] ?? []) as $subraceOption): ?>
                                        <option
                                            value="<?= e($subraceOption["name"]) ?>"
                                            <?= $subraceOption["name"] === $characterSubraceName ? "selected" : "" ?>
                                        ><?= e($subraceOption["name"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="updateField">
                                <span>Trasfondo</span>
                                <input
                                    type="text"
                                    data-sheet-field="Background"
                                    value="<?= e($sheetValue("Background")) ?>"
                                    maxlength="120"
                                >
                            </label>
                            <label class="updateField">
                                <span>Jugador</span>
                                <input
                                    type="text"
                                    data-sheet-field="PlayerName"
                                    value="<?= e($sheetValue("PlayerName")) ?>"
                                    maxlength="120"
                                >
                            </label>
                            <label class="updateField">
                                <span>Alineamiento</span>
                                <input
                                    type="text"
                                    data-sheet-field="Alignment"
                                    value="<?= e($sheetValue("Alignment")) ?>"
                                    maxlength="80"
                                >
                            </label>
                            <label class="updateField">
                                <span>Experiencia</span>
                                <input
                                    type="number"
                                    data-sheet-field="XP"
                                    value="<?= e($sheetValue("XP", "0")) ?>"
                                    min="0"
                                    max="999999999"
                                >
                            </label>
                        </div>
                    </section>

                    <section class="updateFormSection">
                        <div class="updateSectionHeading">
                            <div>
                                <span>Combate</span>
                                <h3>Estado actual</h3>
                            </div>
                        </div>
                        <div class="updateFormGrid updateFormGridCompact">
                            <label class="updateField">
                                <span>Clase de armadura</span>
                                <input type="number" data-sheet-field="AC" value="<?= e($sheetValue("AC", "10")) ?>" min="0" max="40">
                            </label>
                            <label class="updateField">
                                <span>Vida máxima</span>
                                <input type="number" data-sheet-field="HPMax" value="<?= e($sheetValue("HPMax", "1")) ?>" min="1" max="9999">
                            </label>
                            <label class="updateField">
                                <span>Vida actual</span>
                                <input type="number" data-sheet-field="HPCurrent" value="<?= e($sheetValue("HPCurrent", $sheetValue("HPMax", "1"))) ?>" min="0" max="9999">
                            </label>
                            <label class="updateField">
                                <span>Vida temporal</span>
                                <input type="number" data-sheet-field="HPTemp" value="<?= e($sheetValue("HPTemp", "0")) ?>" min="0" max="9999">
                            </label>
                            <label class="updateField">
                                <span>Iniciativa</span>
                                <input type="text" data-sheet-field="Initiative" value="<?= e($sheetValue("Initiative", "+0")) ?>" maxlength="5">
                            </label>
                            <label class="updateField">
                                <span>Velocidad</span>
                                <input type="number" data-sheet-field="Speed" value="<?= e($sheetValue("Speed", "30")) ?>" min="0" max="300">
                            </label>
                            <label class="updateField">
                                <span>Inspiración</span>
                                <input type="text" data-sheet-field="Inspiration" value="<?= e($sheetValue("Inspiration")) ?>" maxlength="12">
                            </label>
                        </div>
                    </section>

                    <section class="updateFormSection">
                        <div class="updateSectionHeading">
                            <div>
                                <span>Características</span>
                                <h3>Puntuaciones base</h3>
                            </div>
                            <p>Los modificadores se recalculan al guardar.</p>
                        </div>
                        <div class="updateAbilityGrid">
                            <?php foreach ($abilityFields as $field => $label): ?>
                                <label class="updateAbility">
                                    <span><?= e($label) ?></span>
                                    <input
                                        type="number"
                                        data-sheet-field="<?= e($field) ?>"
                                        value="<?= e($sheetValue($field, "10")) ?>"
                                        min="1"
                                        max="30"
                                        required
                                    >
                                    <strong data-update-ability-modifier="<?= e($field) ?>">+0</strong>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="characterUpdatePanel" data-update-panel="details" hidden>
                    <section class="updateFormSection">
                        <div class="updateSectionHeading">
                            <div>
                                <span>Competencias</span>
                                <h3>Salvaciones</h3>
                            </div>
                            <p>El modificador se calcula automáticamente con la característica y el nivel total.</p>
                        </div>
                        <div class="updateModifierGrid">
                            <?php foreach ($saveFields as $ability => $label): ?>
                                <label class="updateField updateModifierField">
                                    <span><?= e($label) ?></span>
                                    <input
                                        type="checkbox"
                                        name="saving_throw_proficiencies[]"
                                        value="<?= e($ability) ?>"
                                        <?= in_array($ability, $characterSavingThrowProficiencies, true) ? "checked" : "" ?>
                                    >
                                    <strong data-update-save-preview="<?= e($ability) ?>">+0</strong>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="updateFormSection">
                        <div class="updateSectionHeading">
                            <div>
                                <span>Competencias</span>
                                <h3>Habilidades</h3>
                            </div>
                        </div>
                        <div class="updateModifierGrid updateSkillsGrid">
                            <?php foreach ($skillFields as $skill => $skillInfo): ?>
                                <label class="updateField updateModifierField">
                                    <span><?= e($skillInfo["label"]) ?></span>
                                    <select name="skill_proficiencies[<?= e($skill) ?>]" data-update-skill="<?= e($skill) ?>">
                                        <?php $selectedSkillLevel = (int) ($characterSkillProficiencies[$skill] ?? 0); ?>
                                        <option value="0" <?= $selectedSkillLevel === 0 ? "selected" : "" ?>>Sin competencia</option>
                                        <option value="1" <?= $selectedSkillLevel === 1 ? "selected" : "" ?>>Competencia</option>
                                        <option value="2" <?= $selectedSkillLevel === 2 ? "selected" : "" ?>>Pericia</option>
                                    </select>
                                    <strong data-update-skill-preview="<?= e($skill) ?>">+0</strong>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="updateFormSection">
                        <div class="updateSectionHeading">
                            <div>
                                <span>Comunicación</span>
                                <h3>Idiomas y otras competencias</h3>
                            </div>
                            <p>Los idiomas se guardan de forma independiente y también aparecen en la ficha.</p>
                        </div>
                        <div class="updateLanguageGrid">
                            <?php foreach ($languageOptions as $language): ?>
                                <label class="choicePill">
                                    <input
                                        type="checkbox"
                                        name="languages[]"
                                        value="<?= e($language) ?>"
                                        <?= in_array($language, $characterLanguages, true) ? "checked" : "" ?>
                                    >
                                    <span><?= e($language) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <label class="updateField">
                            <span>Otros idiomas</span>
                            <input type="text" name="custom_languages" maxlength="1000" placeholder="Separados por comas">
                        </label>
                        <label class="updateField">
                            <span>Otras competencias</span>
                            <textarea name="other_proficiencies" rows="4"><?= e($characterOtherProficiencies) ?></textarea>
                        </label>
                    </section>

                    <section class="updateFormSection">
                        <div class="updateSectionHeading">
                            <div>
                                <span>Inventario</span>
                                <h3>Monedas</h3>
                            </div>
                        </div>
                        <div class="updateCoinsGrid">
                            <?php foreach (["CP" => "PC", "SP" => "PP", "EP" => "PE", "GP" => "PO", "PP" => "PPL"] as $field => $label): ?>
                                <label class="updateField">
                                    <span><?= e($label) ?></span>
                                    <input
                                        type="number"
                                        data-sheet-field="<?= e($field) ?>"
                                        value="<?= e($sheetValue($field, "0")) ?>"
                                        min="0"
                                        max="99999999"
                                    >
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="updateFormSection">
                        <div class="updateSectionHeading">
                            <div>
                                <span>Historia</span>
                                <h3>Descripción y contenido de la ficha</h3>
                            </div>
                        </div>
                        <div class="updateTextGrid">
                            <?php foreach ($storyFields as $field => [$label, $rows]): ?>
                                <label class="updateField">
                                    <span><?= e($label) ?></span>
                                    <textarea
                                        data-sheet-field="<?= e($field) ?>"
                                        rows="<?= (int) $rows ?>"
                                    ><?= e($sheetValue($field)) ?></textarea>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <footer class="characterUpdateActions">
                    <p class="characterUpdateResult" id="characterUpdateResult" role="status"></p>
                    <button type="button" class="secondaryUpdateButton" data-close-character-update>Cancelar</button>
                    <button type="submit" class="primaryUpdateButton">
                        <span aria-hidden="true">✓</span>
                        Guardar cambios
                    </button>
                </footer>
            </form>

            <div class="characterUpdatePanel" data-update-panel="pdf" hidden>
                <form
                    id="characterPdfForm"
                    class="characterPdfForm"
                    action="../src/updateCharacterSheet.php"
                    method="post"
                    enctype="multipart/form-data"
                >
                    <input type="hidden" name="character_id" value="<?= $charId ?>">
                    <input type="hidden" name="csrf_token" value="<?= e($updateCsrfToken) ?>">
                    <input type="hidden" name="action" value="pdf">

                    <label class="characterPdfDropzone" id="characterPdfDropzone">
                        <input
                            type="file"
                            name="updated_pdf"
                            id="updatedCharacterPdf"
                            accept="application/pdf,.pdf"
                            required
                        >
                        <span class="pdfDropzoneIcon" aria-hidden="true">⇧</span>
                        <strong>Selecciona la ficha actualizada</strong>
                        <span>PDF rellenable · máximo 20 MB</span>
                        <small id="characterPdfFileName">Ningún archivo seleccionado</small>
                    </label>

                    <div class="pdfImportSummary">
                        <span aria-hidden="true">i</span>
                        <div>
                            <strong id="pdfImportStatus">La ficha se analizará antes de subirla</strong>
                            <p>
                                Se importarán sus campos rellenables. La clase, nivel, raza y subraza
                                detectados se reflejarán en los selectores de la pestaña General.
                            </p>
                        </div>
                    </div>

                    <footer class="characterUpdateActions">
                        <p class="characterUpdateResult" id="characterPdfResult" role="status"></p>
                        <button type="button" class="secondaryUpdateButton" data-close-character-update>Cancelar</button>
                        <button type="submit" class="primaryUpdateButton">
                            <span aria-hidden="true">⇧</span>
                            Importar ficha
                        </button>
                    </footer>
                </form>
            </div>
        </div>
    </section>
</div>
