<?php
// ─────────────────────────────────────────────
//  db.php  —  Өгөгдлийн сантай холбогдох файл
// ─────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shoppy');

function get_db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            error_log('DB холболт амжилтгүй: ' . $conn->connect_error);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Серверийн алдаа гарлаа']);
            exit;
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Тухайн хэрэглэгч нэвтэрсэн эсэхийг шалгах
function current_user(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['user'] ?? null;
}

// Нэвтрэх шаардлагатай route-д ашиглана
function require_auth(): void {
    if (!current_user()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Нэвтэрнэ үү']);
        exit;
    }
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
?>
