<?php

require_once __DIR__ . "/CharacterProgression.php";

final class CharacterSheetUpdater
{
    private const FIELD_LIMITS = [
        "Background" => 120,
        "PlayerName" => 120,
        "Alignment" => 80,
        "XP" => 12,
        "Inspiration" => 12,
        "AC" => 4,
        "Initiative" => 5,
        "Speed" => 4,
        "HPMax" => 5,
        "HPCurrent" => 5,
        "HPTemp" => 5,
        "STR" => 2,
        "DEX" => 2,
        "CON" => 2,
        "INT" => 2,
        "WIS" => 2,
        "CHA" => 2,
        "ST Strength" => 5,
        "ST Dexterity" => 5,
        "ST Constitution" => 5,
        "ST Intelligence" => 5,
        "ST Wisdom" => 5,
        "ST Charisma" => 5,
        "Acrobatics" => 5,
        "Animal" => 5,
        "Arcana" => 5,
        "Athletics" => 5,
        "Deception " => 5,
        "History " => 5,
        "Insight" => 5,
        "Intimidation" => 5,
        "Investigation " => 5,
        "Medicine" => 5,
        "Nature" => 5,
        "Perception " => 5,
        "Performance" => 5,
        "Persuasion" => 5,
        "Religion" => 5,
        "SleightofHand" => 5,
        "Stealth " => 5,
        "Survival" => 5,
        "PersonalityTraits " => 3000,
        "Ideals" => 3000,
        "Bonds" => 3000,
        "Flaws" => 3000,
        "AttacksSpellcasting" => 5000,
        "ProficienciesLang" => 5000,
        "Equipment" => 5000,
        "Features and Traits" => 5000,
        "CP" => 8,
        "SP" => 8,
        "EP" => 8,
        "GP" => 8,
        "PP" => 8,
        "Age" => 80,
        "Height" => 80,
        "Weight" => 80,
        "Eyes" => 80,
        "Skin" => 80,
        "Hair" => 80,
        "Backstory" => 12000,
        "Allies" => 5000,
        "FactionName" => 160,
        "Feat+Traits" => 5000,
        "Treasure" => 5000,
        "Wpn Name" => 160,
        "Wpn1 AtkBonus" => 40,
        "Wpn1 Damage" => 120,
        "Wpn Name 2" => 160,
        "Wpn2 AtkBonus " => 40,
        "Wpn2 Damage " => 120,
        "Wpn Name 3" => 160,
        "Wpn3 AtkBonus  " => 40,
        "Wpn3 Damage " => 120,
    ];

    private const INTEGER_RANGES = [
        "XP" => [0, 999999999],
        "AC" => [0, 40],
        "Speed" => [0, 300],
        "HPMax" => [1, 9999],
        "HPCurrent" => [0, 9999],
        "HPTemp" => [0, 9999],
        "STR" => [1, 30],
        "DEX" => [1, 30],
        "CON" => [1, 30],
        "INT" => [1, 30],
        "WIS" => [1, 30],
        "CHA" => [1, 30],
        "CP" => [0, 99999999],
        "SP" => [0, 99999999],
        "EP" => [0, 99999999],
        "GP" => [0, 99999999],
        "PP" => [0, 99999999],
    ];

    private const MODIFIER_FIELDS = [
        "ST Strength",
        "ST Dexterity",
        "ST Constitution",
        "ST Intelligence",
        "ST Wisdom",
        "ST Charisma",
        "Acrobatics",
        "Animal",
        "Arcana",
        "Athletics",
        "Deception ",
        "History ",
        "Insight",
        "Intimidation",
        "Investigation ",
        "Medicine",
        "Nature",
        "Perception ",
        "Performance",
        "Persuasion",
        "Religion",
        "SleightofHand",
        "Stealth ",
        "Survival",
    ];

    private const ABILITY_MODIFIER_FIELDS = [
        "STR" => "STRmod",
        "DEX" => "DEXmod ",
        "CON" => "CONmod",
        "INT" => "INTmod",
        "WIS" => "WISmod",
        "CHA" => "CHamod",
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
        "Artifice" => "INT",
        "Bardo" => "CHA",
        "Brujo" => "CHA",
        "Clerigo" => "WIS",
        "Druida" => "WIS",
        "Explorador" => "WIS",
        "Hechicero" => "CHA",
        "Mago" => "INT",
        "Paladin" => "CHA",
    ];

    public static function decodeSubmittedFields(string $json): array
    {
        if ($json === "" || strlen($json) > 250000) {
            throw new InvalidArgumentException("Los datos de la ficha no son válidos.");
        }

        $fields = json_decode($json, true);
        if (!is_array($fields)) {
            throw new InvalidArgumentException("No se han podido leer los campos de la ficha.");
        }

        return self::sanitizeFields($fields);
    }

