<?php

require_once __DIR__ . "/../classes/GameRepository.php";

function assertGameSocket(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function websocketClientFrame(array $payload): string
{
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $length = strlen($json);
    $frame = chr(0x81);
    if ($length <= 125) {
        $frame .= chr(0x80 | $length);
    } elseif ($length <= 65535) {
        $frame .= chr(0x80 | 126) . pack("n", $length);
    } else {
        $frame .= chr(0x80 | 127) . pack("NN", 0, $length);
    }
    $mask = random_bytes(4);
    $masked = "";
    for ($index = 0; $index < $length; $index++) {
        $masked .= $json[$index] ^ $mask[$index % 4];
    }
    return $frame . $mask . $masked;
}

function websocketReadExact($socket, int $length): string
{
    $buffer = "";
    while (strlen($buffer) < $length) {
        $chunk = fread($socket, $length - strlen($buffer));
        if ($chunk === false || $chunk === "") {
            throw new RuntimeException("La conexión WebSocket terminó antes de tiempo.");
        }
        $buffer .= $chunk;
    }
    return $buffer;
}

function websocketServerMessage($socket): array
{
    $header = websocketReadExact($socket, 2);
    $first = ord($header[0]);
    $second = ord($header[1]);
    assertGameSocket(($first & 0x0F) === 0x1, "El servidor no devolvió una trama de texto.");
    assertGameSocket(($second & 0x80) === 0, "El servidor no debe enmascarar sus tramas.");
    $length = $second & 0x7F;
    if ($length === 126) {
        $length = unpack("n", websocketReadExact($socket, 2))[1];
    } elseif ($length === 127) {
        $parts = unpack("Nhigh/Nlow", websocketReadExact($socket, 8));
        assertGameSocket((int) $parts["high"] === 0, "La trama de prueba es demasiado grande.");
        $length = (int) $parts["low"];
    }
    $decoded = json_decode(websocketReadExact($socket, $length), true);
    assertGameSocket(is_array($decoded), "El servidor devolvió JSON no válido.");
    return $decoded;
}

$port = isset($argv[1]) ? (int) $argv[1] : 18081;
$repository = new GameRepository();
$gameId = 0;
$socket = null;

try {
    $created = $repository->createGame(
        1,
        "Prueba de socket " . bin2hex(random_bytes(3))
    );
    $gameId = (int) $created["id_game"];
    $token = $repository->issueSocketToken($gameId, 1);

    $errorNumber = 0;
    $errorMessage = "";
    $socket = stream_socket_client(
        "tcp://127.0.0.1:" . $port,
        $errorNumber,
        $errorMessage,
        3
    );
    assertGameSocket((bool) $socket, "No se conectó al servidor: " . $errorMessage);
    stream_set_timeout($socket, 4);

    $key = base64_encode(random_bytes(16));
    fwrite(
        $socket,
        "GET / HTTP/1.1\r\n"
        . "Host: 127.0.0.1:{$port}\r\n"
        . "Upgrade: websocket\r\n"
        . "Connection: Upgrade\r\n"
        . "Sec-WebSocket-Key: {$key}\r\n"
        . "Sec-WebSocket-Version: 13\r\n"
        . "Origin: http://localhost\r\n\r\n"
    );
    $headers = "";
    while (strpos($headers, "\r\n\r\n") === false) {
        $headers .= websocketReadExact($socket, 1);
    }
    assertGameSocket(
        strpos($headers, "101 Switching Protocols") !== false,
        "El servidor rechazó la negociación WebSocket."
    );

    $hello = websocketServerMessage($socket);
    assertGameSocket(($hello["type"] ?? "") === "hello", "Falta el saludo del servidor.");

    fwrite($socket, websocketClientFrame([
        "type" => "auth",
        "request_id" => "auth-test",
        "token" => $token,
    ]));
    $authenticated = websocketServerMessage($socket);
    assertGameSocket(
        ($authenticated["type"] ?? "") === "state",
        "El token temporal no devolvió el estado."
    );
    assertGameSocket(
        (int) $authenticated["state"]["game"]["id_game"] === $gameId,
        "El socket recibió el estado de otra partida."
    );

    fwrite($socket, websocketClientFrame([
        "type" => "command",
        "request_id" => "command-test",
        "command" => "encounter.create",
        "payload" => ["name" => "Encuentro por WebSocket"],
    ]));
    $updated = [];
    for ($attempt = 0; $attempt < 4; $attempt++) {
        $candidate = websocketServerMessage($socket);
        if (($candidate["request_id"] ?? "") === "command-test") {
            $updated = $candidate;
            break;
        }
    }
    assertGameSocket(
        ($updated["request_id"] ?? "") === "command-test",
        "La confirmación no conserva el identificador de la petición."
    );
    assertGameSocket(
        ($updated["state"]["encounter"]["name"] ?? "") === "Encuentro por WebSocket",
        "El comando WebSocket no persistió el encuentro."
    );

    echo "GameWebSocketIntegrationTest OK\n";
} finally {
    if (is_resource($socket)) {
        fclose($socket);
    }
    if ($gameId > 0) {
        $repository->pdo()->prepare(
            "DELETE FROM games WHERE id_game = :game"
        )->execute([":game" => $gameId]);
    }
}
