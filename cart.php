<?php
// cart.php — Сагс API
// GET /cart.php  POST /cart.php  DELETE /cart.php?id=N  POST /cart.php?clear=1
require_once 'db.php';
require_auth();

$user   = current_user();
$uid    = $user['id'];
$db     = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->prepare('
        SELECT ci.id, ci.quantity,
               p.id AS product_id, p.name, p.price, p.image_url
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        WHERE ci.user_id = ? ORDER BY ci.id DESC
    ');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
    echo json_encode(['success'=>true,'items'=>$items,'total'=>$total,'count'=>count($items)]);
    exit;
}

if ($method === 'POST' && isset($_GET['clear'])) {
    $stmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ?');
    $stmt->bind_param('i', $uid);
    $stmt->execute(); $stmt->close();
    echo json_encode(['success'=>true,'message'=>'Сагс цэвэрлэгдлээ']); exit;
}

if ($method === 'POST') {
    $body       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $product_id = (int)($body['product_id'] ?? 0);
    $qty        = max(1, (int)($body['quantity'] ?? 1));
    if (!$product_id) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'product_id шаардлагатай']); exit;
    }
    // Бараа байгаа эсэх шалгах
    $chk = $db->prepare('SELECT id FROM products WHERE id = ?');
    $chk->bind_param('i', $product_id); $chk->execute(); $chk->store_result();
    if ($chk->num_rows === 0) {
        http_response_code(404); echo json_encode(['success'=>false,'message'=>'Бараа олдсонгүй']);
        $chk->close(); exit;
    }
    $chk->close();
    // Аль хэдийн байвал quantity нэмэх
    $exist = $db->prepare('SELECT id, quantity FROM cart_items WHERE user_id=? AND product_id=?');
    $exist->bind_param('ii', $uid, $product_id); $exist->execute();
    $row = $exist->get_result()->fetch_assoc(); $exist->close();
    if ($row) {
        $newQty = $row['quantity'] + $qty;
        $upd = $db->prepare('UPDATE cart_items SET quantity=? WHERE id=?');
        $upd->bind_param('ii', $newQty, $row['id']); $upd->execute(); $upd->close();
    } else {
        $ins = $db->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?,?,?)');
        $ins->bind_param('iii', $uid, $product_id, $qty); $ins->execute(); $ins->close();
    }
    echo json_encode(['success'=>true,'message'=>'Сагсанд нэмэгдлээ']); exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'id шаардлагатай']); exit; }
    $stmt = $db->prepare('DELETE FROM cart_items WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $id, $uid); $stmt->execute(); $stmt->close();
    echo json_encode(['success'=>true,'message'=>'Устгагдлаа']); exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Дэмжигдээгүй метод']);
?>
