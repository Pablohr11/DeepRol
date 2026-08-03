<?php

final class CharacterOptionCatalog
{
    private static $catalog;

    public static function all(): array
    {
        if (is_array(self::$catalog)) {
            return self::$catalog;
        }

        $catalogPath = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . "data"
            . DIRECTORY_SEPARATOR . "character-options.json";
        $catalogJson = is_readable($catalogPath)
            ? file_get_contents($catalogPath)
            : false;
        $catalog = $catalogJson === false
            ? null
            : json_decode($catalogJson, true);

        if (
            !is_array($catalog)
            || !isset($catalog["classes"], $catalog["races"])
            || !is_array($catalog["classes"])
            || !is_array($catalog["races"])
        ) {
            throw new RuntimeException("El catálogo de clases y razas no es válido.");
        }

        self::$catalog = $catalog;
        return self::$catalog;
    }

    public static function classes(): array
    {
        return self::all()["classes"];
    }

    public static function races(): array
    {
        return self::all()["races"];
    }

    public static function findClass(string $className): ?array
    {
        return self::findByName(self::classes(), $className);
    }

    public static function findRace(string $raceName): ?array
    {
        return self::findByName(self::races(), $raceName);
    }

    public static function hasNamedOption(array $options, string $name): bool
    {
        return self::findByName($options, $name) !== null;
    }

    private static function findByName(array $options, string $name): ?array
    {
        foreach ($options as $option) {
            if (
                is_array($option)
                && isset($option["name"])
                && hash_equals((string) $option["name"], $name)
            ) {
                return $option;
            }
        }

        return null;
    }
}
