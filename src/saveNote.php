<?php
require_once("../classes/DbConnector.php");

header("Content-Type: application/json; charset=UTF-8");

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
$noteId = isset($_POST["noteId"]) ? (int) $_POST["noteId"] : 0;
$noteValue = isset($_POST["value"]) ? (string) $_POST["value"] : "";

if ($userId <= 0 || $noteId <= 0) {
    http_response_code(400);
    echo json_encode(["saved" => false]);
    exit;
}

try {
    $db = DbConector::singleton();
    $note = $db->getNote($noteId);

    if (!$note || (int) ($note["ID_User"] ?? 0) !== $userId) {
        http_response_code(403);
        echo json_encode(["saved" => false]);
        exit;
    }

    echo json_encode(["saved" => (bool) $db->saveNote($noteId, $noteValue)]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(["saved" => false]);
}
