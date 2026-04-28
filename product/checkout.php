<?php
/**
 * Checkout Page - Stitch House
 * Handles order submission and payment
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

$pageTitle = 'Checkout - Stitch House';
$pageStyles = ['checkout.css'];

$cartCount = getCartCount();
$subtotal = getCartSubtotal();
$shipping = 200;
$total = $subtotal + $shipping;
$error = null;

// Process checkout form
if ($_SERVER["REQUEST_METHOD"] == "POST" && !csrfVerify()) {
    $error = "Invalid request token. Please refresh and try again.";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city)) {
        $error = "Please fill in all required fields.";
    } elseif (!isset($_POST['terms'])) {
        $error = "You must agree to the Terms of Service and Privacy Policy.";
    } else {
        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;

        // SDD §3.5 — idempotency check (prevents duplicate orders from double-clicks)
        $idempKey = $_POST['_idempotency_key'] ?? '';
        if ($idempKey && $userId) {
            $existingOrderId = idempotencyLookup($idempKey, $userId, 'checkout.place_order');
            if ($existingOrderId) {
                // Already processed — silently redirect to confirmation
                $_SESSION['order_success'] = true;
                $_SESSION['order_id'] = $existingOrderId;
                header("Location: order-confirmation.php");
                exit;
            }
        }

        $conn->begin_transaction();

        try {
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, phone, address, city, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssds", $userId, $name, $email, $phone, $address, $city, $total, $paymentMethod);

            if (!$stmt->execute()) {
                throw new Exception("Failed to create order");
            }

            $orderId = $conn->insert_id;

            // SDD §3.5 — record idempotency key for this order
            if ($idempKey && $userId) {
                idempotencyStore($idempKey, $userId, 'checkout.place_order', $orderId);
            }

            // SDD §3.5 — consume any active inventory reservations for this user
            if ($userId) consumeReservations($userId);

            // SDD §4.1.8 — seed initial status into the log
            logOrderStatus($orderId, 'pending', null, 'Order placed by customer');

            // Insert order items
            foreach ($_SESSION['finalCart'] as $item) {
                // Revalidate stock before committing (SRS §3.2.3.5)
                if (!hasStock($item['id'], $item['quantity'])) {
                    throw new Exception("'" . $item['name'] . "' is out of stock.");
                }

                $itemTotal = $item['price'] * $item['quantity'];
                $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
                $itemStmt->bind_param("isids", $orderId, $item['id'], $item['quantity'], $item['price'], $itemTotal);
                $itemStmt->execute();

                $orderItemId = $conn->insert_id;

                // Decrement inventory
                decrementStock($item['id'], $item['quantity']);

                // Save measurements if present
                if (isset($item['measurements']) && is_array($item['measurements'])) {
                    $measurements = $item['measurements'];
                    $measurementStmt = $conn->prepare("INSERT INTO order_measurements (order_item_id, chest, waist, hip, shoulder, sleeve_length, trouser_length, kameez_length, neck, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    $chest = getMeasurementValue($measurements['chest'] ?? '')['value'];
                    $waist = getMeasurementValue($measurements['waist'] ?? '')['value'];
                    $hip = getMeasurementValue($measurements['hip'] ?? '')['value'];
                    $shoulder = getMeasurementValue($measurements['shoulder'] ?? '')['value'];
                    $sleeve = getMeasurementValue($measurements['sleeve_length'] ?? '')['value'];
                    $trouser = getMeasurementValue($measurements['trouser_length'] ?? '')['value'];
                    $kameez = getMeasurementValue($measurements['kameez_length'] ?? '')['value'];
                    $neck = getMeasurementValue($measurements['neck'] ?? '')['value'];
                    $notes = getMeasurementValue($measurements['notes'] ?? '')['value'];

                    $measurementStmt->bind_param("isssssssss", $orderItemId, $chest, $waist, $hip, $shoulder, $sleeve, $trouser, $kameez, $neck, $notes);
                    $measurementStmt->execute();
                }

                // Save customization details (SRS §3.4 Customization class)
                if (isset($item['designOptions']) && is_array($item['designOptions'])) {
                    $opts = $item['designOptions'];
                    $fabricType    = $item['name']            ?? null; // product name doubles as fabric label
                    $collarStyle   = $opts['collar']          ?? null;
                    $cuffStyle     = $opts['cuff']            ?? null;
                    $fitPreference = $opts['fit_preference']  ?? 'regular';
                    $unitPrice     = $item['price']           ?? 0;

                    $customStmt = $conn->prepare(
                        "INSERT INTO customization (order_id, order_item_id, fabric_type, collar_style, cuff_style, fit_preference, unit_price)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    $customStmt->bind_param(
                        "iissssd",
                        $orderId, $orderItemId, $fabricType, $collarStyle, $cuffStyle, $fitPreference, $unitPrice
                    );
                    $customStmt->execute();
                }
            }

            // Create user account for guests
            if (!isLoggedIn()) {
                $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $checkStmt->bind_param("s", $email);
                $checkStmt->execute();
                $result = $checkStmt->get_result();

                if ($result->num_rows === 0) {
                    $tempPassword = bin2hex(random_bytes(8));
                    $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

                    $userStmt = $conn->prepare("INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
                    $userStmt->bind_param("sssss", $name, $email, $phone, $address, $hashedPassword);
                    $userStmt->execute();
                    $userId = $conn->insert_id;
                    $_SESSION['temp_password'] = $tempPassword;
                } else {
                    $userId = $result->fetch_assoc()['id'];
                    // Refresh the saved address for returning guest-order users
                    $addrStmt = $conn->prepare("UPDATE users SET address = ? WHERE id = ?");
                    $addrStmt->bind_param("si", $address, $userId);
                    $addrStmt->execute();
                }

                $updateStmt = $conn->prepare("UPDATE orders SET user_id = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $userId, $orderId);
                $updateStmt->execute();
            } else {
                // Logged-in user: keep their saved address in sync
                $addrStmt = $conn->prepare("UPDATE users SET address = ? WHERE id = ?");
                $addrStmt->bind_param("si", $address, $userId);
                $addrStmt->execute();
            }

            $conn->commit();

            // Clear cart and redirect
            $_SESSION['finalCart'] = [];
            $_SESSION['shoppingCart'] = [];
            $_SESSION['order_success'] = true;
            $_SESSION['order_id'] = $orderId;

            header("Location: order-confirmation.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error = "An error occurred while processing your order. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="checkout-section">
        <div class="container">
            <h1>Checkout</h1>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php if (empty($_SESSION['finalCart'])): ?>
            <div class="empty-cart-message">
                <i class="fas fa-receipt"></i>
                <h2>Nothing ready to checkout</h2>
                <p>Your cart is empty. Add fabrics, customize them in My Order, then come back here to place your order.</p>
                <a href="order.php" class="btn-primary"><i class="fas fa-shopping-bag"></i> Browse Fabrics</a>
            </div>
            <?php else: ?>

            <div class="checkout-container">
                <div class="checkout-form">
                    <h2 class="reveal">Shipping Information</h2>
                    <form method="post">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="_idempotency_key" value="<?php echo bin2hex(random_bytes(16)); ?>">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo isLoggedIn() ? e($_SESSION['user_name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo isLoggedIn() ? e($_SESSION['user_email'] ?? '') : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>

                        <h2 class="reveal">Payment Method</h2>
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="cash_on_delivery" name="payment_method" value="cash_on_delivery" checked>
                                <label for="cash_on_delivery">Cash on Delivery</label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer">
                                <label for="bank_transfer">Bank Transfer</label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card">
                                <label for="credit_card">Credit Card</label>
                            </div>
                        </div>

                        <div class="form-group terms-checkbox">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                        </div>

                        <button type="submit" class="btn-primary">Place Order</button>
                    </form>
                </div>

                <div class="order-summary">
                    <h2 class="reveal">Order Summary</h2>
                    <div class="cart-items">
                        <?php foreach ($_SESSION['finalCart'] as $item): ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <img src="<?php echo e(getImagePath($item['image'])); ?>"
                                     alt="<?php echo e($item['name']); ?>"
                                     onerror="this.src='images/placeholder.jpg'">
                            </div>
                            <div class="item-details">
                                <h3><?php echo e($item['name']); ?></h3>
                                <p class="item-price"><?php echo formatPrice($item['price']); ?> x <?php echo $item['quantity']; ?></p>
                            </div>
                            <div class="item-total">
                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo formatPrice($shipping); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
