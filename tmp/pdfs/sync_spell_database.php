<?php

declare(strict_types=1);

const SPELL_FIELDS = [
    "name",
    "descr",
    "duracion",
    "concentracion",
    "casteo",
    "level",
    "rango",
    "clases",
    "escuela",
];

function normalizeCanonical(string $value): string
{
    $value = str_replace(["’", "‘", "`", "´"], "'", trim($value));
    return mb_strtolower($value, "UTF-8");
}

function canonicalFromStoredName(string $name): string
{
    if (preg_match('/\(([^()]*)\)\s*$/u', $name, $matches) === 1) {
        return normalizeCanonical($matches[1]);
    }
    return normalizeCanonical($name);
}

function loadRows(PDO $pdo): array
{
    return $pdo
        ->query("SELECT * FROM conjuros ORDER BY id_spell")
        ->fetchAll(PDO::FETCH_ASSOC);
}

function indexByCanonical(array $rows): array
{
    $indexed = [];
    foreach ($rows as $row) {
        $key = canonicalFromStoredName((string) $row["name"]);
        $indexed[$key][] = $row;
    }
    return $indexed;
}

function rowDifferences(array $current, array $desired): array
{
    $differences = [];
    foreach (SPELL_FIELDS as $field) {
        if ((string) $current[$field] !== (string) $desired[$field]) {
            $differences[] = $field;
        }
    }
    return $differences;
}

$apply = in_array("--apply", $argv, true);
$catalogPath = __DIR__ . DIRECTORY_SEPARATOR . "catalog_updates.json";
$reportPath = __DIR__ . DIRECTORY_SEPARATOR . "db_update_report.json";

if (!is_file($catalogPath)) {
    throw new RuntimeException("No existe el catálogo generado: {$catalogPath}");
}

$catalog = json_decode(
    (string) file_get_contents($catalogPath),
    true,
    512,
    JSON_THROW_ON_ERROR
);

