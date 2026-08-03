<?php

final class CharacterProgression
{
    private const ABILITY_MODIFIER_FIELDS = [
        "str" => ["score" => "STR", "modifier" => "STRmod"],
        "dex" => ["score" => "DEX", "modifier" => "DEXmod "],
        "con" => ["score" => "CON", "modifier" => "CONmod"],
        "int" => ["score" => "INT", "modifier" => "INTmod"],
        "wis" => ["score" => "WIS", "modifier" => "WISmod"],
        "cha" => ["score" => "CHA", "modifier" => "CHamod"],
    ];

    private const SAVING_THROWS = [
        "str" => ["field" => "ST Strength", "checkbox" => "Check Box 11"],
        "dex" => ["field" => "ST Dexterity", "checkbox" => "Check Box 18"],
        "con" => ["field" => "ST Constitution", "checkbox" => "Check Box 19"],
        "int" => ["field" => "ST Intelligence", "checkbox" => "Check Box 20"],
        "wis" => ["field" => "ST Wisdom", "checkbox" => "Check Box 21"],
        "cha" => ["field" => "ST Charisma", "checkbox" => "Check Box 22"],
    ];

    private const SKILLS = [
        "acrobatics" => ["label" => "Acrobacias", "ability" => "dex", "field" => "Acrobatics", "checkbox" => "Check Box 23"],
        "animal" => ["label" => "Trato con animales", "ability" => "wis", "field" => "Animal", "checkbox" => "Check Box 24"],
        "arcana" => ["label" => "Arcano", "ability" => "int", "field" => "Arcana", "checkbox" => "Check Box 25"],
        "athletics" => ["label" => "Atletismo", "ability" => "str", "field" => "Athletics", "checkbox" => "Check Box 26"],
        "deception" => ["label" => "Engaño", "ability" => "cha", "field" => "Deception ", "checkbox" => "Check Box 27"],
        "history" => ["label" => "Historia", "ability" => "int", "field" => "History ", "checkbox" => "Check Box 28"],
        "insight" => ["label" => "Perspicacia", "ability" => "wis", "field" => "Insight", "checkbox" => "Check Box 29"],
        "intimidation" => ["label" => "Intimidación", "ability" => "cha", "field" => "Intimidation", "checkbox" => "Check Box 30"],
        "investigation" => ["label" => "Investigación", "ability" => "int", "field" => "Investigation ", "checkbox" => "Check Box 31"],
        "medicine" => ["label" => "Medicina", "ability" => "wis", "field" => "Medicine", "checkbox" => "Check Box 32"],
        "nature" => ["label" => "Naturaleza", "ability" => "int", "field" => "Nature", "checkbox" => "Check Box 33"],
        "perception" => ["label" => "Percepción", "ability" => "wis", "field" => "Perception ", "checkbox" => "Check Box 34"],
        "performance" => ["label" => "Interpretación", "ability" => "cha", "field" => "Performance", "checkbox" => "Check Box 35"],
        "persuasion" => ["label" => "Persuasión", "ability" => "cha", "field" => "Persuasion", "checkbox" => "Check Box 36"],
        "religion" => ["label" => "Religión", "ability" => "int", "field" => "Religion", "checkbox" => "Check Box 37"],
        "sleight_of_hand" => ["label" => "Juego de manos", "ability" => "dex", "field" => "SleightofHand", "checkbox" => "Check Box 38"],
        "stealth" => ["label" => "Sigilo", "ability" => "dex", "field" => "Stealth ", "checkbox" => "Check Box 39"],
        "survival" => ["label" => "Supervivencia", "ability" => "wis", "field" => "Survival", "checkbox" => "Check Box 40"],
    ];

    private const HIT_DICE = [
        "Artifice" => 8,
        "Barbaro" => 12,
        "Bardo" => 8,
        "Brujo" => 8,
        "Clerigo" => 8,
        "Druida" => 8,
        "Explorador" => 10,
        "Guerrero" => 10,
        "Hechicero" => 6,
        "Mago" => 6,
        "Monje" => 8,
        "Paladin" => 10,
        "Picaro" => 8,
    ];

    private const SPELLCASTING_ABILITIES = [
        "Artifice" => "int",
        "Bardo" => "cha",
        "Brujo" => "cha",
        "Clerigo" => "wis",
        "Druida" => "wis",
        "Explorador" => "wis",
        "Hechicero" => "cha",
        "Mago" => "int",
        "Paladin" => "cha",
    ];

