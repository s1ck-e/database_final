<?php
// logout.php — Гарах API
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION = [];
session_destroy();
echo json_encode(['success' => true, 'message' => 'Амжилттай гарлаа']);
?>
