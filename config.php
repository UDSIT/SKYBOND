<?php
// ------------------------------------------------------------------
// GoDaddy database connection settings.
// Find these in cPanel > Databases > MySQL Databases (host is usually
// "localhost"; db name and user are typically prefixed with your
// cPanel username, e.g. "usk1234_skysoft").
// ------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'yourcpaneluser_skysoft');
define('DB_USER', 'yourcpaneluser_dbuser');
define('DB_PASS', 'your-db-password');

// Restrict which origin(s) may call this API. Set to your site's URL
// once the frontend is uploaded, e.g. 'https://uskbond.com'.
define('ALLOWED_ORIGIN', '*');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function cors(): void {
    header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params(['samesite' => 'Lax']);
        session_start();
    }
}

// Call at the top of every protected endpoint.
function require_login(): array {
    start_session();
    if (empty($_SESSION['user'])) {
        respond(['error' => 'Not logged in.'], 401);
    }
    return $_SESSION['user'];
}
