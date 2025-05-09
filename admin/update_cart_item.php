<?php
require_once '../config/admin/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['item_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    $sessionId = $_POST['session_id'] ?? null;
    
    if (!$itemId || !$sessionId) {
        echo json_encode(['success' => false, 'message' => 'Item ID and Session ID are required']);
        exit;
    }
    
    $quantity = max(1, intval($quantity));
    
    // Check if item exists for this session
    $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE id = ? AND session_id = ?");
    $stmt->execute([$itemId, $sessionId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
        exit;
    }
    
    // Update quantity
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $itemId]);
    
    echo json_encode([
        'success' => true,
        'original_quantity' => $item['quantity']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}