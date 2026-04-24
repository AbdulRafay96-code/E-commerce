<?php
/**
 * Common Functions for Stitch House
 * Following DRY principle - centralized utility functions
 */

/**
 * Calculate cart item count from session
 * @return int Total number of items in cart
 */
function getCartCount() {
    $count = 0;

    if (isset($_SESSION['finalCart']) && is_array($_SESSION['finalCart'])) {
        foreach ($_SESSION['finalCart'] as $item) {
            $count += isset($item['quantity']) ? (int)$item['quantity'] : 0;
        }
    }

    return $count;
}

/**
 * Calculate My Order (pre-customization) item count from session
 * @return int Total number of items awaiting customization
 */
function getMyOrderCount() {
    $count = 0;

    if (isset($_SESSION['shoppingCart']) && is_array($_SESSION['shoppingCart'])) {
        foreach ($_SESSION['shoppingCart'] as $item) {
            $count += isset($item['quantity']) ? (int)$item['quantity'] : 0;
        }
    }

    return $count;
}

/**
 * Calculate cart subtotal
 * @return float Subtotal amount
 */
function getCartSubtotal() {
    $subtotal = 0;

    if (isset($_SESSION['finalCart']) && is_array($_SESSION['finalCart'])) {
        foreach ($_SESSION['finalCart'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
    }

    return $subtotal;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user name
 * @return string
 */
function getUserName() {
    return isLoggedIn() ? ($_SESSION['user_name'] ?? '') : '';
}

/**
 * Get current user ID
 * @return int|null
 */
function getUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

/**
 * Sanitize output for HTML
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get correct image path with fallback
 * @param string $imagePath
 * @return string
 */
function getImagePath($imagePath) {
    $imageName = basename($imagePath);
    $cleanImageName = str_replace(['images/', 'images\\'], '', $imageName);
    $filename = pathinfo($cleanImageName, PATHINFO_FILENAME);

    $variants = [
        "images/{$cleanImageName}",
        "images/{$filename}.jpg",
        "images/{$filename}.webp",
        "images/{$filename}.png"
    ];

    foreach ($variants as $variant) {
        if (file_exists($variant)) {
            return $variant;
        }
    }

    return "images/{$filename}.jpg";
}

/**
 * Format price in PKR
 * @param float $amount
 * @return string
 */
function formatPrice($amount) {
    return 'PKR ' . number_format($amount, 0);
}

/**
 * Initialize session arrays if not set
 */
function initializeCart() {
    if (!isset($_SESSION['finalCart']) || !is_array($_SESSION['finalCart'])) {
        $_SESSION['finalCart'] = [];
    }
    if (!isset($_SESSION['shoppingCart']) || !is_array($_SESSION['shoppingCart'])) {
        $_SESSION['shoppingCart'] = [];
    }
}

/**
 * Get product stock quantity from DB. Returns null if product not found.
 * SRS §3.2.3.5 — out-of-stock handling.
 */
function getProductStock($productId) {
    global $conn;
    if (!$conn || !$productId) return null;
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("s", $productId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? (int)$row['stock_quantity'] : null;
}

/**
 * Check if a product has enough stock for requested quantity.
 * Unknown products (null stock) are treated as in-stock for backward compatibility.
 */
function hasStock($productId, $requestedQty = 1) {
    $stock = getProductStock($productId);
    if ($stock === null) return true; // product not catalogued in DB
    return $stock >= $requestedQty;
}

/**
 * Decrement product stock atomically. Returns true on success.
 */
function decrementStock($productId, $qty) {
    global $conn;
    if (!$conn || !$productId || $qty <= 0) return false;
    $stmt = $conn->prepare(
        "UPDATE products SET stock_quantity = stock_quantity - ?
         WHERE id = ? AND stock_quantity >= ?"
    );
    if (!$stmt) return false;
    $stmt->bind_param("isi", $qty, $productId, $qty);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

/**
 * Get measurement value from array or direct value
 * @param mixed $measurement
 * @return array ['value' => string, 'confidence' => float]
 */
function getMeasurementValue($measurement) {
    if (is_array($measurement)) {
        return [
            'value' => $measurement['value'] ?? '',
            'confidence' => $measurement['confidence'] ?? 0.8
        ];
    }
    return [
        'value' => $measurement,
        'confidence' => 0.8
    ];
}

/**
 * Get confidence color based on value
 * @param float $confidence
 * @return string CSS color
 */
function getConfidenceColor($confidence) {
    if ($confidence >= 0.85) return '#2D5A4A';
    if ($confidence >= 0.75) return '#D4A017';
    return '#A4343A';
}

/**
 * CSRF token — lazily generated once per session.
 * Protects state-changing POST forms (SRS §3.5.4 Security).
 */
function csrfToken() {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/**
 * Hidden input for forms — drop into any POST <form>.
 */
function csrfField() {
    return '<input type="hidden" name="_csrf" value="' . e(csrfToken()) . '">';
}

/**
 * Verify a submitted CSRF token. Returns true on match.
 * Use on every state-changing POST handler.
 */
function csrfVerify() {
    $submitted = $_POST['_csrf'] ?? '';
    return !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $submitted);
}

/**
 * Regenerate session ID — call on login to prevent session fixation.
 */
function sessionRegenerate() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
