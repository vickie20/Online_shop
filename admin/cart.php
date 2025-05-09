<?php
require_once '../config/admin/db.php';

// Generate a unique session ID for the cart if not exists
$sessionId = $_COOKIE['cart_session'] ?? uniqid('cart_', true);
if (!isset($_COOKIE['cart_session'])) {
    setcookie('cart_session', $sessionId, time() + (86400 * 30), "/"); // 30 days
}

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT ci.*, p.name, p.image_path, p.description 
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.session_id = ?
");
$stmt->execute([$sessionId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total amount
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

// Handle remove item from cart
if (isset($_GET['remove_item'])) {
    $itemId = $_GET['remove_item'];
    
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
    $stmt->execute([$itemId, $sessionId]);
    
    header("Location: cart.php");
    exit;
}

// Handle place order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'] ?? null;
    $location = $_POST['location'];
    
    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($location)) $errors[] = 'Delivery location is required';
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (customer_name, customer_phone, customer_email, delivery_location, total_amount)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $phone, $email, $location, $totalAmount]);
            $orderId = $pdo->lastInsertId();
            
            // Add order items
            foreach ($cartItems as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            }
            
            // Clear the cart
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE session_id = ?");
            $stmt->execute([$sessionId]);
            
            $pdo->commit();
            
            // Redirect to order confirmation
            header("Location: order_confirmation.php?order_id=" . $orderId);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to place order: " . $e->getMessage();
        }
    }
}

include '../logic/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - E-Commerce Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .animate-ping {
            animation: ping 1s cubic-bezier(0, 0, 0.2, 1) 1;
        }
        @keyframes ping {
            75%, 100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Your Shopping Cart</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-shopping-cart text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-700 mb-2">Your cart is empty</h3>
                <p class="text-gray-500 mb-4">Looks like you haven't added any items yet.</p>
                <a href="products.php" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300 inline-block">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="p-4 flex flex-col sm:flex-row gap-4">
                                    <div class="w-full sm:w-32 h-32 bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="<?= $item['image_path'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <h3 class="text-lg font-medium text-gray-800"><?= htmlspecialchars($item['name']) ?></h3>
                                            <a href="cart.php?remove_item=<?= $item['id'] ?>" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?= htmlspecialchars($item['description']) ?></p>
                                        <div class="mt-4 flex items-center justify-between">
                                            <div class="flex items-center">
                                                <button class="quantity-btn w-8 h-8 border border-gray-300 rounded-l-md flex items-center justify-center" data-action="decrease" data-item-id="<?= $item['id'] ?>">
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>
                                                <span class="quantity-display w-12 h-8 border-t border-b border-gray-300 flex items-center justify-center">
                                                    <?= $item['quantity'] ?>
                                                </span>
                                                <button class="quantity-btn w-8 h-8 border border-gray-300 rounded-r-md flex items-center justify-center" data-action="increase" data-item-id="<?= $item['id'] ?>">
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button>
                                            </div>
                                            <div class="text-lg font-semibold text-gray-800">
                                                KSH <?= number_format($item['price'] * $item['quantity'], 2) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h2>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">KSH <?= number_format($totalAmount, 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery</span>
                                <span class="font-medium">KSH 0.00</span> <!-- You can add delivery fees here -->
                            </div>
                            <div class="border-t border-gray-200 pt-4 flex justify-between">
                                <span class="text-lg font-bold text-gray-800">Total</span>
                                <span class="text-lg font-bold text-gray-800">KSH <?= number_format($totalAmount, 2) ?></span>
                            </div>
                        </div>
                        
                        <!-- Checkout Form -->
                        <form method="POST" class="mt-6 space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                                <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Delivery Location</label>
                                <textarea id="location" name="location" rows="2" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>
                            
                            <button type="submit" name="place_order" class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                                Place Your Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Generate a session ID if not exists
        function getOrCreateSessionId() {
            let sessionId = getCookie('cart_session');
            if (!sessionId) {
                sessionId = 'cart_' + Math.random().toString(36).substr(2, 9);
                setCookie('cart_session', sessionId, 30);
            }
            return sessionId;
        }

        // Helper functions for cookies
        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        }

        function getCookie(name) {
            const value = "; " + document.cookie;
            const parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
        }

        // Update quantity buttons to include session ID
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.dataset.action;
                const itemId = this.dataset.itemId;
                const quantityDisplay = this.closest('.flex').querySelector('.quantity-display');
                let quantity = parseInt(quantityDisplay.textContent);
                
                if (action === 'increase') {
                    quantity++;
                } else if (action === 'decrease' && quantity > 1) {
                    quantity--;
                }
                
                quantityDisplay.textContent = quantity;
                
                // Send AJAX request with session ID
                fetch('update_cart_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}&quantity=${quantity}&session_id=${getOrCreateSessionId()}`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        quantityDisplay.textContent = data.original_quantity;
                        alert(data.message);
                    } else {
                        window.location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>