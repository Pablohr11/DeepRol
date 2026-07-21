<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = __DIR__ . '/../data/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0700, true);
    }
    session_save_path($sessionPath);
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require_once __DIR__ . '/../classes/DbConnector.php';

function app_base_url(): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $marker = strpos($script, '/sections/');
    if ($marker === false) {
        $marker = strpos($script, '/src/');
    }

    $base = $marker === false ? rtrim(dirname($script), '/') : substr($script, 0, $marker);
    return $base === '/' || $base === '.' ? '' : $base;
}

function url(string $path = ''): string
{
    return app_base_url() . '/' . ltrim($path, '/');
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function require_login(): int
{
    $userId = current_user_id();
    if (!$userId) {
        header('Location: ' . url('login.php'));
        exit;
    }
    return $userId;
}

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
