<?php
// login.php — ЭМЗЭГ ХУВИЛБАР
// ⚠️ Зөвхөн localhost demo-д

mysqli_report(MYSQLI_REPORT_OFF);
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'POST шаардлагатай']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$email    = trim($body['email'] ?? '');
$password = $body['password'] ?? '';

if (!$email || !$password) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'И-мэйл болон нууц үгээ оруулна уу']);
    exit;
}

$db = get_db();
$db->set_charset("utf8mb4");

// ⚠️ ЭМЗЭГ #1: Rate limit байхгүй
// ⚠️ ЭМЗЭГ #2: SQL Injection боломжтой
// ⚠️ ЭМЗЭГ #3: password SQL дотор plaintext-р шалгаж байна
$sql = "SELECT * FROM users 
        WHERE email='$email' AND password='$password'
        LIMIT 1";

$result = $db->query($sql);

if (!$result) {
    // ⚠️ ЭМЗЭГ #4: DB error хэрэглэгчид шууд харагдана
    echo json_encode([
        'success'=>false,
        'message'=>'DB алдаа: ' . $db->error,
        'sql'=>$sql
    ]);
    exit;
}

$user = $result->fetch_assoc();

if ($user) {
    // ⚠️ ЭМЗЭГ #5: session_regenerate_id(true) байхгүй
    $_SESSION['user'] = $user;

    // ⚠️ ЭМЗЭГ #6: password хүртэл response-д буцааж байна
    echo json_encode([
        'success'=>true,
        'message'=>'Амжилттай нэвтэрлээ',
        'user'=>$user
    ]);
    exit;
}

http_response_code(401);
echo json_encode([
    'success'=>false,
    'message'=>'И-мэйл эсвэл нууц үг буруу байна'
]);
?>