    private const INITIAL_SAVING_THROWS = [
        "Artifice" => ["con", "int"],
        "Barbaro" => ["str", "con"],
        "Bardo" => ["dex", "cha"],
        "Brujo" => ["wis", "cha"],
        "Clerigo" => ["wis", "cha"],
        "Druida" => ["int", "wis"],
        "Explorador" => ["str", "dex"],
        "Guerrero" => ["str", "con"],
        "Hechicero" => ["con", "cha"],
        "Mago" => ["int", "wis"],
        "Monje" => ["str", "dex"],
        "Paladin" => ["wis", "cha"],
        "Picaro" => ["dex", "int"],
    ];

    private const STANDARD_ASI_LEVELS = [4, 8, 12, 16, 19];
    private const FIGHTER_ASI_LEVELS = [4, 6, 8, 12, 14, 16, 19];
    private const ROGUE_ASI_LEVELS = [4, 8, 10, 12, 16, 19];

    private const LANGUAGES = [
        "Común",
        "Enano",
        "Élfico",
        "Gigante",
        "Gnómico",
        "Goblin",
        "Mediano",
        "Orco",
        "Abisal",
        "Celestial",
        "Dracónico",
        "Habla profunda",
        "Infernal",
        "Primordial",
        "Silvano",
        "Infracomún",
    ];

    public static function savingThrows(): array
    {
        return self::SAVING_THROWS;
    }

    public static function skills(): array
    {
        return self::SKILLS;
    }

    public static function languages(): array
    {
        return self::LANGUAGES;
    }

    public static function initialSavingThrows(string $className): array
    {
        return self::INITIAL_SAVING_THROWS[$className] ?? [];
    }

    public static function normalizeClasses(array $classes): array
    {
        $normalized = [];

        foreach ($classes as $index => $class) {
            if (!is_array($class)) {
                continue;
            }

            $className = trim((string) ($class["class_name"] ?? $class["name"] ?? ""));
            $level = (int) ($class["level"] ?? $class["class_level"] ?? 0);
            if ($className === "" || $level < 1) {
                continue;
            }

            $normalized[] = [
                "class_name" => $className,
                "class_label" => trim((string) ($class["class_label"] ?? $class["label"] ?? $className)),
                "subclass_name" => trim((string) ($class["subclass_name"] ?? $class["subclass"] ?? "")),
                "level" => min(20, $level),
                "is_primary" => count($normalized) === 0 || !empty($class["is_primary"]),
                "sort_order" => count($normalized),
            ];
        }

        if ($normalized) {
            foreach ($normalized as $index => &$class) {
                $class["is_primary"] = $index === 0;
            }
            unset($class);
        }

        return $normalized;
    }

    public static function totalLevel(array $classes): int
    {
        return array_sum(array_map(
            static function (array $class): int {
                return max(0, (int) ($class["level"] ?? $class["class_level"] ?? 0));
            },
            self::normalizeClasses($classes)
        ));
    }

    public static function proficiencyBonus(int $totalLevel): int
    {
        $totalLevel = max(1, min(20, $totalLevel));
        return 2 + (int) floor(($totalLevel - 1) / 4);
    }

    public static function abilityModifier(int $score): int
    {
        return (int) floor(($score - 10) / 2);
    }

    public static function classSummary(array $classes): string
    {
        $parts = [];
        foreach (self::normalizeClasses($classes) as $class) {
            $parts[] = $class["class_label"]
                . ($class["subclass_name"] !== "" ? " · " . $class["subclass_name"] : "")
                . "/" . $class["level"];
        }

        return implode(" / ", $parts);
    }

    public static function hitDiceSummary(array $classes): string
    {
        $parts = [];
        foreach (self::normalizeClasses($classes) as $class) {
            $parts[] = $class["level"] . "d" . (self::HIT_DICE[$class["class_name"]] ?? 8);
        }

        return implode(" + ", $parts);
    }

    public static function abilityScoreImprovementCount(array $classes): int
    {
        $total = 0;
        foreach (self::normalizeClasses($classes) as $class) {
            $milestones = self::STANDARD_ASI_LEVELS;
            if ($class["class_name"] === "Guerrero") {
                $milestones = self::FIGHTER_ASI_LEVELS;
            } elseif ($class["class_name"] === "Picaro") {
                $milestones = self::ROGUE_ASI_LEVELS;
            }

            foreach ($milestones as $milestone) {
                if ($class["level"] >= $milestone) {
                    $total++;
                }
            }
        }

        return $total;
    }

    public static function normalizeSavingThrowProficiencies(array $values): array
    {
        return array_values(array_intersect(
            array_keys(self::SAVING_THROWS),
            array_values(array_unique(array_map("strval", $values)))
        ));
    }

