<?php
/**
 * Admin Customers — list all registered customers (SRS §3.2.6)
 */
require_once '../db_connection.php';
session_start();
require_once 'admin_auth.php';
requireAdminRole(['order_manager']); // super + order_manager

$admin_username = $_SESSION['admin_username'] ?? '';

// Fetch customers with order counts
$result = $conn->query(
    "SELECT u.id, u.name, u.email, u.phone, u.address, u.created_at,
            COUNT(o.id) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS total_spent
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC"
);

// Count summary
$totalCustomers = 0;
$cRes = $conn->query("SELECT COUNT(*) AS c FROM users");
if ($cRes && ($r = $cRes->fetch_assoc())) $totalCustomers = (int)$r['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Stitch House Admin</title>
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
                <div class="admin-menu-item"><a href="admin_products.php"><i class="fas fa-box"></i><span>Products</span></a></div>
                <div class="admin-menu-item"><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i><span>Orders</span></a></div>
                <div class="admin-menu-item active"><a href="admin_customers.php"><i class="fas fa-users"></i><span>Customers</span></a></div>
                <?php if (adminHasRole(['support'])): ?>
                <div class="admin-menu-item"><a href="admin_tickets.php"><i class="fas fa-life-ring"></i><span>Support Tickets</span></a></div>
                <?php endif; ?>
                <div class="admin-menu-item"><a href="../admin_logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
            </div>
        </div>

        <div class="admin-content">
            <div class="admin-header">
                <div class="admin-title"><h1>Customers</h1></div>
                <div class="admin-user-info">
                    <div class="admin-user-name"><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_username); ?> <small style="opacity:0.6; margin-left:8px;">(<?php echo htmlspecialchars(adminRole()); ?>)</small></div>
                </div>
            </div>

            <div class="orders-toolbar" style="margin-bottom:24px;">
                <span style="background:#1A1A1A; color:#C9A96E; padding:8px 14px; border-radius:20px; font-weight:600;">Total Customers: <?php echo $totalCustomers; ?></span>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
            <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.05);">
                <thead>
                    <tr style="background:#F5F0E8;">
                        <th style="padding:12px; text-align:left;">ID</th>
                        <th style="padding:12px; text-align:left;">Name</th>
                        <th style="padding:12px; text-align:left;">Email</th>
                        <th style="padding:12px; text-align:left;">Phone</th>
                        <th style="padding:12px; text-align:left;">Address</th>
                        <th style="padding:12px; text-align:center;">Orders</th>
                        <th style="padding:12px; text-align:right;">Total Spent</th>
                        <th style="padding:12px; text-align:left;">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($c = $result->fetch_assoc()): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:12px;"><?php echo (int)$c['id']; ?></td>
                        <td style="padding:12px; font-weight:600;"><?php echo htmlspecialchars($c['name']); ?></td>
                        <td style="padding:12px;"><?php echo htmlspecialchars($c['email']); ?></td>
                        <td style="padding:12px;"><?php echo htmlspecialchars($c['phone']); ?></td>
                        <td style="padding:12px; font-size:13px; color:#7A7267; max-width:250px;"><?php echo htmlspecialchars($c['address'] ?? '—'); ?></td>
                        <td style="padding:12px; text-align:center;"><?php echo (int)$c['order_count']; ?></td>
                        <td style="padding:12px; text-align:right; font-weight:600; color:#8B6914;">PKR <?php echo number_format((float)$c['total_spent'], 0); ?></td>
                        <td style="padding:12px; font-size:13px; color:#7A7267;"><?php echo htmlspecialchars(date('d M Y', strtotime($c['created_at']))); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div style="background:#fff; padding:20px; border-radius:8px;">No customers yet.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
