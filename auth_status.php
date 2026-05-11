<?php
// auth_status.php — Session шалгах API
// GET /auth_status.php  →  { loggedIn, user? }
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$user = $_SESSION['user'] ?? null;
echo json_encode([
    'loggedIn' => (bool)$user,
    'user'     => $user ? ['name' => $user['name'], 'email' => $user['email']] : null,
]);
?>
