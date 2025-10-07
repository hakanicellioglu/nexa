<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Returns the merged application configuration array.
 */
function app_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $defaults = [
        'db' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'name' => 'nexa',
            'user' => 'nexa_user',
            'password' => 'nexa_pass',
            'charset' => 'utf8mb4',
        ],
    ];

    $configPath = __DIR__ . '/../config.php';
    $fileConfig = [];

    if (is_file($configPath)) {
        $loaded = require $configPath;
        if (is_array($loaded)) {
            $fileConfig = $loaded;
        }
    }

    $config = array_replace_recursive($defaults, $fileConfig);

    return $config;
}

/**
 * Returns the shared PDO connection.
 */
function get_db_connection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config();
    $dbConfig = $config['db'];

    $dbHost = getenv('DB_HOST') ?: (string) $dbConfig['host'];
    $dbPort = getenv('DB_PORT') ?: (string) $dbConfig['port'];
    $dbName = getenv('DB_NAME') ?: (string) $dbConfig['name'];
    $dbUser = getenv('DB_USER') ?: (string) $dbConfig['user'];
    $dbPass = getenv('DB_PASSWORD') ?: (string) $dbConfig['password'];
    $charset = (string) ($dbConfig['charset'] ?? 'utf8mb4');

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $dbHost, $dbPort, $dbName, $charset);

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        $message = 'Database connection failed. Please verify your database credentials in config.php or environment variables.';

        if (PHP_SAPI === 'cli') {
            throw new RuntimeException($message, 0, $exception);
        }

        error_log($exception->getMessage());
        http_response_code(500);
        echo $message;
        exit;
    }

    return $pdo;
}

/**
 * Returns the authenticated user array or null.
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Redirects to the login page when the visitor is not authenticated.
 * Returns the user array otherwise.
 */
function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

/**
 * Ensures the visitor is authenticated for API requests.
 */
function require_api_user(): array
{
    $user = current_user();
    if ($user === null) {
        json_response([
            'success' => false,
            'message' => 'Authentication required.',
        ], 401);
    }

    return $user;
}

/**
 * Redirects authenticated users away from public pages.
 */
function redirect_if_logged_in(): void
{
    if (current_user() !== null) {
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Returns an escaped string suitable for HTML output.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Ensures a CSRF token exists for the current session and returns it.
 */
function ensure_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validates an incoming CSRF token against the session token.
 */
function validate_csrf_token(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || $token === null) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sends a JSON response and terminates execution.
 */
function json_response(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
