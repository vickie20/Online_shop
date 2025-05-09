<?php
require_once '../config/admin/db.php';

// Fetch all categories for filters
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Handle filters
$categoryFilter = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

// Build the products query with filters
$query = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status != 'sold out'";
$params = [];

if ($categoryFilter) {
    $query .= " AND c.id = ?";
    $params[] = $categoryFilter;
}

if ($minPrice !== '') {
    $query .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice !== '') {
    $query .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get min and max prices for price range filter
$priceRange = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products")->fetch(PDO::FETCH_ASSOC);
?>
<?php include '../logic/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - E-Commerce Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom CSS with animations */
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
        }
        
        .product-card {
            transition: all 0.3s ease;
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            transition: all 0.3s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.03);
        }
        
        .add-to-cart-btn {
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(10px);
        }
        
        .product-card:hover .add-to-cart-btn {
            opacity: 1;
            transform: translateY(0);
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-on-sale {
            background-color: var(--secondary);
            color: white;
        }
        
        .status-coming-soon {
            background-color: #f59e0b;
            color: white;
        }
        
        .price-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--primary);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .filter-panel {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }
        
        .filter-panel.open {
            max-height: 500px;
        }
        
        /* Animation classes */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        
        /* Pulse animation for "Coming Soon" products */
        @keyframes pulse {
            0% { opacity: 0.8; }
            50% { opacity: 1; }
            100% { opacity: 0.8; }
        }
        
        .coming-soon-pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    
