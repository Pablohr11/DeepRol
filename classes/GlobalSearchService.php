<?php

require_once(__DIR__ . "/CompendiumRepository.php");
require_once(__DIR__ . "/BestiaryLocalizer.php");

final class GlobalSearchService
{
    private const MIN_QUERY_LENGTH = 2;

    private $database;

    public function __construct($database = null)
    {
        $this->database = $database;
    }

    public function search(string $rawQuery, int $userId = 0, int $limitPerGroup = 6): array
    {
        $query = self::cleanQuery($rawQuery);
        $limitPerGroup = max(1, min(40, $limitPerGroup));
        $payload = [
            "query" => $query,
            "minimumLength" => self::MIN_QUERY_LENGTH,
            "groups" => [],
            "total" => 0,
        ];

        if (self::textLength($query) < self::MIN_QUERY_LENGTH) {
            return $payload;
        }

        if ($userId > 0) {
            $this->appendGroup(
                $payload,
                "characters",
                "Personajes",
                "Personajes",
                "PJ",
                $this->characterResults($query, $userId, $limitPerGroup + 1),
                $limitPerGroup
            );
        }

        $this->appendGroup(
            $payload,
            "spells",
            "Conjuros",
            "Habilidades",
            "CJ",
            $this->spellResults($query, $limitPerGroup + 1),
            $limitPerGroup
        );
        $this->appendGroup(
            $payload,
            "monsters",
            "Bestiario",
            "Bestiario",
            "BE",
            $this->monsterResults($query),
            $limitPerGroup
        );
        $this->appendGroup(
            $payload,
            "ancestries",
            "Razas y linajes",
            "Razas",
            "RA",
            $this->ancestryResults($query),
            $limitPerGroup
        );
        $this->appendGroup(
            $payload,
            "classes",
            "Clases y subclases",
            "Clases",
            "CL",
            $this->classResults($query),
            $limitPerGroup
        );

        if ($userId > 0) {
            $this->appendGroup(
                $payload,
                "notes",
                "Apuntes",
                "Apuntes",
                "AP",
                $this->noteResults($query, $userId, $limitPerGroup + 1),
                $limitPerGroup
            );
        }

        $this->appendGroup(
            $payload,
            "sections",
            "Secciones",
            "",
            "IR",
            $this->sectionResults($query),
            $limitPerGroup
        );

        return $payload;
    }

    public static function cleanQuery(string $query): string
    {
        $query = preg_replace('/[\x00-\x1F\x7F]+/u', " ", $query) ?? $query;
        $query = preg_replace('/\s+/u', " ", trim($query)) ?? trim($query);

        if (function_exists("mb_substr")) {
            return mb_substr($query, 0, 80, "UTF-8");
        }

        return substr($query, 0, 80);
    }

    private function characterResults(string $query, int $userId, int $limit): array
    {
        if (!$this->database || !method_exists($this->database, "searchCharacters")) {
            return [];
        }

        try {
            $rows = $this->database->searchCharacters($userId, $query, $limit);
        } catch (Throwable $exception) {
            error_log("DeepRol global character search: " . $exception->getMessage());
            return [];
        }

        $results = [];
        foreach ((array) $rows as $row) {
            $name = trim((string) ($row["name"] ?? "Personaje"));
            $race = trim(implode(" · ", array_filter([
                (string) ($row["raza"] ?? ""),
                (string) ($row["subraza"] ?? ""),
            ])));
            $class = trim(implode(" / ", array_filter([
                (string) ($row["clase"] ?? ""),
                (string) ($row["subclase"] ?? ""),
            ])));
            $level = max(1, (int) ($row["nivel"] ?? 1));

            $results[] = $this->result(
                $query,
                $name,
                $race !== "" ? $race : "Personaje",
                $class !== "" ? $class . " · Nivel " . $level : "Nivel " . $level,
                "personaje.php?id=" . (int) ($row["id_char"] ?? 0)
            );
        }

        return $results;
    }

    private function spellResults(string $query, int $limit): array
    {
        if (!$this->database || !method_exists($this->database, "searchSpells")) {
            return [];
        }

        try {
            $rows = $this->database->searchSpells($query, $limit);
        } catch (Throwable $exception) {
            error_log("DeepRol global spell search: " . $exception->getMessage());
            return [];
        }

        $results = [];
        foreach ((array) $rows as $row) {
            $name = trim((string) ($row["name"] ?? "Conjuro"));
            $level = trim((string) ($row["level"] ?? "Nivel desconocido"));
            $school = trim((string) ($row["escuela"] ?? "Escuela desconocida"));
            $classes = trim((string) ($row["clases"] ?? ""));

            $results[] = $this->result(
                $query,
                $name,
                implode(" · ", array_filter([$level, $school])),
                $classes !== "" ? $classes : self::excerpt((string) ($row["descr"] ?? "")),
                "spell.php?id_spell=" . (int) ($row["id_spell"] ?? 0)
            );
        }

        return $results;
    }

