<?php
/**
 * Admin Orders — SRS §3.2.6, UC-06
 * Order status transitions: pending → processing → in_tailoring → shipped → completed
 */
require_once '../db_connection.php';
require_once '../includes/functions.php';
session_start();
require_once 'admin_auth.php';
requireAdminRole(['order_manager']); // super + order_manager

$admin_username = $_SESSION['admin_username'] ?? '';
$flash = '';

// Valid statuses (must match orders.status enum)
$validStatuses = ['pending','processing','in_tailoring','shipped','completed','cancelled'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!csrfVerify()) {
        $flash = 'Invalid request token. Please refresh and try again.';
    } else {
    $orderId  = (int)($_POST['id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if ($_POST['action'] === 'update_status' && $orderId > 0 && in_array($newStatus, $validStatuses, true)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        if ($stmt->execute()) {
            $flash = "Order #$orderId status updated to " . str_replace('_', ' ', $newStatus) . ".";
        } else {
            $flash = "Failed to update order #$orderId.";
        }
    }
    }
}

// Filter
$filter = $_GET['status'] ?? 'all';
if (!in_array($filter, array_merge(['all'], $validStatuses), true)) $filter = 'all';

$query = "SELECT o.id, o.name AS customer_name, o.email, o.phone, o.address, o.city,
                 o.total_amount, o.payment_method, o.status, o.order_date,
                 u.name AS user_name
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id";
if ($filter !== 'all') {
    $query .= " WHERE o.status = '" . $conn->real_escape_string($filter) . "'";
}
$query .= " ORDER BY FIELD(o.status,'pending','processing','in_tailoring','shipped','completed','cancelled'), o.order_date DESC";
$ordersResult = $conn->query($query);

// Counts per status
$counts = array_fill_keys($validStatuses, 0);
$countRes = $conn->query("SELECT status, COUNT(*) c FROM orders GROUP BY status");
if ($countRes) {
    while ($r = $countRes->fetch_assoc()) {
        $counts[$r['status']] = (int)$r['c'];
    }
}

// Next-status map for action buttons
$nextStatus = [
    'pending'      => 'processing',
    'processing'   => 'in_tailoring',
    'in_tailoring' => 'shipped',
    'shipped'      => 'completed',
];

