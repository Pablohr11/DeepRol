<?php

require_once __DIR__ . "/GameWebSocketServer.php";

$options = getopt("", ["host::", "port::"]);
$host = (string) ($options["host"] ?? getenv("DEEPROL_WS_HOST") ?: "127.0.0.1");
$port = (int) ($options["port"] ?? getenv("DEEPROL_WS_PORT") ?: 8081);

try {
    $repository = new GameRepository();
    $server = new GameWebSocketServer($repository, $host, $port);
    $server->run();
} catch (Throwable $exception) {
    fwrite(STDERR, "DeepRol WebSocket: " . $exception->getMessage() . PHP_EOL);
    exit(1);
}