    private function noteResults(string $query, int $userId, int $limit): array
    {
        if (!$this->database || !method_exists($this->database, "searchNotes")) {
            return [];
        }

        try {
            $rows = $this->database->searchNotes($userId, $query, $limit);
        } catch (Throwable $exception) {
            error_log("DeepRol global note search: " . $exception->getMessage());
            return [];
        }

        $results = [];
        foreach ((array) $rows as $row) {
            $title = trim((string) ($row["Nombre"] ?? "Apunte sin título"));
            $character = trim((string) ($row["character_name"] ?? ""));
            $date = trim((string) ($row["Date"] ?? ""));

            $results[] = $this->result(
                $query,
                $title,
                implode(" · ", array_filter([$character, $date])),
                self::excerpt((string) ($row["Value"] ?? "")),
                "note.php?id=" . (int) ($row["ID"] ?? 0) . "&framed=false"
            );
        }

        return $results;
    }

    private function monsterResults(string $query): array
    {
        $results = [];
        foreach (CompendiumRepository::monsters() as $monster) {
            $name = BestiaryLocalizer::name($monster);
            $originalName = (string) ($monster["name"] ?? "");
            $type = BestiaryLocalizer::type((string) ($monster["type"] ?? ""));
            $size = BestiaryLocalizer::size((string) ($monster["size"] ?? ""));
            $challenge = (string) ($monster["challengeRating"] ?? "0");
            $haystack = implode(" ", [
                $name,
                $originalName,
                $type,
                $size,
                BestiaryLocalizer::subtype((string) ($monster["subtype"] ?? "")),
                BestiaryLocalizer::alignment((string) ($monster["alignment"] ?? "")),
                "desafío " . $challenge,
            ]);

            if (!$this->matches($query, $haystack)) {
                continue;
            }

            $results[] = $this->result(
                $query,
                $name,
                $type . " · VD " . $challenge,
                $size . " · " . BestiaryLocalizer::alignment(
                    (string) ($monster["alignment"] ?? "")
                ),
                "bestiario.php?q=" . rawurlencode($name)
            );
        }

        return $results;
    }

    private function ancestryResults(string $query): array
    {
        $results = [];

        foreach (CompendiumRepository::playableRaces() as $race) {
            $name = (string) ($race["label"] ?? $race["name"] ?? "Linaje");
            $source = (string) ($race["source"] ?? "Fuente oficial");
            $subraces = is_array($race["subraces"] ?? null) ? $race["subraces"] : [];
            $raceHaystack = implode(" ", array_merge(
                [$name, (string) ($race["name"] ?? ""), $source, "jugable"],
                array_column($subraces, "name")
            ));

            if ($this->matches($query, $raceHaystack)) {
                $results[] = $this->result(
                    $query,
                    $name,
                    "Raza o linaje jugable",
                    $source . " · " . count($subraces) . " variantes",
                    "razas.php?q=" . rawurlencode($name)
                );
            }

            foreach ($subraces as $subrace) {
                $subraceName = (string) ($subrace["name"] ?? "Variante");
                $subraceSource = (string) ($subrace["source"] ?? $source);
                if (!$this->matches($query, $subraceName . " " . $subraceSource)) {
                    continue;
                }

                $results[] = $this->result(
                    $query,
                    $subraceName,
                    "Variante de " . $name,
                    $subraceSource,
                    "razas.php?q=" . rawurlencode($subraceName)
                );
            }
        }

        foreach (CompendiumRepository::nonPlayableAncestries() as $ancestry) {
            $name = (string) ($ancestry["name"] ?? "Linaje");
            $category = (string) ($ancestry["category"] ?? "Referencia de mundo");
            $sources = array_map("strval", (array) ($ancestry["sources"] ?? []));
            $variants = array_map("strval", (array) ($ancestry["variants"] ?? []));
            $haystack = implode(" ", array_merge(
                [$name, $category, (string) ($ancestry["summary"] ?? "")],
                $sources,
                $variants
            ));

            if (!$this->matches($query, $haystack)) {
                continue;
            }

            $results[] = $this->result(
                $query,
                $name,
                $category . " · Referencia de mundo",
                self::excerpt((string) ($ancestry["summary"] ?? "")),
                "razas.php?q=" . rawurlencode($name)
            );
        }

        return $results;
    }

    private function classResults(string $query): array
    {
        $results = [];
        foreach (CompendiumRepository::classes() as $class) {
            $name = (string) ($class["label"] ?? $class["name"] ?? "Clase");
            $description = (string) ($class["description"] ?? "");
            $subclasses = is_array($class["subclasses"] ?? null)
                ? $class["subclasses"]
                : [];
            $classHaystack = implode(" ", array_merge(
                [$name, (string) ($class["name"] ?? ""), $description],
                array_column($subclasses, "name")
            ));

            if ($this->matches($query, $classHaystack)) {
                $results[] = $this->result(
                    $query,
                    $name,
                    "Clase · Subclase a nivel " . max(1, (int) ($class["subclassLevel"] ?? 1)),
                    $description,
                    "clases.php?q=" . rawurlencode($name)
                );
            }

            foreach ($subclasses as $subclass) {
                $subclassName = (string) ($subclass["name"] ?? "Subclase");
                $source = (string) ($subclass["source"] ?? "Fuente oficial");
                if (!$this->matches($query, $subclassName . " " . $source)) {
                    continue;
                }

                $results[] = $this->result(
                    $query,
                    $subclassName,
                    "Subclase de " . $name,
                    $source,
                    "clases.php?q=" . rawurlencode($subclassName)
                );
            }
        }

        return $results;
    }

