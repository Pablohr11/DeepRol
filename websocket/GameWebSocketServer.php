<?php

require_once __DIR__ . "/../classes/GameRepository.php";
require_once __DIR__ . "/../classes/GameCommandService.php";

final class GameWebSocketServer
{
    /** @var GameRepository */
    private $repository;

    /** @var GameCommandService */
    private $commands;

    /** @var resource|null */
    private $server;

    /** @var array<int,array<string,mixed>> */
    private $clients = [];

    /** @var array<int,int> */
    private $lastEventByGame = [];

    /** @var float */
    private $lastPollAt = 0.0;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    public function __construct(
        GameRepository $repository,
        string $host = "127.0.0.1",
        int $port = 8081
    ) {
        $this->repository = $repository;
        $this->commands = new GameCommandService($repository);
        $this->host = $host;
        $this->port = $port;
    }

    public function run(): void
    {
        $errorNumber = 0;
        $errorMessage = "";
        $address = "tcp://" . $this->host . ":" . $this->port;
        $this->server = stream_socket_server(
            $address,
            $errorNumber,
            $errorMessage,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );
        if (!$this->server) {
            throw new RuntimeException(
                "No se pudo abrir {$address}: {$errorMessage} ({$errorNumber})."
            );
        }
        stream_set_blocking($this->server, false);
        $this->log("DeepRol WebSocket escuchando en {$address}");

        while (true) {
            $read = [$this->server];
            foreach ($this->clients as $client) {
                $read[] = $client["socket"];
            }
            $write = null;
            $except = null;
            $changed = @stream_select($read, $write, $except, 0, 200000);
            if ($changed === false) {
                continue;
            }

            foreach ($read as $socket) {
                if ($socket === $this->server) {
                    $this->acceptClient();
                    continue;
                }
                $this->readClient($socket);
            }

            $this->disconnectExpiredUnauthenticatedClients();
            if (microtime(true) - $this->lastPollAt >= 0.5) {
                $this->pollExternalEvents();
                $this->lastPollAt = microtime(true);
            }
        }
    }

    private function acceptClient(): void
    {
        $socket = @stream_socket_accept($this->server, 0);
        if (!$socket) {
            return;
        }
        stream_set_blocking($socket, false);
        $id = (int) $socket;
        $this->clients[$id] = [
            "socket" => $socket,
            "handshake" => false,
            "buffer" => "",
            "identity" => null,
            "connected_at" => microtime(true),
        ];
    }

    private function readClient($socket): void
    {
        $id = (int) $socket;
        if (!isset($this->clients[$id])) {
            return;
        }
        $data = @fread($socket, 65536);
        if ($data === "" && feof($socket)) {
            $this->closeClient($id);
            return;
        }
        if ($data === false || $data === "") {
            return;
        }

        $this->clients[$id]["buffer"] .= $data;
        if (!$this->clients[$id]["handshake"]) {
            $this->performHandshake($id);
            return;
        }

        while (isset($this->clients[$id])) {
            try {
                $frame = $this->decodeFrame($this->clients[$id]["buffer"]);
            } catch (Throwable $exception) {
                $this->sendError($id, $exception->getMessage());
                $this->closeClient($id);
                return;
            }
            if ($frame === null) {
                return;
            }
            $opcode = (int) $frame["opcode"];
            if ($opcode === 0x8) {
                $this->closeClient($id);
                return;
            }
            if ($opcode === 0x9) {
                $this->sendFrame($id, (string) $frame["payload"], 0xA);
                continue;
            }
            if ($opcode !== 0x1) {
                continue;
            }
            $this->handleMessage($id, (string) $frame["payload"]);
        }
    }

