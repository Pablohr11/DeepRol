<?php

declare(strict_types=1);

$catalog = json_decode(
    (string) file_get_contents(__DIR__ . "/catalog_updates.json"),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$report = json_decode(
    (string) file_get_contents(__DIR__ . "/db_update_report.json"),
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
    ]
);

$fields = [
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
$rows = $pdo->query("SELECT * FROM conjuros ORDER BY id_spell")->fetchAll();
$byCanonical = [];
foreach ($rows as $row) {
    if (preg_match('/\(([^()]*)\)\s*$/u', $row["name"], $matches) === 1) {
        $canonical = $matches[1];
    } else {
        $canonical = $row["name"];
    }
    $canonical = mb_strtolower(
        str_replace(["’", "‘", "`", "´"], "'", trim($canonical)),
        "UTF-8"
    );
    $byCanonical[$canonical][] = $row;
}

$catalogErrors = [];
foreach ($catalog as $expected) {
    $key = mb_strtolower(
        str_replace(["’", "‘", "`", "´"], "'", $expected["canonical_name"]),
        "UTF-8"
    );
    $matches = $byCanonical[$key] ?? [];
    if (count($matches) !== 1) {
        $catalogErrors[] = [
            "canonical_name" => $expected["canonical_name"],
            "problem" => "coincidencias=" . count($matches),
        ];
        continue;
    }
    foreach ($fields as $field) {
        if ((string) $matches[0][$field] !== (string) $expected[$field]) {
            $catalogErrors[] = [
                "canonical_name" => $expected["canonical_name"],
                "problem" => "campo {$field}",
            ];
        }
    }
}

$duplicates = $pdo->query(
    "SELECT name, COUNT(*) AS total
     FROM conjuros
     GROUP BY name
     HAVING COUNT(*) > 1"
)->fetchAll();
$blankNames = (int) $pdo->query(
    "SELECT COUNT(*) FROM conjuros WHERE TRIM(name) = ''"
)->fetchColumn();
$backupTable = (string) $report["backup_table"];
if (preg_match('/^[a-z0-9_]+$/', $backupTable) !== 1) {
    throw new RuntimeException("Nombre de copia inválido en el informe");
}
$backupRows = (int) $pdo->query(
    "SELECT COUNT(*) FROM `{$backupTable}`"
)->fetchColumn();
$trapTheSoul = (int) $pdo->query(
    "SELECT COUNT(*) FROM conjuros WHERE name LIKE '%(Trap the Soul)'"
)->fetchColumn();
$permanentImage = (int) $pdo->query(
    "SELECT COUNT(*) FROM conjuros WHERE name LIKE '%(Permanent Image)'"
)->fetchColumn();
$levels = $pdo->query(
    "SELECT level, COUNT(*) AS total
     FROM conjuros
     GROUP BY level
     ORDER BY FIELD(level, 'Truco', 'Nivel 1', 'Nivel 2', 'Nivel 3',
                    'Nivel 4', 'Nivel 5', 'Nivel 6', 'Nivel 7',
                    'Nivel 8', 'Nivel 9')"
)->fetchAll();
$coreRows = $pdo->query(
    "SELECT id_spell, name, level, escuela
     FROM conjuros
     WHERE id_spell IN (8, 105, 106, 173, 214, 242, 353, 368, 369)
     ORDER BY id_spell"
)->fetchAll();

$result = [
    "rows" => count($rows),
    "unique_names" => count(array_unique(array_column($rows, "name"))),
    "blank_names" => $blankNames,
    "duplicate_names" => $duplicates,
    "catalog_records_checked" => count($catalog),
    "catalog_errors" => $catalogErrors,
    "backup_table" => $backupTable,
    "backup_rows" => $backupRows,
    "trap_the_soul_rows" => $trapTheSoul,
    "permanent_image_rows" => $permanentImage,
    "levels" => $levels,
    "core_rows" => $coreRows,
    "roll20_reconciliations" => [
        [
            "id_spell" => 214,
            "name" => "Revivir (Revivify)",
            "field" => "escuela",
            "value" => "Nigromancia",
        ],
    ],
];

file_put_contents(
    __DIR__ . "/db_final_verification.json",
    json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode(
    $result,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
) . PHP_EOL;

if (
    count($rows) !== 477
    || count(array_unique(array_column($rows, "name"))) !== 477
    || $blankNames !== 0
    || $duplicates !== []
    || $catalogErrors !== []
    || $backupRows !== 410
    || $trapTheSoul !== 0
    || $permanentImage !== 0
    || ($byCanonical["revivify"][0]["escuela"] ?? null) !== "Nigromancia"
) {
    exit(1);
}
