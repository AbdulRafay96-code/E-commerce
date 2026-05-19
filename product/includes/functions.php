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

// ============================================================
// AUDIT LOGGING (SDD §4.1.9 — admin action audit trail)
// ============================================================

/**
 * Append a row to audit_logs. Call from any admin-side state change.
 * Silently no-ops if the table doesn't exist (safe pre-migration).
 *
 * @param string   $action      e.g. 'order.status_change', 'product.create'
 * @param string   $entityType  e.g. 'order', 'product', 'customer'
 * @param int|null $entityId    primary key of the affected row
 * @param mixed    $details     scalar or array — array is JSON-encoded
 */
function auditLog($action, $entityType = null, $entityId = null, $details = null) {
    global $conn;
    if (!isset($conn) || !$conn) return;

    $adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $detailsStr = is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : ($details === null ? null : (string)$details);

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, ip_address)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param('ississ', $adminId, $action, $entityType, $entityId, $detailsStr, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Append a row to order_status_logs (SDD §4.1.8). Called whenever an order's
 * status changes — by admin or by the system on order creation.
 */
function logOrderStatus($orderId, $status, $changedBy = null, $notes = null) {
    global $conn;
    if (!isset($conn) || !$conn) return;
    $stmt = $conn->prepare(
        "INSERT INTO order_status_logs (order_id, status, changed_by, notes)
         VALUES (?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param('isis', $orderId, $status, $changedBy, $notes);
        $stmt->execute();
        $stmt->close();
    }
}

// ============================================================
// LOGIN RATE LIMITING (NFR-SEC, SDD §3.5)
// ============================================================

/**
 * Record a login attempt (success or failure) for throttling.
 */
function recordLoginAttempt($identifier, $type = 'user', $success = false) {
    global $conn;
    if (!isset($conn) || !$conn) return;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $successInt = $success ? 1 : 0;
    $stmt = $conn->prepare(
        "INSERT INTO login_attempts (identifier, attempt_type, success, ip_address)
         VALUES (?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param('ssis', $identifier, $type, $successInt, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Check if login is currently locked out due to too many recent failures.
 * Default: 5 failed attempts within 15 minutes triggers a 15-minute lockout.
 *
 * @return array ['locked' => bool, 'retry_after_seconds' => int|null, 'attempts_left' => int]
 */
function checkLoginRateLimit($identifier, $type = 'user', $maxAttempts = 5, $windowMin = 15) {
    global $conn;
    if (!isset($conn) || !$conn) return ['locked' => false, 'retry_after_seconds' => null, 'attempts_left' => $maxAttempts];

    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS fails, MAX(attempted_at) AS last_attempt
         FROM login_attempts
         WHERE identifier = ?
           AND attempt_type = ?
           AND success = 0
           AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
    );
    $stmt->bind_param('ssi', $identifier, $type, $windowMin);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $fails = (int)($row['fails'] ?? 0);
    if ($fails >= $maxAttempts) {
        $lastTs = strtotime($row['last_attempt']);
        $unlockTs = $lastTs + ($windowMin * 60);
        $retryAfter = max(0, $unlockTs - time());
        return ['locked' => true, 'retry_after_seconds' => $retryAfter, 'attempts_left' => 0];
    }
    return ['locked' => false, 'retry_after_seconds' => null, 'attempts_left' => $maxAttempts - $fails];
}

// ============================================================
// IDEMPOTENCY (SDD §3.5 — duplicate-submission protection)
// ============================================================

/**
 * Check if an idempotency key was already used. If yes, returns the previous
 * result_id (e.g. previously-created order_id) so the caller can return that
 * instead of creating a duplicate.
 *
 * @return int|null  prior result_id if seen, null if first-time
 */
function idempotencyLookup($clientKey, $userId, $endpoint) {
    global $conn;
    if (empty($clientKey)) return null;
    $hash = hash('sha256', $clientKey . '|' . $userId . '|' . $endpoint);

    $stmt = $conn->prepare("SELECT result_id FROM idempotency_keys WHERE key_hash = ? LIMIT 1");
    $stmt->bind_param('s', $hash);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['result_id'] : null;
}

/**
 * Record that a key was used and what result it produced.
 */
function idempotencyStore($clientKey, $userId, $endpoint, $resultId) {
    global $conn;
    if (empty($clientKey)) return;
    $hash = hash('sha256', $clientKey . '|' . $userId . '|' . $endpoint);
    $stmt = $conn->prepare(
        "INSERT INTO idempotency_keys (key_hash, user_id, endpoint, result_id)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE result_id = VALUES(result_id)"
    );
    $stmt->bind_param('sisi', $hash, $userId, $endpoint, $resultId);
    $stmt->execute();
    $stmt->close();
}

// ============================================================
// INVENTORY RESERVATIONS (SDD §3.5 — temporary stock locks)
// ============================================================

/**
 * Reserve N units of a product for a user during checkout.
 * Reservations expire after $minutes (default 15) — past that the stock frees up.
 */
function reserveInventory($productId, $userId, $quantity, $minutes = 15) {
    global $conn;
    if (!isset($conn) || !$conn) return false;
    $stmt = $conn->prepare(
        "INSERT INTO inventory_reservations (product_id, user_id, quantity, expires_at)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))"
    );
    $stmt->bind_param('siii', $productId, $userId, $quantity, $minutes);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/**
 * Mark all reservations for a user as consumed (typically on successful order).
 */
function consumeReservations($userId) {
    global $conn;
    if (!isset($conn) || !$conn) return;
    $stmt = $conn->prepare(
        "UPDATE inventory_reservations SET consumed = 1
         WHERE user_id = ? AND consumed = 0 AND expires_at > NOW()"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Compute effective stock = stock_quantity - currently active reservations from OTHER users.
 */
function effectiveStock($productId, $excludeUserId = 0) {
    global $conn;
    if (!isset($conn) || !$conn) return 0;
    $stmt = $conn->prepare(
        "SELECT p.stock_quantity
              - COALESCE((SELECT SUM(r.quantity) FROM inventory_reservations r
                          WHERE r.product_id = p.id
                            AND r.consumed = 0
                            AND r.expires_at > NOW()
                            AND r.user_id != ?), 0) AS effective
         FROM products p WHERE p.id = ?"
    );
    $stmt->bind_param('is', $excludeUserId, $productId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['effective'] ?? 0);
}
