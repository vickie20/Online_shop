<?php
require_once '../config/admin/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $sessionId = $_POST['session_id'] ?? null;
    
    if (!$productId || !$sessionId) {
        echo json_encode(['success' => false, 'message' => 'Product ID and Session ID are required']);
        exit;
    }
    
    // Get product details
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Check if product already exists in cart for this session
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE session_id = ? AND product_id = ?");
    $stmt->execute([$sessionId, $productId]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['quantity'] + 1;
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $existingItem['id']]);
    } else {
        // Add new item to cart
        $stmt = $pdo->prepare("INSERT INTO cart_items (session_id, product_id, quantity, price) VALUES (?, ?, 1, ?)");
        $stmt->execute([$sessionId, $productId, $product['price']]);
    }
    
    // Get updated cart count
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_items FROM cart_items WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $result = $stmt->fetch();
    $totalItems = $result['total_items'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'total_items' => $totalItems,
        'message' => 'Product added to cart'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}