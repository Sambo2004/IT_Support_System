<?php
// config/app.php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// 1. Load .env file
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Strip outer quotes if any
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }
            $_ENV[$key] = $val;
            putenv("{$key}={$val}");
        }
    }
}

// 2. Constants & Runtime settings
define('APP_NAME', $_ENV['APP_NAME'] ?? 'IT Support System');
define('APP_URL', rtrim($_ENV['APP_URL'] ?? 'http://localhost/IT_Support_System/public', '/'));
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'Asia/Phnom_Penh');

date_default_timezone_set(APP_TIMEZONE);

// Ensure storage log directory exists
$logDir = BASE_PATH . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/app.log');

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

// Global Uncaught Exception Handler
set_exception_handler(function (Throwable $e) {
    $timestamp = date('Y-m-d H:i:s');
    $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $logFile = BASE_PATH . '/storage/logs/app.log';
    $logMsg = "[{$timestamp}] [{$ip}] [{$method} {$uri}] Uncaught " . get_class($e) . ": {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}\n\n";
    @file_put_contents($logFile, $logMsg, FILE_APPEND | LOCK_EX);

    if (php_sapi_name() === 'cli') {
        echo "\n[ERROR] " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")\n";
        exit(1);
    }

    if (!headers_sent()) {
        http_response_code(500);
    }

    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "<!DOCTYPE html><html><head><title>System Exception</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'></head><body class='bg-light p-4'><div class='container my-5'><div class='alert alert-danger'><h4 class='alert-heading'>Uncaught Exception</h4><p class='lead mb-1'>" . htmlspecialchars($e->getMessage()) . "</p><p class='small text-muted'>Location: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p><pre class='bg-white p-3 border rounded small mt-3'>" . htmlspecialchars($e->getTraceAsString()) . "</pre></div></div></body></html>";
    } else {
        $errView = BASE_PATH . '/resources/views/errors/500.php';
        if (file_exists($errView)) {
            require $errView;
        } else {
            echo "<!DOCTYPE html><html><head><title>500 - Internal Server Error</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'></head><body class='bg-light d-flex align-items-center justify-content-center min-vh-100'><div class='text-center p-4'><h1 class='display-1 fw-bold text-danger'>500</h1><h3 class='fw-semibold'>Internal System Error</h3><p class='text-muted'>An unexpected incident occurred. Our technical staff has been notified.</p><a href='" . url('/') . "' class='btn btn-primary mt-2'>Back to Safety</a></div></body></html>";
        }
    }
    exit;
});

// Production Security Response Headers
if (!headers_sent() && php_sapi_name() !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// 3. Global Helper Functions

function view(string $name, array $data = []): void {
    extract($data);
    $viewPath = BASE_PATH . '/resources/views/' . str_replace('.', '/', $name) . '.php';
    if (!file_exists($viewPath)) {
        http_response_code(500);
        die("View [{$name}] not found at {$viewPath}");
    }
    require $viewPath;
}

function redirect(string $path = '/'): void {
    $target = str_starts_with($path, 'http') ? $path : url($path);
    header("Location: {$target}");
    exit;
}

function url(string $path = ''): string {
    $cleanPath = '/' . ltrim($path, '/');
    return ($cleanPath === '/') ? APP_URL : APP_URL . $cleanPath;
}

function asset(string $path): string {
    return APP_URL . '/static/' . ltrim($path, '/');
}

function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function old(string $field, string $default = ''): string {
    if (isset($_SESSION['old_input'][$field])) {
        $val = $_SESSION['old_input'][$field];
        unset($_SESSION['old_input'][$field]);
        return (string)$val;
    }
    return $default;
}

function formatDate(?string $date, string $format = 'M d, Y'): string {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function formatDateTime(?string $date, string $format = 'M d, Y H:i'): string {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function statusBadge(string $status): string {
    $badges = [
        'open'        => 'bg-info text-dark',
        'assigned'    => 'bg-primary text-white',
        'in_progress' => 'bg-warning text-dark',
        'pending'     => 'bg-secondary text-white',
        'resolved'    => 'bg-success text-white',
        'closed'      => 'bg-dark text-white'
    ];
    $cls = $badges[$status] ?? 'bg-secondary text-white';
    $label = ucwords(str_replace('_', ' ', $status));
    return "<span class=\"badge {$cls}\">" . e($label) . "</span>";
}

function priorityBadge(string $priority): string {
    $badges = [
        'low'      => 'bg-success text-white',
        'medium'   => 'bg-info text-dark',
        'high'     => 'bg-warning text-dark',
        'critical' => 'bg-danger text-white'
    ];
    $cls = $badges[$priority] ?? 'bg-secondary text-white';
    $label = ucfirst($priority);
    return "<span class=\"badge {$cls}\">" . e($label) . "</span>";
}

function isRole(string ...$roles): bool {
    if (!class_exists('Session')) {
        return false;
    }
    $userRole = Session::userRole();
    if (!$userRole) return false;
    return in_array($userRole, $roles, true);
}

