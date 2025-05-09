<?php
// This can be included at the top of your pages
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopEase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
  
    /* Replace the existing .loader styles with this */
    .loader {
        position: relative;
        width: 80px;
        height: 80px;
    }
    .page-transition {
    position: fixed;
    inset: 0;
    background: white;
    z-index: 1000;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.page-transition.active {
    opacity: 1;
    pointer-events: auto;
}
    .cart-icon {
        position: absolute;
        font-size: 40px;
        color: #6366f1;
        animation: cartBounce 1s infinite ease-in-out;
    }
    
    .items {
        position: absolute;
        width: 20px;
        height: 20px;
        background-color: #ef4444;
        border-radius: 50%;
        animation: itemsFloat 1s infinite ease-in-out;
    }
    
    .items:nth-child(2) {
        left: 30px;
        animation-delay: 0.2s;
    }
    
    .items:nth-child(3) {
        left: 50px;
        animation-delay: 0.4s;
    }
    
    @keyframes cartBounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-15px);
        }
    }
    
    @keyframes itemsFloat {
        0%, 100% {
            transform: translateY(0);
            opacity: 1;
        }
        50% {
            transform: translateY(-25px);
            opacity: 0.5;
        }
    }
        
        /* Mobile menu animation */
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .mobile-menu.open {
            max-height: 500px;
            transition: max-height 0.5s ease-in;
        }
        
        /* Navbar hover effects */
        .nav-link {
            position: relative;
            padding-bottom: 4px;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #6366f1;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .nav-link.active::after {
            width: 100%;
        }
        
        /* Cart indicator */
        .cart-indicator {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 18px;
            height: 18px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
   
<div class="page-transition" id="pageTransition">
    <div class="loader">
        <i class="fas fa-shopping-cart cart-icon"></i>
        <div class="items"></div>
        <div class="items"></div>
        <div class="items"></div>
    </div>
</div>

    <!-- Navigation Bar -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Company Logo/Icon -->
                <div class="flex items-center">
                    <a href="/Vivian_shop/admin/login.php" class="text-2xl font-bold text-indigo-600 flex items-center">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        <span>ShopEase</span>
                    </a>
                </div>
                

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="/Vivian_shop/index.php" class="nav-link text-gray-700 hover:text-indigo-600 <?php echo $current_page == 'index.php' ? 'active text-indigo-600' : ''; ?>">
                        Home
                    </a>
                    <a href="/Vivian_shop/admin/products.php" class="nav-link text-gray-700 hover:text-indigo-600 <?php echo $current_page == 'products.php' ? 'active text-indigo-600' : ''; ?>">
                        Products
                    </a>
                    <a href="/Vivian_shop/admin/cart.php" class="nav-link text-gray-700 hover:text-indigo-600 relative <?php echo $current_page == 'cart.php' ? 'active text-indigo-600' : ''; ?>">
                        Cart
                     <!-- Replace with dynamic cart count -->
                    </a>
                    <a href="/Vivian_shop/admin/track_orders.php" class="nav-link text-gray-700 hover:text-indigo-600 <?php echo $current_page == 'orders.php' ? 'active text-indigo-600' : ''; ?>">
                        Orders
                    </a>
                    <a href="/Vivian_shop/pages/reviews.php" class="nav-link text-gray-700 hover:text-indigo-600 <?php echo $current_page == 'reviews.php' ? 'active text-indigo-600' : ''; ?>">
                        Reviews
                    </a>
                </nav>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-indigo-600 focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="mobile-menu md:hidden bg-white">
                <div class="px-2 pt-2 pb-4 space-y-1">
                    <a href="/Vivian_shop/index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 <?php echo $current_page == 'index.php' ? 'bg-gray-100 text-indigo-600' : ''; ?>">
                        Home
                    </a>
                    <a href="/Vivian_shop/admin/products.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 <?php echo $current_page == 'products.php' ? 'bg-gray-100 text-indigo-600' : ''; ?>">
                        Products
                    </a>
                    <a href="/Vivian_shop/admin/cart.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 relative <?php echo $current_page == 'cart.php' ? 'bg-gray-100 text-indigo-600' : ''; ?>">
                        Cart
                       <!-- Replace with dynamic cart count -->
                    </a>
                    <a href="/Vivian_shop/admin/track_orders.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 <?php echo $current_page == 'orders.php' ? 'bg-gray-100 text-indigo-600' : ''; ?>">
                        Orders
                    </a>
                    <a href="/Vivian_shop/pages/reviews.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 <?php echo $current_page == 'reviews.php' ? 'bg-gray-100 text-indigo-600' : ''; ?>">
                        Reviews
                    </a>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
        });
        
        
        document.querySelectorAll('a').forEach(link => {
    if (link.href &&
        !link.href.startsWith('http') &&
        !link.href.startsWith('mailto') &&
        !link.href.startsWith('tel') &&
        !link.href.includes('#')) {
        
        link.addEventListener('click', (e) => {
            if (e.ctrlKey || e.metaKey || e.shiftKey || link.hasAttribute('download')) return;

            e.preventDefault();
            const pageTransition = document.getElementById('pageTransition');
            pageTransition.classList.add('active');

            // Delay navigation to show animation
            setTimeout(() => {
                window.location.href = link.href;
            }, 1500); // Slightly longer for smooth experience
        });
    }
});

window.addEventListener('load', () => {
    const pageTransition = document.getElementById('pageTransition');
    pageTransition.classList.remove('active');
});


    </script>