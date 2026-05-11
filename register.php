<?php
// ══════════════════════════════════════════════
//  register.php  —  ЭМЗЭГ ХУВИЛБАР
//  ⚠️  Бий даалтын демо зорилгоор
// ══════════════════════════════════════════════
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'POST шаардлагатай']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$name     = trim($body['name']     ?? '');
$email    = trim($body['email']    ?? '');
$phone    = trim($body['phone']    ?? '');
$password =      $body['password'] ?? '';

if (!$name || !$email || !$phone || !$password) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Бүх талбарыг бөглөнө үү']);
    exit;
}

$db = get_db();

// ── ЭМЗЭГ #1: SQL Injection ──────────────────
// Prepared statement байхгүй
$check = "SELECT id FROM users WHERE email = '$email'";
$res   = $db->query($check);

if ($res && $res->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'Энэ и-мэйл аль хэдийн бүртгэлтэй байна']);
    exit;
}

// ── ЭМЗЭГ #2: Нууц үг plaintext хадгална ────
// password_hash() ашиглахгүй — тэр чигт нь DB-д орно
$plain_password = $password;

$insert = "INSERT INTO users (name, email, phone, password)
           VALUES ('$name', '$email', '$phone', '$plain_password')";

if ($db->query($insert)) {
    $uid = $db->insert_id;

    // ── ЭМЗЭГ #3: Session fixation ───────────
    $_SESSION['user'] = ['id' => $uid, 'name' => $name, 'email' => $email];

    // ── ЭМЗЭГ #4: ID задруулна ───────────────
    echo json_encode([
        'success' => true,
        'message' => 'Бүртгэл амжилттай',
        'user'    => ['id' => $uid, 'name' => $name, 'email' => $email]
    ]);
} else {
    // ── ЭМЗЭГ #5: DB алдаа задруулна ─────────
    echo json_encode(['success'=>false,'message'=>'DB алдаа: ' . $db->error]);
}
?>
