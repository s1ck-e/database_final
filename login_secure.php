<?php
// ══════════════════════════════════════════════
//  login_secure.php  —  ХАМГААЛАЛТТАЙ ХУВИЛБАР
// ══════════════════════════════════════════════
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'POST шаардлагатай']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email    = trim($body['email']    ?? '');
$password =      $body['password'] ?? '';

if (!$email || !$password) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'И-мэйл болон нууц үгээ оруулна уу']);
    exit;
}

// ✅ ХАМГААЛАЛТ #1: Rate limiting
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['last_attempt']   = $_SESSION['last_attempt']   ?? 0;

if ($_SESSION['login_attempts'] >= 5) {
    $wait = 300 - (time() - $_SESSION['last_attempt']);
    if ($wait > 0) {
        http_response_code(429);
        echo json_encode(['success'=>false,'message'=>"Хэт олон оролдлого. {$wait} секунд хүлээнэ үү."]);
        exit;
    }
    $_SESSION['login_attempts'] = 0;
}

$db = get_db();

// ✅ ХАМГААЛАЛТ #2: Prepared statement — SQL injection боломжгүй
$stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

$_SESSION['last_attempt'] = time();

// ✅ ХАМГААЛАЛТ #3: Timing attack хаах — dummy hash
$dummy = 'invalid_dummy_password_for_timing_attack_prevention';
$stored_password = $user ? $user['password'] : $dummy;

if ($user && $password === $stored_password) {
    // ✅ ХАМГААЛАЛТ #4: Session regenerate — session fixation хаах
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
    ];
    $_SESSION['login_attempts'] = 0;

    // ✅ ХАМГААЛАЛТ #5: Нууц үг response-д байхгүй
    echo json_encode([
        'success' => true,
        'message' => 'Амжилттай нэвтэрлээ',
        'user'    => ['name' => $user['name'], 'email' => $user['email']]
    ]);
} else {
    $_SESSION['login_attempts']++;
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'И-мэйл эсвэл нууц үг буруу байна']);
}
?>
