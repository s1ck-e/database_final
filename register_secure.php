<?php
// ══════════════════════════════════════════════
//  register_secure.php  —  ХАМГААЛАЛТТАЙ ХУВИЛБАР
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

// ✅ ХАМГААЛАЛТ #1: Input validation
$errors = [];
if (mb_strlen($name) < 2)                       $errors[] = 'Нэр 2-оос дээш тэмдэгт байна';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'И-мэйл хаяг буруу байна';
if (!preg_match('/^[0-9]{8}$/', $phone))        $errors[] = 'Утасны дугаар 8 оронтой байна';
if (mb_strlen($password) < 8)                   $errors[] = 'Нууц үг 8-аас дээш тэмдэгт байна';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>implode('. ', $errors)]);
    exit;
}

$db = get_db();

// ✅ ХАМГААЛАЛТ #2: Prepared statement
$chk = $db->prepare('SELECT id FROM users WHERE email = ?');
$chk->bind_param('s', $email);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'Энэ и-мэйл аль хэдийн бүртгэлтэй байна']);
    $chk->close(); exit;
}
$chk->close();

// ✅ ХАМГААЛАЛТ #3: bcrypt hash
$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $db->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $name, $email, $phone, $hash);

if (!$stmt->execute()) {
    // ✅ ХАМГААЛАЛТ #5: Алдааг log-д — хэрэглэгчид задруулахгүй
    error_log('Register error: ' . $stmt->error);
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Бүртгэл хийхэд алдаа гарлаа']);
    $stmt->close(); exit;
}

$uid = $db->insert_id;
$stmt->close();

// ✅ ХАМГААЛАЛТ #4: Session regenerate
session_regenerate_id(true);
$_SESSION['user'] = ['id' => $uid, 'name' => $name, 'email' => $email];

// ✅ ХАМГААЛАЛТ #5: ID задруулахгүй
echo json_encode([
    'success' => true,
    'message' => 'Бүртгэл амжилттай',
    'user'    => ['name' => $name, 'email' => $email]
]);
?>