$pdo = new PDO(
    "mysql:host=localhost;dbname=deeprol;charset=utf8mb4",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$beforeRows = loadRows($pdo);
$beforeIndex = indexByCanonical($beforeRows);
$coreNameRepairs = [
    8 => [
        "before" => "Trucos Druidaas (Druidacraft)",
        "after" => "Magia druídica (Druidcraft)",
    ],
    105 => ["before" => "Rayo de bruja", "after" => "Virote encantado (Witch Bolt)"],
    106 => ["before" => "Golpe iracundo", "after" => "Golpe iracundo (Wrathful Smite)"],
    173 => ["before" => "Zona de verdad", "after" => "Zona de verdad (Zone of Truth)"],
    214 => ["before" => "", "after" => "Revivir (Revivify)"],
    242 => [
        "before" => "Adivinación (Adivinacion)",
        "after" => "Adivinación (Divination)",
    ],
    353 => ["before" => "Palabra de retorno", "after" => "Palabra de retorno (Word of Recall)"],
    368 => [
        "before" => "Resurrección verdadera (True Resurrection)",
        "after" => "Resurrección (Resurrection)",
    ],
    369 => ["before" => "... (continúa)", "after" => "Invertir gravedad (Reverse Gravity)"],
];
$coreDelete = [
    "id_spell" => 393,
    "name" => "Atrapando el alma (Trap the Soul)",
];

$beforeById = [];
foreach ($beforeRows as $row) {
    $beforeById[(int) $row["id_spell"]] = $row;
}
foreach ($coreNameRepairs as $id => $repair) {
    if (
        !isset($beforeById[$id])
        || (string) $beforeById[$id]["name"] !== $repair["before"]
    ) {
        throw new RuntimeException(
            "La reparación prevista para el conjuro {$id} no coincide con el estado local"
        );
    }
}
if (
    !isset($beforeById[$coreDelete["id_spell"]])
    || (string) $beforeById[$coreDelete["id_spell"]]["name"] !== $coreDelete["name"]
) {
    throw new RuntimeException("La fila no canónica Trap the Soul no coincide con lo esperado");
}
$resurrectionCandidates = array_values(
    array_filter(
        $beforeRows,
        static fn(array $row): bool =>
            canonicalFromStoredName((string) $row["name"]) ===
                normalizeCanonical("True Resurrection")
            && (string) $row["level"] === "Nivel 7"
    )
);

if (count($resurrectionCandidates) !== 1) {
    throw new RuntimeException(
        "Se esperaba exactamente una Resurrección verdadera incorrecta de nivel 7; " .
        "se encontraron " . count($resurrectionCandidates)
    );
}

$predicted = [
    "renamed_resurrection_id" => (int) $resurrectionCandidates[0]["id_spell"],
    "inserted" => [],
    "updated" => [],
];

foreach ($catalog as $desired) {
    $key = normalizeCanonical((string) $desired["canonical_name"]);
    $matches = $beforeIndex[$key] ?? [];
    if (count($matches) > 1) {
        throw new RuntimeException(
            "Hay más de una fila local para el nombre canónico {$desired["canonical_name"]}"
        );
    }
    if ($matches === []) {
        $predicted["inserted"][] = $desired["name"];
        continue;
    }
    $differences = rowDifferences($matches[0], $desired);
    if ($differences !== []) {
        $predicted["updated"][] = [
            "id_spell" => (int) $matches[0]["id_spell"],
            "name_before" => $matches[0]["name"],
            "name_after" => $desired["name"],
            "fields" => $differences,
        ];
    }
}

if (!$apply) {
    echo json_encode(
        [
            "mode" => "dry-run",
            "rows_before" => count($beforeRows),
            "predicted" => [
                "core_name_repairs" => count($coreNameRepairs),
                "noncanonical_deletes" => 1,
                "updates" => count($predicted["updated"]),
                "inserts" => count($predicted["inserted"]),
                "rows_after" =>
                    count($beforeRows) - 1 + count($predicted["inserted"]),
            ],
            "inserted_names" => $predicted["inserted"],
        ],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    ) . PHP_EOL;
    exit(0);
}

$backupTable = "conjuros_backup_" . date("Ymd_His");
if (preg_match('/^[a-z0-9_]+$/', $backupTable) !== 1) {
    throw new RuntimeException("Nombre de copia no válido");
}

$pdo->exec("CREATE TABLE `{$backupTable}` LIKE `conjuros`");
$pdo->exec("INSERT INTO `{$backupTable}` SELECT * FROM `conjuros`");
$backupCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM `{$backupTable}`"
)->fetchColumn();
if ($backupCount !== count($beforeRows)) {
    throw new RuntimeException(
        "La copia contiene {$backupCount} filas y la original " .
        count($beforeRows)
    );
}

$actual = [
    "renamed_resurrection_id" => null,
    "core_name_repairs" => [],
    "deleted" => [],
    "inserted" => [],
    "updated" => [],
];