    private function performHandshake(int $clientId): void
    {
        $request = (string) $this->clients[$clientId]["buffer"];
        $end = strpos($request, "\r\n\r\n");
        if ($end === false) {
            if (strlen($request) > 16384) {
                $this->closeClient($clientId);
            }
            return;
        }
        $headerBlock = substr($request, 0, $end + 4);
        $this->clients[$clientId]["buffer"] = substr($request, $end + 4);
        $lines = preg_split("/\r\n/", trim($headerBlock)) ?: [];
        $requestLine = array_shift($lines);
        if (!is_string($requestLine) || strpos($requestLine, "GET ") !== 0) {
            $this->closeClient($clientId);
            return;
        }
        $headers = [];
        foreach ($lines as $line) {
            $separator = strpos($line, ":");
            if ($separator === false) {
                continue;
            }
            $key = strtolower(trim(substr($line, 0, $separator)));
            $headers[$key] = trim(substr($line, $separator + 1));
        }
        $key = (string) ($headers["sec-websocket-key"] ?? "");
        $upgrade = strtolower((string) ($headers["upgrade"] ?? ""));
        $origin = (string) ($headers["origin"] ?? "");
        if (
            $key === ""
            || $upgrade !== "websocket"
            || !$this->originAllowed($origin)
        ) {
            @fwrite(
                $this->clients[$clientId]["socket"],
                "HTTP/1.1 403 Forbidden\r\nConnection: close\r\n\r\n"
            );
            $this->closeClient($clientId);
            return;
        }

        $accept = base64_encode(
            sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true)
        );
        $response = "HTTP/1.1 101 Switching Protocols\r\n"
            . "Upgrade: websocket\r\n"
            . "Connection: Upgrade\r\n"
            . "Sec-WebSocket-Accept: {$accept}\r\n\r\n";
        @fwrite($this->clients[$clientId]["socket"], $response);
        $this->clients[$clientId]["handshake"] = true;
        $this->sendJson($clientId, [
            "type" => "hello",
            "message" => "Autentica la conexión.",
        ]);

