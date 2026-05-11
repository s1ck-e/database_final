<?php
// ─────────────────────────────────────────────
//  products.php  —  Бараа жагсаалт API
//
//  GET /products.php                  → бүх бараа
//  GET /products.php?category_id=2    → ангиллаар шүүх
//  GET /products.php?search=гутал     → хайлт
//  GET /products.php?id=5             → нэг бараа
// ─────────────────────────────────────────────
require_once 'db.php';

$db = get_db();

// Нэг бараа авах
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare('
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.id = ?
    ');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Бараа олдсонгүй']);
    } else {
        echo json_encode(['success'=>true,'product'=>$row]);
    }
    exit;
}

// Жагсаалт авах
$where  = [];
$params = [];
$types  = '';

if (!empty($_GET['category_id'])) {
    $where[]  = 'p.category_id = ?';
    $params[] = (int)$_GET['category_id'];
    $types   .= 'i';
}

if (!empty($_GET['search'])) {
    $keyword  = '%' . $_GET['search'] . '%';
    $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = $keyword;
    $params[] = $keyword;
    $types   .= 'ss';
}

$sql = 'SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY p.id DESC';

// LIMIT / OFFSET (хуудасчлал)
$limit  = min((int)($_GET['limit']  ?? 20), 100);
$offset = max((int)($_GET['offset'] ?? 0),   0);
$sql   .= ' LIMIT ? OFFSET ?';
$params[] = $limit;
$params[] = $offset;
$types   .= 'ii';

$stmt = $db->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Нийт тоо (хуудасчлалд хэрэгтэй)
$count_sql = 'SELECT COUNT(*) FROM products p';
$total = $db->query($count_sql)->fetch_row()[0];

echo json_encode([
    'success'  => true,
    'products' => $rows,
    'total'    => (int)$total,
]);
?>
