<?php
require_once __DIR__ . '/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

<<<<<<< Updated upstream
    require_once("../classes/DbConnector.php");
    $db = DbConector::singleton();
    $noteId = $_POST["noteId"];
    $noteValue = $_POST["value"];
    $db->saveNote($noteId, $noteValue);

    // echo($db->saveNote($noteId, $noteValue));


    return json_encode($db->saveNote($noteId, $noteValue))
?>
=======
$userId = current_user_id();
$noteId = filter_input(INPUT_POST, 'noteId', FILTER_VALIDATE_INT);
if (!$userId || !$noteId || !isset($_POST['value'])) {
    http_response_code($userId ? 422 : 401);
    echo json_encode(['ok' => false]);
    exit;
}

$saved = DbConector::singleton()->saveNoteForUser($noteId, $userId, (string) $_POST['value']);
echo json_encode(['ok' => $saved]);
>>>>>>> Stashed changes