        if ($this->clients[$clientId]["buffer"] !== "") {
            $this->readClient($this->clients[$clientId]["socket"]);
        }
    }

    private function handleMessage(int $clientId, string $rawMessage): void
    {
        $message = json_decode($rawMessage, true);
        if (!is_array($message)) {
            $this->sendError($clientId, "El mensaje no es JSON válido.");
            return;
        }
        $type = (string) ($message["type"] ?? "");
        $requestId = $this->safeRequestId($message["request_id"] ?? null);

        if ($type === "auth") {
            $identity = $this->repository->authenticateSocketToken(
                (string) ($message["token"] ?? "")
            );
            if (!$identity) {
                $this->sendError($clientId, "El acceso en tiempo real ha caducado.", $requestId);
                $this->closeClient($clientId);
                return;
            }
            $this->clients[$clientId]["identity"] = $identity;
            $gameId = (int) $identity["id_game"];
            $userId = (int) $identity["id_user"];
            try {
                $state = $this->repository->getState($gameId, $userId);
                $this->lastEventByGame[$gameId] = max(
                    (int) ($this->lastEventByGame[$gameId] ?? 0),
                    (int) $state["latest_event_id"]
                );
                $this->sendJson($clientId, [
                    "type" => "state",
                    "state" => $state,
                    "presence" => $this->presenceForGame($gameId),
                    "request_id" => $requestId,
                ]);
                $this->broadcastPresence($gameId);
            } catch (Throwable $exception) {
                $this->sendError($clientId, $exception->getMessage(), $requestId);
                $this->closeClient($clientId);
            }
            return;
        }

        $identity = $this->clients[$clientId]["identity"] ?? null;
        if (!is_array($identity)) {
            $this->sendError($clientId, "Autentica la conexión antes de continuar.", $requestId);
            return;
        }
        $gameId = (int) $identity["id_game"];
        $userId = (int) $identity["id_user"];

        if ($type === "ping") {
            $this->sendJson($clientId, [
                "type" => "pong",
                "request_id" => $requestId,
            ]);
            return;
        }
        if ($type === "sync") {
            try {
                $this->sendJson($clientId, [
                    "type" => "state",
                    "state" => $this->repository->getState($gameId, $userId),
                    "presence" => $this->presenceForGame($gameId),
                    "request_id" => $requestId,
                ]);
            } catch (Throwable $exception) {
                $this->sendError($clientId, $exception->getMessage(), $requestId);
            }
            return;
        }
        if ($type !== "command") {
            $this->sendError($clientId, "El tipo de mensaje no es válido.", $requestId);
            return;
        }

        $command = (string) ($message["command"] ?? "");
        $payload = is_array($message["payload"] ?? null)
            ? $message["payload"]
            : [];
        try {
            $result = $this->commands->handle(
                $gameId,
                $userId,
                $command,
                $payload
            );
            $this->lastEventByGame[$gameId] = max(
                (int) ($this->lastEventByGame[$gameId] ?? 0),
                (int) $result["event_id"]
            );
            $this->broadcastState(
                $gameId,
                [
                    "event_id" => (int) $result["event_id"],
                    "event_type" => (string) $result["event_type"],
                    "state_version" => (int) $result["state_version"],
                ],
                $requestId,
                $clientId
            );
        } catch (InvalidArgumentException $exception) {
            $this->sendError($clientId, $exception->getMessage(), $requestId);
        } catch (RuntimeException $exception) {
            $this->sendError($clientId, $exception->getMessage(), $requestId);
        } catch (Throwable $exception) {
            $this->log("Command error: " . $exception->getMessage());
            $this->sendError(
                $clientId,
                "No se ha podido completar la acción.",
                $requestId
            );
        }
    }

    private function pollExternalEvents(): void
    {
        $games = [];
        foreach ($this->clients as $client) {
            if (is_array($client["identity"] ?? null)) {
                $gameId = (int) $client["identity"]["id_game"];
                $games[$gameId] = true;
            }
        }
        foreach (array_keys($games) as $gameId) {
            try {
                $events = $this->repository->eventsAfter(
                    (int) $gameId,
                    (int) ($this->lastEventByGame[$gameId] ?? 0)
                );
                if (!$events) {
                    continue;
                }
                $last = end($events);
                $this->lastEventByGame[$gameId] = (int) $last["id_event"];
                $this->broadcastState((int) $gameId, [
                    "event_id" => (int) $last["id_event"],
                    "event_type" => (string) $last["event_type"],
                    "state_version" => (int) $last["state_version"],
                ]);
            } catch (Throwable $exception) {
                $this->log("Poll error in game {$gameId}: " . $exception->getMessage());
            }
        }
    }

    private function broadcastState(
        int $gameId,
        array $cause,
        ?string $requestId = null,
        ?int $requestClientId = null
    ): void {
        foreach ($this->clients as $clientId => $client) {
            $identity = $client["identity"] ?? null;
            if (!is_array($identity) || (int) $identity["id_game"] !== $gameId) {
                continue;
            }
            try {
                $payload = [
                    "type" => "state",
                    "state" => $this->repository->getState(
                        $gameId,
                        (int) $identity["id_user"]
                    ),
                    "presence" => $this->presenceForGame($gameId),
                    "cause" => $cause,
                ];
                if ($requestClientId === $clientId) {
                    $payload["request_id"] = $requestId;
                }
                $this->sendJson($clientId, $payload);
            } catch (Throwable $exception) {
                $this->sendError($clientId, "No se pudo sincronizar la partida.");
            }
        }
    }

    private function broadcastPresence(int $gameId): void
    {
        $message = [
            "type" => "presence",
            "presence" => $this->presenceForGame($gameId),
        ];
        foreach ($this->clients as $clientId => $client) {
            $identity = $client["identity"] ?? null;
            if (is_array($identity) && (int) $identity["id_game"] === $gameId) {
                $this->sendJson($clientId, $message);
            }
        }
    }

    private function presenceForGame(int $gameId): array
    {
        $presence = [];
        foreach ($this->clients as $client) {
            $identity = $client["identity"] ?? null;
            if (!is_array($identity) || (int) $identity["id_game"] !== $gameId) {
                continue;
            }
            $userId = (int) $identity["id_user"];
            $presence[$userId] = [
                "id_user" => $userId,
                "display_name" => (string) (
                    $identity["display_name"] ?: $identity["username"]
                ),
                "role" => (string) $identity["role"],
            ];
        }
        return array_values($presence);
    }

    private function disconnectExpiredUnauthenticatedClients(): void
    {
        $now = microtime(true);
        foreach ($this->clients as $clientId => $client) {
            if (
                $client["identity"] === null
                && $now - (float) $client["connected_at"] > 12
            ) {
                $this->closeClient($clientId);
            }
        }
    }

    private function closeClient(int $clientId): void
    {
        if (!isset($this->clients[$clientId])) {
            return;
        }
        $identity = $this->clients[$clientId]["identity"] ?? null;
        $socket = $this->clients[$clientId]["socket"];
        unset($this->clients[$clientId]);
        @fclose($socket);
        if (is_array($identity)) {
            $this->broadcastPresence((int) $identity["id_game"]);
        }
    }

    private function originAllowed(string $origin): bool
    {
        if ($origin === "") {
            return true;
        }
        $configured = trim((string) getenv("DEEPROL_WS_ALLOWED_ORIGINS"));
        if ($configured !== "") {
            $allowed = array_map("trim", explode(",", $configured));
            return in_array($origin, $allowed, true);
        }
        $host = strtolower((string) parse_url($origin, PHP_URL_HOST));
        return in_array($host, ["localhost", "127.0.0.1", "::1"], true);
    }

    private function decodeFrame(string &$buffer): ?array
    {
        $length = strlen($buffer);
        if ($length < 2) {
            return null;
        }
        $first = ord($buffer[0]);
        $second = ord($buffer[1]);
        $finished = ($first & 0x80) !== 0;
        $opcode = $first & 0x0F;
        $masked = ($second & 0x80) !== 0;
        $payloadLength = $second & 0x7F;
        $offset = 2;
        if (!$finished) {
            throw new RuntimeException("No se admiten mensajes WebSocket fragmentados.");
        }
        if (!$masked) {
            throw new RuntimeException("El cliente debe enmascarar sus mensajes.");
        }
        if ($payloadLength === 126) {
            if ($length < 4) {
                return null;
            }
            $payloadLength = unpack("n", substr($buffer, 2, 2))[1];
            $offset = 4;
        } elseif ($payloadLength === 127) {
            if ($length < 10) {
                return null;
            }
            $parts = unpack("Nhigh/Nlow", substr($buffer, 2, 8));
            if ((int) $parts["high"] !== 0) {
                throw new RuntimeException("El mensaje WebSocket es demasiado grande.");
            }
            $payloadLength = (int) $parts["low"];
            $offset = 10;
        }
        if ($payloadLength > 1048576) {
            throw new RuntimeException("El mensaje WebSocket supera 1 MB.");
        }
        $frameLength = $offset + 4 + $payloadLength;
        if ($length < $frameLength) {
            return null;
        }
        $mask = substr($buffer, $offset, 4);
        $offset += 4;
        $payload = substr($buffer, $offset, $payloadLength);
        $decoded = "";
        for ($index = 0; $index < $payloadLength; $index++) {
            $decoded .= $payload[$index] ^ $mask[$index % 4];
        }
        $buffer = substr($buffer, $frameLength);
        return ["opcode" => $opcode, "payload" => $decoded];
    }

    private function sendJson(int $clientId, array $payload): void
    {
        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if ($json === false) {
            $json = '{"type":"error","error":"No se pudo serializar el mensaje."}';
        }
        $this->sendFrame($clientId, $json, 0x1);
    }

    private function sendError(
        int $clientId,
        string $message,
        ?string $requestId = null
    ): void {
        $this->sendJson($clientId, [
            "type" => "error",
            "error" => $message,
            "request_id" => $requestId,
        ]);
    }

    private function sendFrame(int $clientId, string $payload, int $opcode): void
    {
        if (!isset($this->clients[$clientId])) {
            return;
        }
        $length = strlen($payload);
        $header = chr(0x80 | ($opcode & 0x0F));
        if ($length <= 125) {
            $header .= chr($length);
        } elseif ($length <= 65535) {
            $header .= chr(126) . pack("n", $length);
        } else {
            $header .= chr(127) . pack("NN", 0, $length);
        }
        $frame = $header . $payload;
        $written = 0;
        $frameLength = strlen($frame);
        for ($attempt = 0; $attempt < 5 && $written < $frameLength; $attempt++) {
            $result = @fwrite(
                $this->clients[$clientId]["socket"],
                substr($frame, $written)
            );
            if ($result === false) {
                $this->closeClient($clientId);
                return;
            }
            $written += $result;
            if ($result === 0) {
                usleep(1000);
            }
        }
        if ($written < $frameLength) {
            $this->closeClient($clientId);
        }
    }

    private function safeRequestId($requestId): ?string
    {
        $value = (string) $requestId;
        return preg_match("/^[A-Za-z0-9_-]{1,64}$/", $value) ? $value : null;
    }

    private function log(string $message): void
    {
        fwrite(STDOUT, "[" . date("Y-m-d H:i:s") . "] {$message}" . PHP_EOL);
    }
}
