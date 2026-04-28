<?php
/**
 * Admin Products — manage catalog + stock (SRS §3.2.3.5, §3.2.6)
 */
require_once '../db_connection.php';
require_once '../includes/functions.php';
session_start();
require_once 'admin_auth.php';
requireAdminRole(['order_manager']); // super + order_manager

$admin_username = $_SESSION['admin_username'] ?? '';
$flash = '';

// Handle product update (name, price, stock — all editable)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!csrfVerify()) {
        $flash = 'Invalid request token. Please refresh and try again.';
    } elseif ($_POST['action'] === 'update_product') {
        $productId = $_POST['id'] ?? '';
        $newName   = trim($_POST['name'] ?? '');
        $newPrice  = max(0, (float)($_POST['price'] ?? 0));
        $newStock  = max(0, (int)($_POST['stock_quantity'] ?? 0));

        if ($productId === '' || $newName === '') {
            $flash = "Name is required.";
        } else {
            // Capture before-state for audit diff
            $beforeRow = $conn->query("SELECT name, price, stock_quantity FROM products WHERE id = '" . $conn->real_escape_string($productId) . "'")->fetch_assoc();

            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock_quantity = ? WHERE id = ?");
            $stmt->bind_param("sdis", $newName, $newPrice, $newStock, $productId);
            if ($stmt->execute()) {
                // SDD §4.1.9 — audit log
                auditLog('product.update', 'product', null, [
                    'product_id' => $productId,
                    'before' => $beforeRow,
                    'after' => ['name' => $newName, 'price' => $newPrice, 'stock_quantity' => $newStock],
                ]);
                $flash = "$productId updated (name, price, stock).";
            } else {
                $flash = "Failed to update $productId.";
            }
        }
    }
}

// Fetch products grouped by category — include reorder_level (SDD §4.1.4)
$result = $conn->query(
    "SELECT id, name, price, stock_quantity, reorder_level, category, image
     FROM products
     ORDER BY category, id"
);

$productsByCategory = [];
while ($row = $result->fetch_assoc()) {
    $productsByCategory[$row['category']][] = $row;
}

// Overall counts — low_stock now uses per-product reorder_level (SDD §3.7)
$counts = ['total'=>0,'out_of_stock'=>0,'low_stock'=>0];
$cRes = $conn->query(
    "SELECT COUNT(*) total,
            SUM(stock_quantity = 0) out_of_stock,
            SUM(stock_quantity > 0 AND stock_quantity <= reorder_level) low_stock
     FROM products"
);
if ($cRes && ($row = $cRes->fetch_assoc())) {
    $counts = [
        'total' => (int)$row['total'],
        'out_of_stock' => (int)$row['out_of_stock'],
        'low_stock' => (int)$row['low_stock'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Stitch House Admin</title>
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
                <div class="admin-menu-item"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
                <div class="admin-menu-item active"><a href="admin_products.php"><i class="fas fa-box"></i><span>Products</span></a></div>
                <div class="admin-menu-item"><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i><span>Orders</span></a></div>
                <div class="admin-menu-item"><a href="admin_customers.php"><i class="fas fa-users"></i><span>Customers</span></a></div>
                <?php if (adminHasRole(['support'])): ?>
                <div class="admin-menu-item"><a href="admin_tickets.php"><i class="fas fa-life-ring"></i><span>Support Tickets</span></a></div>
                <?php endif; ?>
                <div class="admin-menu-item"><a href="../admin_logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
            </div>
        </div>

        <div class="admin-content">
            <div class="admin-header">
                <div class="admin-title"><h1>Products</h1></div>
                <div class="admin-user-info">
                    <div class="admin-user-name"><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_username); ?> <small style="opacity:0.6; margin-left:8px;">(<?php echo htmlspecialchars(adminRole()); ?>)</small></div>
                </div>
            </div>

            <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>

            <div class="orders-toolbar" style="margin-bottom:24px;">
                <span style="background:#1A1A1A; color:#C9A96E; padding:8px 14px; border-radius:20px; font-weight:600;">Total: <?php echo $counts['total']; ?></span>
                <span style="background:#A4343A; color:#fff; padding:8px 14px; border-radius:20px; font-weight:600;">Out of Stock: <?php echo $counts['out_of_stock']; ?></span>
                <span style="background:#C9A96E; color:#1A1A1A; padding:8px 14px; border-radius:20px; font-weight:600;">Low Stock: <?php echo $counts['low_stock']; ?></span>
            </div>

            <?php foreach ($productsByCategory as $cat => $rows): ?>
                <h3 style="font-family:'Playfair Display',serif; color:#1A1A1A; margin-top:24px;"><?php echo htmlspecialchars(strtoupper($cat)); ?></h3>
                <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.05);">
                    <thead>
                        <tr style="background:#F5F0E8;">
                            <th style="padding:10px; text-align:left; width:100px;">ID</th>
                            <th style="padding:10px; text-align:left;">Name</th>
                            <th style="padding:10px; text-align:right; width:130px;">Price (PKR)</th>
                            <th style="padding:10px; text-align:center; width:110px;">Stock</th>
                            <th style="padding:10px; text-align:center; width:80px;">Status</th>
                            <th style="padding:10px; text-align:left; width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $p):
                            $isOut = ((int)$p['stock_quantity']) <= 0;
                            $reorderLvl = max(1, (int)($p['reorder_level'] ?? 5));
                            $isLow = !$isOut && ((int)$p['stock_quantity']) <= $reorderLvl;
                            $formId = 'pf-' . $p['id'];
                        ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:10px; font-family:monospace; color:#7A7267;">
                                <form id="<?php echo $formId; ?>" method="post" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="update_product">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id']); ?>">
                                </form>
                                <?php echo htmlspecialchars($p['id']); ?>
                            </td>
                            <td style="padding:10px;">
                                <input form="<?php echo $formId; ?>" type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px;">
                            </td>
                            <td style="padding:10px;">
                                <input form="<?php echo $formId; ?>" type="number" name="price" value="<?php echo (int)$p['price']; ?>" min="0" step="1" style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; text-align:right;">
                            </td>
                            <td style="padding:10px;">
                                <input form="<?php echo $formId; ?>" type="number" name="stock_quantity" value="<?php echo (int)$p['stock_quantity']; ?>" min="0" title="Reorder threshold: <?php echo $reorderLvl; ?>" style="width:100%; padding:6px; border:1px solid <?php echo $isLow ? '#C9A96E' : '#ddd'; ?>; border-radius:4px; text-align:center; <?php echo $isLow ? 'background:#FFF8E1;' : ''; ?>">
                            </td>
                            <td style="padding:10px; text-align:center;">
                                <?php if ($isOut): ?>
                                    <span class="status-chip" style="background:#A4343A; color:#fff;" title="Out of stock — reorder needed">⚠ Out</span>
                                <?php elseif ($isLow): ?>
                                    <span class="status-chip" style="background:#C9A96E; color:#1A1A1A;" title="Below reorder threshold (<?php echo $reorderLvl; ?>) — reorder soon">⚠ Reorder</span>
                                <?php else: ?>
                                    <span class="status-chip" style="background:#2D5A4A; color:#fff;">✓ OK</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:10px;">
                                <button form="<?php echo $formId; ?>" type="submit" class="btn-advance" style="padding:6px 12px; width:100%;">Save</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
