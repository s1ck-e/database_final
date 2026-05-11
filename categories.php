<?php
// categories.php — Ангиллын жагсаалт
// GET /categories.php
require_once 'db.php';

$rows = get_db()->query('SELECT * FROM categories ORDER BY id')->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success' => true, 'categories' => $rows]);
?>