    public static function decodePdfFields(string $json): array
    {
        if ($json === "" || strlen($json) > 250000) {
            return [];
        }

        $fields = json_decode($json, true);
        if (!is_array($fields)) {
            throw new InvalidArgumentException("No se han podido leer los campos del PDF.");
        }

        return self::sanitizePdfFields($fields);
    }

    public static function sanitizeFields(array $fields): array
    {
        $sanitized = [];

        foreach (self::FIELD_LIMITS as $name => $limit) {
            if (!array_key_exists($name, $fields) || is_array($fields[$name])) {
                continue;
            }

            $value = trim((string) $fields[$name]);
            if (isset(self::INTEGER_RANGES[$name])) {
                if (!preg_match("/^-?\d+$/", $value)) {
                    throw new InvalidArgumentException("El campo {$name} debe contener un número válido.");
                }

                $number = (int) $value;
                [$minimum, $maximum] = self::INTEGER_RANGES[$name];
                if ($number < $minimum || $number > $maximum) {
                    throw new InvalidArgumentException("El valor del campo {$name} está fuera del rango permitido.");
                }
                $value = (string) $number;
            } elseif (
                in_array($name, self::MODIFIER_FIELDS, true)
                && $value !== ""
                && !preg_match("/^[+-]?\d{1,3}$/", $value)
            ) {
                throw new InvalidArgumentException("El modificador de {$name} no es válido.");
            }

            $sanitized[$name] = self::limitText($value, $limit);
        }

        return $sanitized;
    }

    public static function compose(
        array $existing,
        array $submitted,
        array $identity
    ): array {
        $fields = array_merge($existing, $submitted);
        $level = max(1, min(20, (int) $identity["level"]));
        $className = (string) $identity["class_name"];
        $subclassName = trim((string) $identity["subclass_name"]);
        $subraceName = trim((string) $identity["subrace_name"]);

        $fields["CharacterName"] = (string) $identity["character_name"];
        $fields["CharacterName 2"] = (string) $identity["character_name"];
        $fields["Race "] = (string) $identity["race_label"]
            . ($subraceName !== "" ? " · " . $subraceName : "");

        $classes = CharacterProgression::normalizeClasses(
            isset($identity["classes"]) && is_array($identity["classes"])
                ? $identity["classes"]
                : [[
                    "class_name" => $className,
                    "class_label" => (string) $identity["class_label"],
                    "subclass_name" => $subclassName,
                    "level" => $level,
                    "is_primary" => true,
                ]]
        );
        $totalLevel = CharacterProgression::totalLevel($classes);
        $savingThrowProficiencies = isset($identity["saving_throw_proficiencies"])
            && is_array($identity["saving_throw_proficiencies"])
                ? $identity["saving_throw_proficiencies"]
                : CharacterProgression::inferSavingThrowProficiencies($existing);
        $skillProficiencies = isset($identity["skill_proficiencies"])
            && is_array($identity["skill_proficiencies"])
                ? $identity["skill_proficiencies"]
                : CharacterProgression::inferSkillProficiencies($existing ?: $submitted, $totalLevel);
        $languages = isset($identity["languages"]) && is_array($identity["languages"])
            ? $identity["languages"]
            : (array) ($existing["_languages"] ?? []);

        if (array_key_exists("other_proficiencies", $identity)) {
            $fields["_otherProficiencies"] = trim((string) $identity["other_proficiencies"]);
        }

        return CharacterProgression::deriveSheetFields(
            $fields,
            $classes,
            $savingThrowProficiencies,
            $skillProficiencies,
            $languages
        );
    }

    public static function sanitizePdfFields(array $fields): array
    {
        $sanitized = self::sanitizeFields($fields);

        if (isset($fields["_pdfCheckboxes"]) && is_array($fields["_pdfCheckboxes"])) {
            $sanitized["_pdfCheckboxes"] = array_values(array_unique(array_filter(
                array_map("strval", $fields["_pdfCheckboxes"]),
                static function (string $name): bool {
                    return (bool) preg_match("/^Check Box \d{1,3}$/", $name);
                }
            )));
        }

        return $sanitized;
    }

    public static function writeJson(string $path, array $fields): void
    {
        $json = json_encode(
            $fields,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if ($json === false || file_put_contents($path, $json, LOCK_EX) === false) {
            throw new RuntimeException("No se ha podido guardar la ficha local.");
        }
    }

    private static function limitText(string $value, int $limit): string
    {
        if (function_exists("mb_substr")) {
            return mb_substr($value, 0, $limit, "UTF-8");
        }

        return substr($value, 0, $limit);
    }

    private static function formatModifier(int $value): string
    {
        return ($value >= 0 ? "+" : "") . $value;
    }
}
