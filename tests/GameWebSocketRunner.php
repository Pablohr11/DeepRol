<?php

$root = dirname(__DIR__);
$php = "C:\\xampp\\php\\php.exe";
$port = 18081;
$command = [
    $php,
    $root . DIRECTORY_SEPARATOR . "websocket" . DIRECTORY_SEPARATOR . "server.php",
    "--host=127.0.0.1",
    "--port=" . $port,
];
$descriptors = [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"],
    2 => ["pipe", "w"],
];
$pipes = [];
$process = proc_open($command, $descriptors, $pipes, $root);
if (!is_resource($process)) {
    throw new RuntimeException("No se pudo iniciar el servidor WebSocket de prueba.");
}

try {
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    usleep(700000);
    $testCommand = sprintf(
        '"%s" "%s" %d',
        $php,
        __DIR__ . DIRECTORY_SEPARATOR . "GameWebSocketClient.php",
        $port
    );
    passthru($testCommand, $status);
    if ($status !== 0) {
        $serverOutput = stream_get_contents($pipes[1]);
        $serverError = stream_get_contents($pipes[2]);
        throw new RuntimeException(
            "Falló la prueba WebSocket.\n" . $serverOutput . $serverError
        );
    }
} finally {
    foreach ([1, 2] as $pipeIndex) {
        if (isset($pipes[$pipeIndex]) && is_resource($pipes[$pipeIndex])) {
            fclose($pipes[$pipeIndex]);
        }
    }
    proc_terminate($process);
    proc_close($process);
}
