<?php
// ─────────────────────────────────────────────
//  orders.php  —  Захиалга API
//
//  GET  /orders.php          → хэрэглэгчийн захиалгын жагсаалт
//  GET  /orders.php?id=5     → нэг захиалгын дэлгэрэнгүй
//  POST /orders.php          → шинэ захиалга үүсгэх
// ─────────────────────────────────────────────
require_once 'db.php';
require_auth();

$user   = current_user();
$uid    = $user['id'];
$db     = get_db();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET нэг захиалга ─────────────────────────
if ($method === 'GET' && isset($_GET['id'])) {
    $oid  = (int)$_GET['id'];
    $stmt = $db->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $oid, $uid); $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$order) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Олдсонгүй']); exit; }

    $istmt = $db->prepare('
        SELECT oi.*, p.name, p.image_url
        FROM order_items oi JOIN products p ON p.id=oi.product_id
        WHERE oi.order_id=?
    ');
    $istmt->bind_param('i', $oid); $istmt->execute();
    $order['items'] = $istmt->get_result()->fetch_all(MYSQLI_ASSOC); $istmt->close();
    echo json_encode(['success'=>true,'order'=>$order]); exit;
}

// ── GET жагсаалт ─────────────────────────────
if ($method === 'GET') {
    $stmt = $db->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
    $stmt->bind_param('i', $uid); $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
    echo json_encode(['success'=>true,'orders'=>$orders]); exit;
}

// ── POST: шинэ захиалга ───────────────────────
if ($method === 'POST') {
    $body           = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $receiver_name  = trim($body['receiver_name']  ?? '');
    $phone          = trim($body['phone']          ?? '');
    $address        = trim($body['address']        ?? '');
    $payment_method = trim($body['payment_method'] ?? 'Карт');

    if (!$receiver_name || !$phone || !$address) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'Хүргэлтийн мэдээллийг бүрэн оруулна уу']); exit;
    }

    // Сагс авах
    $cstmt = $db->prepare('
        SELECT ci.quantity, p.id AS pid, p.price
        FROM cart_items ci JOIN products p ON p.id=ci.product_id
        WHERE ci.user_id=?
    ');
    $cstmt->bind_param('i', $uid); $cstmt->execute();
    $cart_items = $cstmt->get_result()->fetch_all(MYSQLI_ASSOC); $cstmt->close();

    if (empty($cart_items)) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'Сагс хоосон байна']); exit;
    }

    $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));

    // Transaction эхлүүлэх
    $db->begin_transaction();
    try {
        $ostmt = $db->prepare('
            INSERT INTO orders (user_id, receiver_name, phone, address, payment_method, total_price)
            VALUES (?,?,?,?,?,?)
        ');
        $ostmt->bind_param('issssi', $uid, $receiver_name, $phone, $address, $payment_method, $total);
        $ostmt->execute();
        $order_id = $db->insert_id; $ostmt->close();

        $iistmt = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)');
        foreach ($cart_items as $item) {
            $iistmt->bind_param('iiii', $order_id, $item['pid'], $item['quantity'], $item['price']);
            $iistmt->execute();
        }
        $iistmt->close();

        // Сагс цэвэрлэх
        $delstmt = $db->prepare('DELETE FROM cart_items WHERE user_id=?');
        $delstmt->bind_param('i', $uid); $delstmt->execute(); $delstmt->close();

        $db->commit();
        echo json_encode([
            'success'  => true,
            'message'  => 'Захиалга амжилттай',
            'order_id' => $order_id,
            'total'    => $total,
        ]);
    } catch (Exception $e) {
        $db->rollback();
        error_log('Order create error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>'Захиалга хийхэд алдаа гарлаа']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Дэмжигдээгүй метод']);
?>