try {
    $pdo->beginTransaction();

    $rename = $pdo->prepare(
        "UPDATE conjuros SET name = :name WHERE id_spell = :id"
    );
    foreach ($coreNameRepairs as $id => $repair) {
        $rename->execute([":name" => $repair["after"], ":id" => $id]);
        $actual["core_name_repairs"][] = [
            "id_spell" => $id,
            "name_before" => $repair["before"],
            "name_after" => $repair["after"],
        ];
    }
    $actual["renamed_resurrection_id"] = 368;

    $spellsetReference = $pdo->prepare(
        "SELECT COUNT(*)
         FROM spellset
         WHERE FIND_IN_SET(:id, REPLACE(spells, ' ', '')) > 0"
    );
    $spellsetReference->execute([":id" => (string) $coreDelete["id_spell"]]);
    if ((int) $spellsetReference->fetchColumn() !== 0) {
        throw new RuntimeException(
            "No se puede retirar Trap the Soul porque está asignado a un personaje"
        );
    }
    $delete = $pdo->prepare(
        "DELETE FROM conjuros WHERE id_spell = :id AND name = :name"
    );
    $delete->execute([
        ":id" => $coreDelete["id_spell"],
        ":name" => $coreDelete["name"],
    ]);
    if ($delete->rowCount() !== 1) {
        throw new RuntimeException("No se pudo retirar la fila no canónica Trap the Soul");
    }
    $actual["deleted"][] = $coreDelete;

    $currentRows = loadRows($pdo);
    $currentIndex = indexByCanonical($currentRows);
    $update = $pdo->prepare(
        "UPDATE conjuros SET
            name = :name,
            descr = :descr,
            duracion = :duracion,
            concentracion = :concentracion,
            casteo = :casteo,
            level = :level,
            rango = :rango,
            clases = :clases,
            escuela = :escuela
        WHERE id_spell = :id_spell"
    );
    $insert = $pdo->prepare(
        "INSERT INTO conjuros
            (name, descr, duracion, concentracion, casteo, level, rango, clases, escuela)
        VALUES
            (:name, :descr, :duracion, :concentracion, :casteo, :level, :rango, :clases, :escuela)"
    );

    foreach ($catalog as $desired) {
        $key = normalizeCanonical((string) $desired["canonical_name"]);
        $matches = $currentIndex[$key] ?? [];
        $parameters = [];
        foreach (SPELL_FIELDS as $field) {
            $parameters[":{$field}"] = (string) $desired[$field];
        }

        if ($matches === []) {
            $insert->execute($parameters);
            $newId = (int) $pdo->lastInsertId();
            $actual["inserted"][] = [
                "id_spell" => $newId,
                "name" => $desired["name"],
                "source" => $desired["source"],
            ];
            $desired["id_spell"] = $newId;
            $currentIndex[$key] = [$desired];
            continue;
        }

        if (count($matches) !== 1) {
            throw new RuntimeException(
                "El nombre canónico {$desired["canonical_name"]} dejó de ser único"
            );
        }
        $differences = rowDifferences($matches[0], $desired);
        if ($differences === []) {
            continue;
        }

        $parameters[":id_spell"] = (int) $matches[0]["id_spell"];
        $update->execute($parameters);
        $actual["updated"][] = [
            "id_spell" => (int) $matches[0]["id_spell"],
            "name_before" => $matches[0]["name"],
            "name_after" => $desired["name"],
            "fields" => $differences,
            "source" => $desired["source"],
        ];
    }

    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}

$afterRows = loadRows($pdo);
$duplicateNames = $pdo->query(
    "SELECT name, COUNT(*) AS total
     FROM conjuros
     GROUP BY name
     HAVING COUNT(*) > 1
     ORDER BY name"
)->fetchAll();

$report = [
    "applied_at" => date(DATE_ATOM),
    "backup_table" => $backupTable,
    "backup_rows" => $backupCount,
    "rows_before" => count($beforeRows),
    "rows_after" => count($afterRows),
    "renamed_resurrection_id" => $actual["renamed_resurrection_id"],
    "core_name_repair_count" => count($actual["core_name_repairs"]),
    "deleted_count" => count($actual["deleted"]),
    "updated_count" => count($actual["updated"]),
    "inserted_count" => count($actual["inserted"]),
    "duplicates_after" => $duplicateNames,
    "updated" => $actual["updated"],
    "core_name_repairs" => $actual["core_name_repairs"],
    "deleted" => $actual["deleted"],
    "inserted" => $actual["inserted"],
];

file_put_contents(
    $reportPath,
    json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode(
    [
        "mode" => "apply",
        "backup_table" => $backupTable,
        "backup_rows" => $backupCount,
        "rows_before" => count($beforeRows),
        "rows_after" => count($afterRows),
        "core_name_repairs" => count($actual["core_name_repairs"]),
        "deleted" => count($actual["deleted"]),
        "updated" => count($actual["updated"]),
        "inserted" => count($actual["inserted"]),
        "duplicates_after" => count($duplicateNames),
        "report" => $reportPath,
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