<!-- Header -->
<header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <!--h1 class="text-2xl font-bold text-indigo-600">ShopEase</h1-->
                <div class="flex items-center space-x-4">
                    <button id="filter-toggle" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-filter"></i>
                        <span>Filters</span>
                    </button>
                    <a href="#" class="text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Filter Panel -->
        <div id="filter-panel" class="filter-panel bg-white rounded-lg shadow-md mb-8 overflow-hidden">
            <form method="GET" class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="category" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $categoryFilter == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Price Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range (KSH)</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <input type="number" name="min_price" placeholder="Min" 
                                   value="<?= htmlspecialchars($minPrice) ?>" 
                                   min="0" max="<?= $priceRange['max_price'] ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <input type="number" name="max_price" placeholder="Max" 
                                   value="<?= htmlspecialchars($maxPrice) ?>" 
                                   min="0" max="<?= $priceRange['max_price'] ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Range: KSH <?= number_format($priceRange['min_price'], 2) ?> - KSH <?= number_format($priceRange['max_price'], 2) ?>
                    </div>
                </div>
                
                <!-- Filter Buttons -->
                <div class="flex items-end space-x-3">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                        Apply Filters
                    </button>
                    <a href="products.php" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Products Grid -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Our Products</h2>
            
            <?php if (empty($products)): ?>
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <i class="fas fa-box-open text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-700 mb-2">No products found</h3>
                    <p class="text-gray-500 mb-4">Try adjusting your filters or check back later for new arrivals.</p>
                    <a href="products.php" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300 inline-block">
                        Clear Filters
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($products as $index => $product): ?>
                        <div class="product-card bg-white rounded-lg shadow-md overflow-hidden animate-fade-in delay-<?= ($index % 4) * 100 ?>" data-product-id="<?= $product['id'] ?>">
                            <!-- Product Image -->
                            <div class="relative overflow-hidden h-48 bg-gray-100">
                                <img src="<?= $product['image_path'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="product-image w-full h-full object-cover">
                                
                                <!-- Price Tag -->
                                <div class="price-tag">
                                    KSH <?= number_format($product['price'], 2) ?>
                                </div>
                                
                                <!-- Status Badge -->
                                <div class="absolute top-2 left-2">
                                    <span class="status-badge status-<?= str_replace(' ', '-', $product['status']) ?> <?= $product['status'] == 'coming soon' ? 'coming-soon-pulse' : '' ?>">
                                        <?= ucfirst($product['status']) ?>
                                    </span>
                                </div>
                                
                                <!-- Add to Cart Button (Plus Icon) -->
                                <button class="add-to-cart-btn absolute bottom-4 right-4 w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800 mb-1"><?= htmlspecialchars($product['name']) ?></h3>
                                        <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($product['category_name']) ?></p>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= htmlspecialchars($product['description']) ?></p>
                                
                                <?php if ($product['warranty']): ?>
                                    <div class="text-xs text-blue-600 mb-2">
                                        <i class="fas fa-shield-alt mr-1"></i> Warranty: <?= htmlspecialchars($product['warranty']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($product['offer']): ?>
                                    <div class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded inline-block">
                                        <i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($product['offer']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">ShopEase</h3>
                    <p class="text-gray-400">Your one-stop shop for quality products at affordable prices.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Home</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Products</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i> Nairobi, Kenya
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i> +254 700 000000
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i> info@shopease.co.ke
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
                <p>&copy; <?= date('Y') ?> ShopEase. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Toggle filter panel
        const filterToggle = document.getElementById('filter-toggle');
        const filterPanel = document.getElementById('filter-panel');
        
        filterToggle.addEventListener('click', () => {
            filterPanel.classList.toggle('open');
            
            // Change icon
            const icon = filterToggle.querySelector('i');
            if (filterPanel.classList.contains('open')) {
                icon.classList.remove('fa-filter');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-filter');
            }
        });
        
        // Add to cart button functionality
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const productCard = btn.closest('.product-card');
        const productId = productCard.dataset.productId;
        
        // Show loading state
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        // Generate or get session ID
        const sessionId = getOrCreateSessionId();
        
        // Send AJAX request to add to cart
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&session_id=${sessionId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count in navbar
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.total_items;
                    cartCount.classList.add('animate-ping');
                    setTimeout(() => cartCount.classList.remove('animate-ping'), 500);
                } else {
                    // Create cart count if it doesn't exist
                    const cartLink = document.querySelector('a[href="cart.php"]');
                    if (cartLink) {
                        const countBadge = document.createElement('span');
                        countBadge.id = 'cart-count';
                        countBadge.className = 'absolute -top-1 -right-1 bg-indigo-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center';
                        countBadge.textContent = data.total_items;
                        cartLink.appendChild(countBadge);
                    }
                }
                
                // Animation effect
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.remove('bg-indigo-600');
                btn.classList.add('bg-green-500');
                
                // Reset after animation
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-plus"></i>';
                    btn.classList.remove('bg-green-500');
                    btn.classList.add('bg-indigo-600');
                }, 1000);
            } else {
                alert(data.message);
                btn.innerHTML = '<i class="fas fa-plus"></i>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = '<i class="fas fa-plus"></i>';
        });
    });
});

// Cookie functions for session management
function getOrCreateSessionId() {
    let sessionId = getCookie('cart_session');
    if (!sessionId) {
        sessionId = 'cart_' + Math.random().toString(36).substr(2, 9);
        setCookie('cart_session', sessionId, 30);
    }
    return sessionId;
}

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
        
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe all product cards that aren't already animated
        document.querySelectorAll('.product-card:not(.animate-fade-in)').forEach(card => {
            observer.observe(card);
        });
        // Handle add to cart with AJAX
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const productCard = btn.closest('.product-card');
        const productId = productCard.dataset.productId; // You'll need to add data-product-id to your product cards
        
        // Show loading state
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        try {
            const response = await fetch('../logic/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update cart count in navbar
                document.querySelectorAll('.cart-indicator').forEach(indicator => {
                    indicator.textContent = result.cart_count;
                });
                
                // Show success animation
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.remove('bg-indigo-600');
                btn.classList.add('bg-green-500');
                
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-plus"></i>';
                    btn.classList.remove('bg-green-500');
                    btn.classList.add('bg-indigo-600');
                    btn.disabled = false;
                }, 1000);
            } else {
                alert(result.message || 'Failed to add to cart');
                btn.innerHTML = '<i class="fas fa-plus"></i>';
                btn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while adding to cart');
            btn.innerHTML = '<i class="fas fa-plus"></i>';
            btn.disabled = false;
        }
    });
});
    </script>
</body>
</html>