    public static function normalizeSkillProficiencies(array $values): array
    {
        $normalized = [];
        foreach (self::SKILLS as $key => $skill) {
            $level = (int) ($values[$key] ?? 0);
            $normalized[$key] = in_array($level, [0, 1, 2], true) ? $level : 0;
        }

        return $normalized;
    }

    public static function inferSavingThrowProficiencies(array $fields): array
    {
        if (isset($fields["_savingThrowProficiencies"]) && is_array($fields["_savingThrowProficiencies"])) {
            return self::normalizeSavingThrowProficiencies($fields["_savingThrowProficiencies"]);
        }

        $checkboxes = array_map("strval", (array) ($fields["_pdfCheckboxes"] ?? []));
        $proficiencies = [];
        foreach (self::SAVING_THROWS as $ability => $definition) {
            if (in_array($definition["checkbox"], $checkboxes, true)) {
                $proficiencies[] = $ability;
            }
        }

        return $proficiencies;
    }

    public static function inferSkillProficiencies(array $fields, int $totalLevel): array
    {
        if (isset($fields["_skillProficiencies"]) && is_array($fields["_skillProficiencies"])) {
            return self::normalizeSkillProficiencies($fields["_skillProficiencies"]);
        }

        $checkboxes = array_map("strval", (array) ($fields["_pdfCheckboxes"] ?? []));
        $proficiency = self::proficiencyBonus($totalLevel);
        $result = [];

        foreach (self::SKILLS as $key => $definition) {
            $scoreField = self::ABILITY_MODIFIER_FIELDS[$definition["ability"]]["score"];
            $score = isset($fields[$scoreField]) && preg_match("/^\d+$/", (string) $fields[$scoreField])
                ? (int) $fields[$scoreField]
                : 10;
            $baseModifier = self::abilityModifier($score);
            $storedModifier = isset($fields[$definition["field"]])
                && preg_match("/^[+-]?\d+$/", (string) $fields[$definition["field"]])
                    ? (int) $fields[$definition["field"]]
                    : $baseModifier;
            $difference = $storedModifier - $baseModifier;

            if ($difference === $proficiency * 2) {
                $result[$key] = 2;
            } elseif (
                $difference === $proficiency
                || in_array($definition["checkbox"], $checkboxes, true)
            ) {
                $result[$key] = 1;
            } else {
                $result[$key] = 0;
            }
        }

        return $result;
    }

    public static function normalizeLanguages(array $selected, string $custom = ""): array
    {
        $languages = [];
        foreach ($selected as $language) {
            if (is_array($language)) {
                continue;
            }
            $language = self::limitText(trim((string) $language), 80);
            if ($language !== "") {
                $languages[] = $language;
            }
        }

        foreach (preg_split("/[,;\r\n]+/u", $custom) ?: [] as $language) {
            $language = self::limitText(trim($language), 80);
            if ($language !== "") {
                $languages[] = $language;
            }
        }

        $unique = [];
        foreach ($languages as $language) {
            $key = self::normaliseName($language);
            if ($key !== "" && !isset($unique[$key])) {
                $unique[$key] = $language;
            }
        }

        return array_slice(array_values($unique), 0, 30);
    }

    public static function proficienciesAndLanguages(string $other, array $languages): string
    {
        $other = trim((string) preg_replace(
            "/(?:\r?\n){0,2}Idiomas:\s*.*$/isu",
            "",
            trim($other)
        ));
        $languages = self::normalizeLanguages($languages);
        $parts = [];
        if ($other !== "") {
            $parts[] = $other;
        }
        if ($languages) {
            $parts[] = "Idiomas: " . implode(", ", $languages);
        }

        return implode("\n\n", $parts);
    }

