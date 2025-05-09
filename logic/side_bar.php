<?php
// side_bar.php
// Verify admin session here (add your authentication logic)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Panel</title>
    <style>
        :root {
            --orbitalSidebar-width: 280px;
            --nebulaSidebar-collapsed: 0;
            --quantumPrimary: #3498db;
            --stellarPrimary-dark: #2980b9;
            --cosmicText: #333;
            --lunarText-light: #f8f9fa;
            --galacticBG: #f8f9fa;
            --voidSidebar-bg: #2c3e50;
            --blackholeSidebar-hover: #34495e;
            --pulsarTransition: 0.3s;
            --singularityBreakpoint: 768px;
        }
        
        * {
            box-sizing: border-box;
        }
        
        .astroBody {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            transition: transform var(--pulsarTransition) ease;
            background-color: var(--galacticBG);
            overflow-x: hidden;
        }
        
        /* Mobile menu toggle button */
        .cosmosMenuToggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: var(--quantumPrimary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            display: none;
        }
        
        .cosmosMenuToggle:hover {
            background: var(--stellarPrimary-dark);
        }
        
        /* Sidebar styles */
        .nebulaSidebar {
            position: fixed;
            width: var(--orbitalSidebar-width);
            height: 100vh;
            background: var(--voidSidebar-bg);
            color: var(--lunarText-light);
            transition: transform var(--pulsarTransition) ease;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
        }
        
        .stellarHeader {
            padding: 20px;
            background: var(--blackholeSidebar-hover);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .stellarHeader h3 {
            margin: 0;
            color: white;
            font-size: 1.3rem;
            font-weight: 500;
        }
        
        .quasarCloseBtn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
        }
        
        .celestialMenu {
            padding: 0;
            list-style: none;
            margin: 0;
        }
        
        .celestialMenu li {
            position: relative;
        }
        
        .celestialMenu li a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: var(--lunarText-light);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
            font-size: 1rem;
        }
        
        .celestialMenu li a:hover,
        .celestialMenu li a.active {
            background: var(--blackholeSidebar-hover);
            border-left: 4px solid var(--quantumPrimary);
        }
        
        .celestialMenu li a i {
            min-width: 25px;
            text-align: center;
            font-size: 1.1rem;
            margin-right: 15px;
        }
        
        .supernovaLogout {
            position: sticky;
            bottom: 0;
            background: var(--blackholeSidebar-hover);
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .supernovaLogout a {
            color: #ff6b6b !important;
            font-weight: 500;
        }
        
        /* Overlay for mobile */
        .eventHorizonOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all var(--pulsarTransition) ease;
        }
        
        /* Main content area */
        .interstellarContent {
            transition: all var(--pulsarTransition) ease;
            min-height: 100vh;
            padding: 20px;
        }
        
        /* Mobile styles */
        @media (max-width: 768px) {
            .cosmosMenuToggle {
                display: flex;
            }
            
            .quasarCloseBtn {
                display: block;
            }
            
            .nebulaSidebar {
                transform: translateX(-100%);
                width: 85%;
                max-width: 300px;
            }
            
            .astroBody.sidebar-open .nebulaSidebar {
                transform: translateX(0);
            }
            
            .astroBody.sidebar-open .eventHorizonOverlay {
                opacity: 1;
                visibility: visible;
            }
            
            .astroBody.sidebar-open {
                overflow: hidden;
            }
            
            .interstellarContent {
                padding-top: 80px;
            }
        }
        
        /* Desktop styles */
        @media (min-width: 769px) {
            .interstellarContent {
                margin-left: var(--orbitalSidebar-width);
            }
            
            .astroBody.sidebar-collapsed .nebulaSidebar {
                transform: translateX(-100%);
            }
            
            .astroBody.sidebar-collapsed .interstellarContent {
                margin-left: 0;
            }
            
            .astroBody.sidebar-collapsed .cosmosMenuToggle {
                transform: translateX(0);
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="astroBody">
    <!-- Mobile menu toggle button -->
    <button class="cosmosMenuToggle" id="quantumToggleBtn">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar overlay for mobile -->
    <div class="eventHorizonOverlay" id="singularityOverlay"></div>
    
    <!-- Sidebar -->
    <div class="nebulaSidebar" id="voidSidebar">
        <div class="stellarHeader">
            <h3>Admin Panel</h3>
            <button class="quasarCloseBtn" id="wormholeCloseBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <ul class="celestialMenu">
            <li>
                <a href="#" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="/Vivian_shop/admin/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                    <i class="fas fa-boxes"></i>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="/Vivian_shop/admin/admin_update_orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li class="supernovaLogout">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main content area -->
    <div class="interstellarContent" id="cosmicMainContent">
        <!-- Your page content will go here -->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantumToggleBtn = document.getElementById('quantumToggleBtn');
            const wormholeCloseBtn = document.getElementById('wormholeCloseBtn');
            const singularityOverlay = document.getElementById('singularityOverlay');
            const voidSidebar = document.getElementById('voidSidebar');
            const astroBody = document.querySelector('.astroBody');
            
            // Mobile menu toggle
            quantumToggleBtn.addEventListener('click', function() {
                astroBody.classList.add('sidebar-open');
            });
            
            // Close sidebar
            wormholeCloseBtn.addEventListener('click', function() {
                astroBody.classList.remove('sidebar-open');
            });
            
            // Close when clicking overlay
            singularityOverlay.addEventListener('click', function() {
                astroBody.classList.remove('sidebar-open');
            });
            
            // Handle desktop collapse/expand
            const isCollapsed = localStorage.getItem('nebulaSidebarCollapsed') === 'true';
            if (isCollapsed && window.innerWidth >= 769) {
                astroBody.classList.add('sidebar-collapsed');
            }
            
            // Auto-close sidebar when clicking a link on mobile
            const cosmicLinks = document.querySelectorAll('.celestialMenu a');
            cosmicLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 769) {
                        astroBody.classList.remove('sidebar-open');
                    }
                });
            });
            
            // Handle window resize
            function handleResize() {
                if (window.innerWidth >= 769) {
                    astroBody.classList.remove('sidebar-open');
                }
            }
            
            window.addEventListener('resize', handleResize);
            
            // Swipe to close on mobile
            let touchStartX = 0;
            let touchEndX = 0;
            
            voidSidebar.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, false);
            
            voidSidebar.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                if (touchStartX - touchEndX > 50) {
                    astroBody.classList.remove('sidebar-open');
                }
            }, false);
        });
    </script>
</body>
</html>