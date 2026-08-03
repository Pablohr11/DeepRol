<?php

require_once __DIR__ . "/../classes/GameRepository.php";
require_once __DIR__ . "/../classes/GameCommandService.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, max-age=0");
header("X-Content-Type-Options: nosniff");

function gameApiResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function gameApiPayload(): array
{
    $contentType = (string) ($_SERVER["CONTENT_TYPE"] ?? "");
    if (stripos($contentType, "application/json") !== false) {
        $decoded = json_decode((string) file_get_contents("php://input"), true);
        return is_array($decoded) ? $decoded : [];
    }
    return $_POST;
}

function gameApiRequireCsrf(array $payload): void
{
    $cookie = (string) ($_COOKIE["deeprol_game_csrf"] ?? "");
    $header = (string) ($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "");
    $posted = (string) ($payload["csrf_token"] ?? "");
    $candidate = $header !== "" ? $header : $posted;
    if (
        $cookie === ""
        || $candidate === ""
        || !preg_match("/^[a-f0-9]{48}$/", $cookie)
        || !hash_equals($cookie, $candidate)
    ) {
        gameApiResponse(["ok" => false, "error" => "La sesión del formulario ha caducado."], 403);
    }
}

$userId = isset($_COOKIE["logged"]) ? (int) $_COOKIE["logged"] : 0;
if ($userId <= 0) {
    gameApiResponse(["ok" => false, "error" => "Inicia sesión para acceder a la partida."], 401);
}

$action = (string) ($_GET["action"] ?? $_POST["action"] ?? "");
$payload = gameApiPayload();
if ($action === "" && isset($payload["action"])) {
    $action = (string) $payload["action"];
}

try {
    $repository = new GameRepository();

    if ($_SERVER["REQUEST_METHOD"] === "GET" && $action === "state") {
        $gameId = (int) ($_GET["game_id"] ?? 0);
        gameApiResponse([
            "ok" => true,
            "state" => $repository->getState($gameId, $userId),
        ]);
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        gameApiResponse(["ok" => false, "error" => "Método no permitido."], 405);
    }

    gameApiRequireCsrf($payload);

    if ($action === "create_game") {
        $game = $repository->createGame(
            $userId,
            (string) ($payload["name"] ?? ""),
            (string) ($payload["description"] ?? "")
        );
        gameApiResponse([
            "ok" => true,
            "game" => $game,
            "redirect" => "partida.php?id=" . (int) $game["id_game"],
        ], 201);
    }

    if ($action === "join_game") {
        $characterId = (int) ($payload["character_id"] ?? 0);
        $gameId = $repository->joinGame(
            $userId,
            (string) ($payload["invite_code"] ?? ""),
            $characterId > 0 ? $characterId : null
        );
        gameApiResponse([
            "ok" => true,
            "game_id" => $gameId,
            "redirect" => "partida.php?id=" . $gameId,
        ]);
    }

    if ($action === "command") {
        $gameId = (int) ($payload["game_id"] ?? 0);
        $command = (string) ($payload["command"] ?? "");
        $commandPayload = is_array($payload["payload"] ?? null)
            ? $payload["payload"]
            : [];
        $service = new GameCommandService($repository);
        gameApiResponse([
            "ok" => true,
            "result" => $service->handle(
                $gameId,
                $userId,
                $command,
                $commandPayload
            ),
        ]);
    }

    gameApiResponse(["ok" => false, "error" => "Acción no encontrada."], 404);
} catch (InvalidArgumentException $exception) {
    gameApiResponse(["ok" => false, "error" => $exception->getMessage()], 422);
} catch (RuntimeException $exception) {
    gameApiResponse(["ok" => false, "error" => $exception->getMessage()], 409);
} catch (Throwable $exception) {
    error_log("DeepRol gameApi: " . $exception->getMessage());
    gameApiResponse([
        "ok" => false,
        "error" => "No se ha podido completar la acción de la partida.",
    ], 500);
}
