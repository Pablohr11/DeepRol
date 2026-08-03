<?php

require_once(__DIR__ . "/../classes/DbConnector.php");
require_once(__DIR__ . "/../classes/GlobalSearchService.php");

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, max-age=0");
header("X-Content-Type-Options: nosniff");

$query = isset($_GET["q"]) ? (string) $_GET["q"] : "";
$userId = isset($_COOKIE["logged"]) ? max(0, (int) $_COOKIE["logged"]) : 0;

try {
    $database = null;
    try {
        $database = DbConector::singleton();
    } catch (Throwable $databaseException) {
        error_log("DeepRol global search database: " . $databaseException->getMessage());
    }

    $search = new GlobalSearchService($database);
    $payload = $search->search($query, $userId, 5);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
} catch (Throwable $exception) {
    error_log("DeepRol global search endpoint: " . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        "query" => GlobalSearchService::cleanQuery($query),
        "minimumLength" => 2,
        "groups" => [],
        "total" => 0,
        "error" => "No se pudo completar la búsqueda.",
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
