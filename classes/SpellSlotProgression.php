<?php

final class SpellSlotProgression
{
    private const FULL_CASTER_SLOTS = [
        1 => [1 => 2],
        2 => [1 => 3],
        3 => [1 => 4, 2 => 2],
        4 => [1 => 4, 2 => 3],
        5 => [1 => 4, 2 => 3, 3 => 2],
        6 => [1 => 4, 2 => 3, 3 => 3],
        7 => [1 => 4, 2 => 3, 3 => 3, 4 => 1],
        8 => [1 => 4, 2 => 3, 3 => 3, 4 => 2],
        9 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 1],
        10 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2],
        11 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2, 6 => 1],
        12 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2, 6 => 1],
        13 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2, 6 => 1, 7 => 1],
        14 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2, 6 => 1, 7 => 1],
        15 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2, 6 => 1, 7 => 1, 8 => 1],
        16 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2, 6 => 1, 7 => 1, 8 => 1],
        17 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2, 6 => 1, 7 => 1, 8 => 1, 9 => 1],
        18 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 3, 6 => 1, 7 => 1, 8 => 1, 9 => 1],
        19 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 3, 6 => 2, 7 => 1, 8 => 1, 9 => 1],
        20 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 3, 6 => 2, 7 => 2, 8 => 1, 9 => 1],
    ];

    private const HALF_CASTER_SLOTS = [
        1 => [],
        2 => [1 => 2],
        3 => [1 => 3],
        4 => [1 => 3],
        5 => [1 => 4, 2 => 2],
        6 => [1 => 4, 2 => 2],
        7 => [1 => 4, 2 => 3],
        8 => [1 => 4, 2 => 3],
        9 => [1 => 4, 2 => 3, 3 => 2],
        10 => [1 => 4, 2 => 3, 3 => 2],
        11 => [1 => 4, 2 => 3, 3 => 3],
        12 => [1 => 4, 2 => 3, 3 => 3],
        13 => [1 => 4, 2 => 3, 3 => 3, 4 => 1],
        14 => [1 => 4, 2 => 3, 3 => 3, 4 => 1],
        15 => [1 => 4, 2 => 3, 3 => 3, 4 => 2],
        16 => [1 => 4, 2 => 3, 3 => 3, 4 => 2],
        17 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 1],
        18 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 1],
        19 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2],
        20 => [1 => 4, 2 => 3, 3 => 3, 4 => 3, 5 => 2],
    ];

    private const THIRD_CASTER_SLOTS = [
        1 => [],
        2 => [],
        3 => [1 => 2],
        4 => [1 => 3],
        5 => [1 => 3],
        6 => [1 => 3],
        7 => [1 => 4, 2 => 2],
        8 => [1 => 4, 2 => 2],
        9 => [1 => 4, 2 => 2],
        10 => [1 => 4, 2 => 3],
        11 => [1 => 4, 2 => 3],
        12 => [1 => 4, 2 => 3],
        13 => [1 => 4, 2 => 3, 3 => 2],
        14 => [1 => 4, 2 => 3, 3 => 2],
        15 => [1 => 4, 2 => 3, 3 => 2],
        16 => [1 => 4, 2 => 3, 3 => 3],
        17 => [1 => 4, 2 => 3, 3 => 3],
        18 => [1 => 4, 2 => 3, 3 => 3],
        19 => [1 => 4, 2 => 3, 3 => 3, 4 => 1],
        20 => [1 => 4, 2 => 3, 3 => 3, 4 => 1],
    ];

    private const FULL_CASTERS = [
        "bardo",
        "clerigo",
        "druida",
        "hechicero",
        "mago",
    ];

    private const HALF_CASTERS = [
        "explorador",
        "paladin",
    ];

    public static function forCharacter(
        string $className,
        int $level,
        string $subclassName = ""
    ): array
    {
        $level = max(1, min(20, $level));
        $normalisedClass = self::normaliseClassName($className);
        $normalisedSubclass = self::normaliseClassName($subclassName);

        if (in_array($normalisedClass, self::FULL_CASTERS, true)) {
            return [
                "type" => "standard",
                "slots" => self::FULL_CASTER_SLOTS[$level],
                "label" => "Lanzamiento de conjuros",
                "description" => "Espacios disponibles tras un descanso largo.",
                "arcanum" => [],
            ];
        }

        if (in_array($normalisedClass, ["artifice", "artificer"], true)) {
            $slots = $level === 1
                ? [1 => 2]
                : self::HALF_CASTER_SLOTS[$level];
            return [
                "type" => "standard",
                "slots" => $slots,
                "label" => "Lanzamiento de conjuros",
                "description" => "Espacios disponibles tras un descanso largo.",
                "arcanum" => [],
            ];
        }

        if (in_array($normalisedClass, self::HALF_CASTERS, true)) {
            $slots = self::HALF_CASTER_SLOTS[$level];
            return [
                "type" => "standard",
                "slots" => $slots,
                "label" => "Lanzamiento de conjuros",
                "description" => $slots
                    ? "Espacios disponibles tras un descanso largo."
                    : "Esta clase obtiene sus primeros espacios de conjuro en el nivel 2.",
                "arcanum" => [],
            ];
        }

        if ($normalisedClass === "brujo") {
            $pactSlotLevel = 1;
            $pactSlotCount = 1;

            if ($level >= 17) {
                $pactSlotLevel = 5;
                $pactSlotCount = 4;
            } elseif ($level >= 11) {
                $pactSlotLevel = 5;
                $pactSlotCount = 3;
            } elseif ($level >= 9) {
                $pactSlotLevel = 5;
                $pactSlotCount = 2;
            } elseif ($level >= 7) {
                $pactSlotLevel = 4;
                $pactSlotCount = 2;
            } elseif ($level >= 5) {
                $pactSlotLevel = 3;
                $pactSlotCount = 2;
            } elseif ($level >= 3) {
                $pactSlotLevel = 2;
                $pactSlotCount = 2;
            } elseif ($level >= 2) {
                $pactSlotCount = 2;
            }

            $arcanum = [];
            if ($level >= 11) {
                $arcanum[] = 6;
            }
            if ($level >= 13) {
                $arcanum[] = 7;
            }
            if ($level >= 15) {
                $arcanum[] = 8;
            }
            if ($level >= 17) {
                $arcanum[] = 9;
            }

            return [
                "type" => "pact",
                "slots" => [$pactSlotLevel => $pactSlotCount],
                "label" => "Magia de pacto",
                "description" => "Todos los espacios son del mismo nivel y se recuperan tras un descanso corto o largo.",
                "arcanum" => $arcanum,
            ];
        }

        $isThirdCaster = (
            $normalisedClass === "guerrero"
            && $normalisedSubclass === "caballero arcano"
        ) || (
            $normalisedClass === "picaro"
            && $normalisedSubclass === "tramposo arcano"
        );

        if ($isThirdCaster) {
            return [
                "type" => "standard",
                "slots" => self::THIRD_CASTER_SLOTS[$level],
                "label" => "Lanzamiento de conjuros",
                "description" => "Espacios concedidos por la subclase lanzadora.",
                "arcanum" => [],
            ];
        }

        $subclassMessage = in_array($normalisedClass, ["guerrero", "picaro"], true)
            ? "Esta clase solo obtiene espacios mediante una subclase lanzadora."
            : "Esta clase no utiliza espacios de conjuro estándar.";

        return [
            "type" => "none",
            "slots" => [],
            "label" => "Sin espacios de conjuro",
            "description" => $subclassMessage,
            "arcanum" => [],
        ];
    }

    public static function forClasses(array $classes): array
    {
        $entries = [];
        foreach ($classes as $class) {
            if (!is_array($class)) {
                continue;
            }

            $className = trim((string) ($class["class_name"] ?? $class["name"] ?? ""));
            $level = max(1, min(20, (int) ($class["level"] ?? $class["class_level"] ?? 1)));
            if ($className === "") {
                continue;
            }

            $entries[] = [
                "class_name" => $className,
                "subclass_name" => trim((string) ($class["subclass_name"] ?? $class["subclass"] ?? "")),
                "level" => $level,
            ];
        }

        if (!$entries) {
            $empty = self::forCharacter("", 1);
            $empty["groups"] = [];
            return $empty;
        }

        $standardCasters = [];
        $pactGroups = [];
        foreach ($entries as $entry) {
            $progression = self::forCharacter(
                $entry["class_name"],
                $entry["level"],
                $entry["subclass_name"]
            );
            $kind = self::multiclassCasterKind(
                $entry["class_name"],
                $entry["subclass_name"],
                $entry["level"]
            );

            if ($kind !== "") {
                $entry["kind"] = $kind;
                $standardCasters[] = $entry;
            }
            if ($progression["type"] === "pact") {
                $progression["key"] = "pact-" . count($pactGroups);
                $pactGroups[] = $progression;
            }
        }

        $groups = [];
        if (count($standardCasters) === 1) {
            $caster = $standardCasters[0];
            $standard = self::forCharacter(
                $caster["class_name"],
                $caster["level"],
                $caster["subclass_name"]
            );
            $standard["key"] = "standard";
            $groups[] = $standard;
        } elseif (count($standardCasters) > 1) {
            $casterLevel = 0;
            foreach ($standardCasters as $caster) {
                if ($caster["kind"] === "full") {
                    $casterLevel += $caster["level"];
                } elseif ($caster["kind"] === "artificer") {
                    $casterLevel += (int) ceil($caster["level"] / 2);
                } elseif ($caster["kind"] === "half") {
                    $casterLevel += (int) floor($caster["level"] / 2);
                } elseif ($caster["kind"] === "third") {
                    $casterLevel += (int) floor($caster["level"] / 3);
                }
            }

            $casterLevel = max(1, min(20, $casterLevel));
            $groups[] = [
                "key" => "standard",
                "type" => "standard",
                "slots" => self::FULL_CASTER_SLOTS[$casterLevel],
                "label" => "Espacios multiclase",
                "description" => "Espacios compartidos de lanzador de nivel " . $casterLevel . ", recuperados tras un descanso largo.",
                "arcanum" => [],
                "caster_level" => $casterLevel,
            ];
        }

        foreach ($pactGroups as $pactGroup) {
            $groups[] = $pactGroup;
        }

        if (!$groups) {
            $empty = self::forCharacter(
                $entries[0]["class_name"],
                $entries[0]["level"],
                $entries[0]["subclass_name"]
            );
            $empty["groups"] = [];
            return $empty;
        }

        if (count($groups) === 1) {
            $result = $groups[0];
            $result["groups"] = $groups;
            return $result;
        }

        return [
            "type" => "mixed",
            "slots" => $groups[0]["slots"],
            "label" => "Progresión mágica multiclase",
            "description" => "Los espacios compartidos y la magia de pacto se administran por separado.",
            "arcanum" => [],
            "groups" => $groups,
        ];
    }

    private static function multiclassCasterKind(
        string $className,
        string $subclassName,
        int $level
    ): string {
        $normalisedClass = self::normaliseClassName($className);
        $normalisedSubclass = self::normaliseClassName($subclassName);

        if (in_array($normalisedClass, self::FULL_CASTERS, true)) {
            return "full";
        }
        if (in_array($normalisedClass, ["artifice", "artificer"], true)) {
            return "artificer";
        }
        if (in_array($normalisedClass, self::HALF_CASTERS, true) && $level >= 2) {
            return "half";
        }
        if (
            $level >= 3
            && (
                ($normalisedClass === "guerrero" && $normalisedSubclass === "caballero arcano")
                || ($normalisedClass === "picaro" && $normalisedSubclass === "tramposo arcano")
            )
        ) {
            return "third";
        }

        return "";
    }

    private static function normaliseClassName(string $className): string
    {
        $normalised = function_exists("mb_strtolower")
            ? mb_strtolower(trim($className), "UTF-8")
            : strtolower(trim($className));

        return strtr($normalised, [
            "á" => "a",
            "é" => "e",
            "í" => "i",
            "ó" => "o",
            "ú" => "u",
            "ü" => "u",
        ]);
    }
}
