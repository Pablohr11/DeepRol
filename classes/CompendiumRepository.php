<?php

require_once(__DIR__ . "/CharacterOptionCatalog.php");

final class CompendiumRepository
{
    private static $cache = [];

    public static function monsters(): array
    {
        $payload = self::loadJson("bestiary-srd.json");
        return is_array($payload["monsters"] ?? null)
            ? $payload["monsters"]
            : [];
    }

    public static function playableRaces(): array
    {
        return CharacterOptionCatalog::races();
    }

    public static function nonPlayableAncestries(): array
    {
        $payload = self::loadJson("nonplayable-ancestries.json");
        return is_array($payload["entries"] ?? null)
            ? $payload["entries"]
            : [];
    }

    public static function classes(): array
    {
        return CharacterOptionCatalog::classes();
    }

    public static function sourceBooks(): array
    {
        $books = self::loadJson("source-books.json");
        return is_array($books) ? $books : [];
    }

    public static function sourceBookMap(): array
    {
        $map = [];
        foreach (self::sourceBooks() as $book) {
            if (isset($book["key"])) {
                $map[(string) $book["key"]] = $book;
            }
        }

        return $map;
    }

    private static function loadJson(string $filename): array
    {
        if (isset(self::$cache[$filename])) {
            return self::$cache[$filename];
        }

        $path = dirname(__DIR__)
            . DIRECTORY_SEPARATOR . "data"
            . DIRECTORY_SEPARATOR . basename($filename);
        $json = is_readable($path) ? file_get_contents($path) : false;
        $decoded = $json === false ? null : json_decode($json, true);

        if (!is_array($decoded)) {
            throw new RuntimeException(
                "No se pudo leer el catálogo " . basename($filename) . "."
            );
        }

        self::$cache[$filename] = $decoded;
        return self::$cache[$filename];
    }
}