function statusLabel($s) { return ucwords(str_replace('_',' ',$s)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Stitch House Admin</title>
    <link rel="stylesheet" href="../variables.css">
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../admin-tickets-orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-sidebar-header"><h2>Stitch <span>House</span></h2></div>
            <div class="admin-menu">
                <?php if (adminHasRole(['order_manager','support'])): ?>
                <div class="admin-menu-item"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
                <?php endif; ?>
                <?php if (adminHasRole(['order_manager'])): ?>
                <div class="admin-menu-item active"><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i><span>Orders</span></a></div>
                <?php endif; ?>
                <?php if (adminHasRole(['support'])): ?>
                <div class="admin-menu-item"><a href="admin_tickets.php"><i class="fas fa-life-ring"></i><span>Support Tickets</span></a></div>
                <?php endif; ?>
                <div class="admin-menu-item"><a href="../admin_logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
            </div>
        </div>

        <div class="admin-content">
            <div class="admin-header">
                <div class="admin-title"><h1>Orders</h1></div>
                <div class="admin-user-info">
                    <div class="admin-user-name"><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_username); ?> <small style="opacity:0.6; margin-left:8px;">(<?php echo htmlspecialchars(adminRole()); ?>)</small></div>
                </div>
            </div>

            <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>

            <div class="orders-toolbar">
                <?php
                $total = array_sum($counts);
                $pills = array_merge(['all' => ['All', $total]], array_map(function($s) use ($counts) {
                    return [statusLabel($s), $counts[$s]];
                }, array_combine($validStatuses, $validStatuses)));
                foreach ($pills as $key => [$label, $n]):
                ?>
                    <a href="?status=<?php echo $key; ?>" class="<?php echo $filter === $key ? 'active' : ''; ?>"><?php echo $label; ?><span class="cnt">(<?php echo $n; ?>)</span></a>
                <?php endforeach; ?>
            </div>

            <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                <?php while ($o = $ordersResult->fetch_assoc()): ?>
                    <?php
                    // Fetch order items (join products for a human-readable name)
                    $itemsStmt = $conn->prepare(
                        "SELECT oi.product_id, oi.quantity, oi.price, oi.total, p.name AS product_name
                         FROM order_items oi
                         LEFT JOIN products p ON p.id = oi.product_id
                         WHERE oi.order_id = ?"
                    );
                    $itemsStmt->bind_param("i", $o['id']);
                    $itemsStmt->execute();
                    $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

                    // Fetch customizations
                    $custStmt = $conn->prepare("SELECT fabric_type, collar_style, cuff_style, fit_preference FROM customization WHERE order_id = ?");
                    $custStmt->bind_param("i", $o['id']);
                    $custStmt->execute();
                    $customs = $custStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <div class="order-row s-<?php echo htmlspecialchars($o['status']); ?>">
                        <div class="order-header">
                            <div>
                                <span class="order-id">Order #<?php echo (int)$o['id']; ?></span>
                                <span class="status-chip"><?php echo statusLabel($o['status']); ?></span>
                            </div>
                            <div style="font-weight:700; color:#8B6914;">PKR <?php echo number_format((float)$o['total_amount'], 0); ?></div>
                        </div>
                        <div class="order-meta">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($o['customer_name']); ?>
                            · <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($o['email']); ?>
                            · <i class="fas fa-phone"></i> <?php echo htmlspecialchars($o['phone']); ?>
                            · <i class="fas fa-clock"></i> <?php echo htmlspecialchars($o['order_date']); ?>
                        </div>
                        <div class="order-meta">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($o['address']); ?>, <?php echo htmlspecialchars($o['city']); ?>
                            · <i class="fas fa-credit-card"></i> <?php echo htmlspecialchars(statusLabel($o['payment_method'])); ?>
                        </div>

                        <div class="order-actions">
                            <button type="button" class="btn-toggle" onclick="this.closest('.order-row').classList.toggle('expanded')">
                                <i class="fas fa-eye"></i> Details (<?php echo count($items); ?> items)
                            </button>

                            <?php if (isset($nextStatus[$o['status']])): ?>
                            <form method="post" class="inline">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $nextStatus[$o['status']]; ?>">
                                <button type="submit" class="btn-advance">
                                    <i class="fas fa-arrow-right"></i> Advance to <?php echo statusLabel($nextStatus[$o['status']]); ?>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if (!in_array($o['status'], ['completed','cancelled'], true)): ?>
                            <form method="post" class="inline" onsubmit="return confirm('Cancel this order?');">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn-cancel"><i class="fas fa-times"></i> Cancel</button>
                            </form>
                            <?php endif; ?>
                        </div>

                        <div class="order-details">
                            <strong>Items:</strong>
                            <table class="order-items-table">
                                <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                                <tbody>
                                <?php foreach ($items as $it): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($it['product_name'] ?? $it['product_id']); ?>
                                            <small style="color:#7A7267; font-family:monospace;">(<?php echo htmlspecialchars($it['product_id']); ?>)</small>
                                        </td>
                                        <td><?php echo (int)$it['quantity']; ?></td>
                                        <td>PKR <?php echo number_format((float)$it['price'], 0); ?></td>
                                        <td>PKR <?php echo number_format((float)$it['total'], 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (!empty($customs)): ?>
                            <div style="margin-top:10px;"><strong>Customization:</strong></div>
                            <ul style="margin:4px 0 0 18px;">
                                <?php foreach ($customs as $c): ?>
                                    <li>
                                        <?php echo htmlspecialchars($c['fabric_type'] ?? '—'); ?> ·
                                        Collar: <?php echo htmlspecialchars($c['collar_style'] ?? '—'); ?> ·
                                        Cuff: <?php echo htmlspecialchars($c['cuff_style'] ?? '—'); ?> ·
                                        Fit: <?php echo htmlspecialchars($c['fit_preference'] ?? '—'); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="order-row">No orders found for this filter.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