    private function sectionResults(string $query): array
    {
        $sections = [
            ["Inicio", "Resumen de personajes, conjuros y actividad", "home.php", "Home"],
            ["Personajes", "Compañía, fichas y creación de héroes", "personajes.php", "Personajes"],
            ["Partidas", "Campañas y sesiones de juego", "partidas.php", "Partidas"],
            ["Conjuros", "Grimorio, escuelas y niveles", "allSpells.php", "Habilidades"],
            ["Bestiario", "Criaturas y estadísticas en castellano", "bestiario.php", "Bestiario"],
            ["Razas y linajes", "Razas, subrazas y pueblos del multiverso", "razas.php", "Razas"],
            ["Clases y subclases", "Caminos y arquetipos de personaje", "clases.php", "Clases"],
            ["Apuntes", "Notas generales y diarios de personaje", "notes.php", "Apuntes"],
            ["Ajustes", "Preferencias de la aplicación", "test.php", "Ajustes"],
        ];
        $results = [];

        foreach ($sections as $section) {
            if (!$this->matches($query, $section[0] . " " . $section[1])) {
                continue;
            }

            $results[] = $this->result(
                $query,
                $section[0],
                "Sección de DeepRol",
                $section[1],
                $section[2],
                $section[3]
            );
        }

        return $results;
    }

    private function result(
        string $query,
        string $title,
        string $meta,
        string $excerpt,
        string $path,
        string $page = ""
    ): array {
        return [
            "title" => $title,
            "meta" => $meta,
            "excerpt" => self::excerpt($excerpt),
            "path" => $path,
            "page" => $page,
            "score" => $this->score($query, $title . " " . $meta . " " . $excerpt),
        ];
    }

    private function appendGroup(
        array &$payload,
        string $key,
        string $label,
        string $page,
        string $badge,
        array $results,
        int $limit
    ): void {
        if (!$results) {
            return;
        }

        usort($results, static function (array $first, array $second): int {
            $scoreDifference = (int) ($first["score"] ?? 99)
                - (int) ($second["score"] ?? 99);
            if ($scoreDifference !== 0) {
                return $scoreDifference;
            }

            return strnatcasecmp(
                (string) ($first["title"] ?? ""),
                (string) ($second["title"] ?? "")
            );
        });

        $deduplicated = [];
        foreach ($results as $result) {
            $identity = (string) ($result["path"] ?? "")
                . "|" . (string) ($result["title"] ?? "");
            if (!isset($deduplicated[$identity])) {
                $deduplicated[$identity] = $result;
            }
        }

        $hasMore = count($deduplicated) > $limit;
        $visibleResults = array_slice(array_values($deduplicated), 0, $limit);
        foreach ($visibleResults as &$result) {
            unset($result["score"]);
            if (($result["page"] ?? "") === "") {
                $result["page"] = $page;
            }
            $result["badge"] = $badge;
        }
        unset($result);

        $payload["groups"][] = [
            "key" => $key,
            "label" => $label,
            "page" => $page,
            "hasMore" => $hasMore,
            "results" => $visibleResults,
        ];
        $payload["total"] += count($visibleResults);
    }

    private function matches(string $query, string $haystack): bool
    {
        return strpos(self::normalise($haystack), self::normalise($query)) !== false;
    }

    private function score(string $query, string $haystack): int
    {
        $needle = self::normalise($query);
        $normalizedHaystack = self::normalise($haystack);
        if ($normalizedHaystack === $needle) {
            return 0;
        }
        if (strpos($normalizedHaystack, $needle) === 0) {
            return 1;
        }
        return strpos($normalizedHaystack, " " . $needle) !== false ? 2 : 3;
    }

    private static function normalise(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES, "UTF-8");
        $ascii = function_exists("iconv")
            ? @iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $value)
            : false;
        $normalized = $ascii !== false ? $ascii : $value;

        return strtolower(trim(preg_replace('/\s+/', " ", $normalized) ?? $normalized));
    }

    private static function excerpt(string $value, int $limit = 140): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES, "UTF-8");
        $value = trim(preg_replace('/\s+/u', " ", $value) ?? $value);
        if ($value === "") {
            return "";
        }

        if (self::textLength($value) <= $limit) {
            return $value;
        }

        if (function_exists("mb_substr")) {
            return rtrim(mb_substr($value, 0, $limit - 1, "UTF-8")) . "…";
        }

        return rtrim(substr($value, 0, $limit - 3)) . "...";
    }

    private static function textLength(string $value): int
    {
        return function_exists("mb_strlen")
            ? mb_strlen($value, "UTF-8")
            : strlen($value);
    }
}
