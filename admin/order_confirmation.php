<?php
require_once '../config/admin/db.php';

$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    header("Location: cart.php");
    exit;
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ?
    GROUP BY o.id
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: cart.php");
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_path 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../logic/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - E-Commerce Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-green-500 text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Order Confirmed!</h1>
                <p class="text-gray-600">Thank you for your purchase. Your order has been received and is being processed.</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Details</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Number</span>
                        <span class="font-medium">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Date</span>
                        <span class="font-medium"><?= date('F j, Y', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total</span>
                        <span class="font-medium">KSH <?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Items</span>
                        <span class="font-medium"><?= $order['item_count'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment Method</span>
                        <span class="font-medium">Cash on Delivery</span>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Customer Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Name</h3>
                        <p class="text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Phone</h3>
                        <p class="text-gray-800"><?= htmlspecialchars($order['customer_phone']) ?></p>
                    </div>
                    <?php if (!empty($order['customer_email'])): ?>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Email</h3>
                        <p class="text-gray-800"><?= htmlspecialchars($order['customer_email']) ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-medium text-gray-600">Delivery Location</h3>
                        <p class="text-gray-800"><?= htmlspecialchars($order['delivery_location']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Items</h2>
                <div class="space-y-4">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                            <img src="<?= $item['image_path'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-800"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-sm text-gray-600">Qty: <?= $item['quantity'] ?> × KSH <?= number_format($item['price'], 2) ?></p>
                        </div>
                        <div class="font-medium">
                            KSH <?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="text-center">
                <a href="products.php" class="inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                    Continue Shopping
                </a>
            </div>
        </div>
    </main>
</body>
</html>