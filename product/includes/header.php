<?php
/**
 * Common Header Component
 * Include this at the top of each page after session_start()
 */

// Get common variables
$logged_in = isLoggedIn();
$user_name = getUserName();
$cartCount = getCartCount();
$myOrderCount = getMyOrderCount();
?>
<!-- Side Navigation -->
<div class="side-nav">
    <div class="side-nav-content">
        <div class="side-nav-header">
            <div class="logo-container">
                <h2 class="logo-text">Stitch House</h2>
            </div>
            <button class="close-sidenav">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="side-nav-links">
            <ul>
                <li><a href="order.php" class="shop-link"><i class="fas fa-shopping-bag"></i>Shop Now</a></li>
                <li><a href="order.php#cotton-section"><i class="fas fa-tshirt"></i>Cotton Fabrics</a></li>
                <li><a href="order.php#lattha-section"><i class="fas fa-scroll"></i>Lattha Fabrics</a></li>
                <li><a href="order.php#karandi-section"><i class="fas fa-layer-group"></i>Karandi Fabrics</a></li>
                <li><a href="order.php#boski-section"><i class="fas fa-square"></i>Boski Fabrics</a></li>
                <li><a href="order.php#linen-section"><i class="fas fa-wind"></i>Linen Fabrics</a></li>
                <li><a href="order.php#washandwear-section"><i class="fas fa-tshirt"></i>Wash and Wear</a></li>
                <li><a href="order.php#silk-section"><i class="fas fa-stream"></i>Silk Fabrics</a></li>
                <li><a href="order.php#khaddar-section"><i class="fas fa-border-all"></i>Khaddar Fabrics</a></li>
            </ul>
        </div>
        <div class="side-nav-footer">
            <div class="social-icons">
                <a href="#" class="facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" class="instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" class="pinterest"><i class="fab fa-pinterest"></i></a>
                <a href="#" class="youtube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
</div>
<div class="side-nav-backdrop"></div>

<header>
    <div class="container">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <div class="site-title">
            <a href="index.php">
                <h1 class="logo-text">Stitch House</h1>
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="nav-link<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? ' active' : ''; ?>">Home</a></li>
                <li><a href="order.php" class="nav-link<?php echo basename($_SERVER['PHP_SELF']) === 'order.php' ? ' active' : ''; ?>">Shop Now</a></li>
                <li><a href="myorder.php" class="nav-link<?php echo basename($_SERVER['PHP_SELF']) === 'myorder.php' ? ' active' : ''; ?>">My Order</a></li>
                <li><a href="contact.php" class="nav-link<?php echo basename($_SERVER['PHP_SELF']) === 'contact.php' ? ' active' : ''; ?>">Contact Us</a></li>
            </ul>
        </nav>
        <div class="header-actions">
            <?php if (!$logged_in): ?>
            <div class="login-link">
                <a href="login.php">
                    <i class="fas fa-user"></i>
                </a>
            </div>
            <?php endif; ?>

            <div class="myorder-link">
                <a href="myorder.php" title="My Order">
                    <i class="fas fa-cut"></i>
                    <span class="myorder-count"><?php echo $myOrderCount; ?></span>
                </a>
            </div>

            <div class="cart-link" id="cart-drawer-trigger">
                <a href="cart.php" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo $cartCount; ?></span>
                </a>
            </div>

            <?php if ($logged_in): ?>
            <div class="user-dropdown">
                <button class="user-dropdown-btn" onclick="toggleUserDropdown(this)">
                    <i class="fas fa-user-circle"></i>
                    <span class="user-name"><?php echo e($user_name); ?></span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </button>
                <div class="user-dropdown-content">
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> My Account</a>
                    <a href="dashboard.php?tab=orders"><i class="fas fa-box"></i> Order History</a>
                    <a href="dashboard.php?tab=measurements"><i class="fas fa-ruler"></i> My Measurements</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Cart Drawer -->
<div class="cart-drawer-backdrop" id="cart-drawer-backdrop"></div>
<div class="cart-drawer" id="cart-drawer">
    <div class="cart-drawer-header">
        <h3>Your Cart <span class="cart-drawer-count">(<?php echo $cartCount; ?> items)</span></h3>
        <button class="cart-drawer-close" id="cart-drawer-close">&times;</button>
    </div>
    <div class="cart-drawer-items" id="cart-drawer-items">
        <?php if (empty($_SESSION['finalCart'])): ?>
        <div class="cart-drawer-empty">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
        </div>
        <?php else: ?>
        <?php foreach ($_SESSION['finalCart'] as $index => $item): ?>
        <div class="cart-drawer-item" data-index="<?php echo $index; ?>">
            <img src="<?php echo e(getImagePath($item['image'])); ?>" alt="<?php echo e($item['name']); ?>" class="cart-drawer-item-img" onerror="this.src='images/placeholder.jpg'">
            <div class="cart-drawer-item-info">
                <h4><?php echo e($item['name']); ?></h4>
                <span class="drawer-item-price"><?php echo formatPrice($item['price']); ?></span>
                <div class="cart-drawer-item-qty">
                    <button onclick="updateDrawerQty(<?php echo $index; ?>, -1)">-</button>
                    <span><?php echo $item['quantity']; ?></span>
                    <button onclick="updateDrawerQty(<?php echo $index; ?>, 1)">+</button>
                </div>
            </div>
            <button class="cart-drawer-item-remove" onclick="removeDrawerItem(<?php echo $index; ?>)">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="cart-drawer-footer">
        <div class="cart-drawer-total">
            <span>Subtotal</span>
            <span class="total-amount"><?php echo formatPrice(getCartSubtotal()); ?></span>
        </div>
        <a href="cart.php" class="cart-drawer-btn checkout">View Cart & Checkout</a>
        <button class="cart-drawer-btn continue" id="cart-drawer-continue">Continue Shopping</button>
    </div>
</div>
