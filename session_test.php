<?php
// Session Test - View current session info
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'session_id' => session_id(),
    'user' => $_SESSION['user'] ?? null,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>