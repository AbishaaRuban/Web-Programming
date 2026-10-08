<?php
/**
 * Shared config: session start, CORS headers, DB connection, and helper functions.
 * Every backend PHP file includes this first.
 */

// ---- Session ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- CORS (only needed if frontend is opened separately, e.g. from Live Server) ----
// If you open the frontend HTML files directly by double-clicking, or via
// http://localhost/skillhive/frontend/..., CORS usually isn't an issue.
// This is kept safe/permissive for local development.
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ---- Database connection ----
$DB_HOST = "sql303.infinityfree.com";
$DB_NAME = "if0_43121919_skillhive";
$DB_USER = "if0_43121919";
$DB_PASS = "webskillhive"; // default XAMPP MySQL password is empty

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "data" => null, "message" => "Database connection failed. Is MySQL running in XAMPP?"]);
    exit();
}

// ---- Helper: send a JSON response and stop ----
function respond($success, $data = null, $message = "") {
    echo json_encode(["success" => $success, "data" => $data, "message" => $message]);
    exit();
}

// ---- Helper: require the user to be logged in ----
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        respond(false, null, "Not authenticated. Please log in.");
    }
    return $_SESSION['user_id'];
}

// ---- Helper: read JSON body sent from JavaScript fetch() ----
function getJsonBody() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