    public static function deriveSheetFields(
        array $fields,
        array $classes,
        array $savingThrowProficiencies,
        array $skillProficiencies,
        array $languages = []
    ): array {
        $classes = self::normalizeClasses($classes);
        if (!$classes) {
            throw new InvalidArgumentException("El personaje necesita al menos una clase.");
        }

        $totalLevel = self::totalLevel($classes);
        if ($totalLevel < 1 || $totalLevel > 20) {
            throw new InvalidArgumentException("La suma de niveles de clase debe estar entre 1 y 20.");
        }

        $proficiency = self::proficiencyBonus($totalLevel);
        $savingThrowProficiencies = self::normalizeSavingThrowProficiencies($savingThrowProficiencies);
        $skillProficiencies = self::normalizeSkillProficiencies($skillProficiencies);
        $modifiers = [];

        foreach (self::ABILITY_MODIFIER_FIELDS as $ability => $definition) {
            $score = isset($fields[$definition["score"]]) && preg_match("/^\d+$/", (string) $fields[$definition["score"]])
                ? (int) $fields[$definition["score"]]
                : 10;
            $modifiers[$ability] = self::abilityModifier($score);
            $fields[$definition["modifier"]] = self::formatModifier($modifiers[$ability]);
        }

        $managedCheckboxes = [];
        foreach (self::SAVING_THROWS as $ability => $definition) {
            $isProficient = in_array($ability, $savingThrowProficiencies, true);
            $value = $modifiers[$ability] + ($isProficient ? $proficiency : 0);
            $fields[$definition["field"]] = self::formatModifier($value);
            $managedCheckboxes[] = $definition["checkbox"];
        }

        foreach (self::SKILLS as $key => $definition) {
            $proficiencyLevel = $skillProficiencies[$key];
            $value = $modifiers[$definition["ability"]] + ($proficiency * $proficiencyLevel);
            $fields[$definition["field"]] = self::formatModifier($value);
            $managedCheckboxes[] = $definition["checkbox"];
        }

        $checkboxes = array_values(array_filter(
            array_map("strval", (array) ($fields["_pdfCheckboxes"] ?? [])),
            static function (string $checkbox) use ($managedCheckboxes): bool {
                return !in_array($checkbox, $managedCheckboxes, true);
            }
        ));
        foreach (self::SAVING_THROWS as $ability => $definition) {
            if (in_array($ability, $savingThrowProficiencies, true)) {
                $checkboxes[] = $definition["checkbox"];
            }
        }
        foreach (self::SKILLS as $key => $definition) {
            if ($skillProficiencies[$key] > 0) {
                $checkboxes[] = $definition["checkbox"];
            }
        }

        $primaryClass = $classes[0];
        $fields["ClassLevel"] = self::classSummary($classes);
        $fields["ProfBonus"] = self::formatModifier($proficiency);
        $fields["HDTotal"] = (string) $totalLevel;
        $fields["HD"] = self::hitDiceSummary($classes);
        $fields["Passive"] = (string) (10 + (int) $fields["Perception "]);
        $fields["_pdfCheckboxes"] = array_values(array_unique($checkboxes));
        $fields["_characterClasses"] = $classes;
        $fields["_savingThrowProficiencies"] = $savingThrowProficiencies;
        $fields["_skillProficiencies"] = $skillProficiencies;
        $fields["_languages"] = self::normalizeLanguages($languages);
        $fields["_abilityScoreImprovements"] = self::abilityScoreImprovementCount($classes);

        $otherProficiencies = trim((string) (
            $fields["_otherProficiencies"] ?? $fields["ProficienciesLang"] ?? ""
        ));
        $fields["_otherProficiencies"] = (string) preg_replace(
            "/(?:\r?\n){0,2}Idiomas:\s*.*$/isu",
            "",
            $otherProficiencies
        );
        $fields["ProficienciesLang"] = self::proficienciesAndLanguages(
            $fields["_otherProficiencies"],
            $fields["_languages"]
        );

        foreach ($classes as $class) {
            $spellAbility = self::SPELLCASTING_ABILITIES[$class["class_name"]] ?? "";
            if ($spellAbility === "") {
                continue;
            }

            $fields["Spellcasting Class 2"] = $class["class_label"];
            $fields["SpellcastingAbility 2"] = strtoupper($spellAbility);
            $fields["SpellSaveDC  2"] = (string) (8 + $proficiency + $modifiers[$spellAbility]);
            $fields["SpellAtkBonus 2"] = self::formatModifier($proficiency + $modifiers[$spellAbility]);
            break;
        }

        return $fields;
    }

    private static function formatModifier(int $value): string
    {
        return ($value >= 0 ? "+" : "") . $value;
    }

    private static function normaliseName(string $value): string
    {
        $value = function_exists("mb_strtolower")
            ? mb_strtolower(trim($value), "UTF-8")
            : strtolower(trim($value));

        return strtr($value, [
            "á" => "a",
            "é" => "e",
            "í" => "i",
            "ó" => "o",
            "ú" => "u",
            "ü" => "u",
        ]);
    }

    private static function limitText(string $value, int $limit): string
    {
        if (function_exists("mb_substr")) {
            return mb_substr($value, 0, $limit, "UTF-8");
        }

        return substr($value, 0, $limit);
    }
}
