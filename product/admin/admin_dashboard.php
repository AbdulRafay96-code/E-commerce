
<?php
require_once '../db_connection.php';
session_start();
require_once 'admin_auth.php';
requireAdmin(); // any logged-in admin may see the dashboard; role filters menu items below

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];

// Aggregate stats — single query batch for dashboard cards
$stats = [
    'total_products'  => 0,
    'total_customers' => 0,
    'total_orders'    => 0,
    'pending_orders'  => 0,
    'open_tickets'    => 0,
    'revenue'         => 0.0,
];

if ($r = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()) {
    $stats['total_products'] = (int)$r['c'];
}
if ($r = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()) {
    $stats['total_customers'] = (int)$r['c'];
}
if ($r = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()) {
    $stats['total_orders'] = (int)$r['c'];
}
if ($r = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status = 'pending'")->fetch_assoc()) {
    $stats['pending_orders'] = (int)$r['c'];
}
if ($r = $conn->query("SELECT COUNT(*) AS c FROM contact_messages WHERE status IN ('open','in_progress')")->fetch_assoc()) {
    $stats['open_tickets'] = (int)$r['c'];
}
// Revenue = sum of non-cancelled orders
if ($r = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS s FROM orders WHERE status <> 'cancelled'")->fetch_assoc()) {
    $stats['revenue'] = (float)$r['s'];
}

// Recent orders (guard against null user_id — guest checkout)
$recentOrders = [];
$sql = "SELECT o.id, o.order_date, o.total_amount, o.status,
               COALESCE(u.name, o.name) AS customer_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC
        LIMIT 5";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Stitch House</title>
    <link rel="stylesheet" href="../variables.css">
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;600;700&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2>Stitch <span>House</span></h2>
            </div>
            <div class="admin-menu">
                <div class="admin-menu-item active">
                    <a href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <?php if (adminHasRole(['order_manager'])): ?>
                <div class="admin-menu-item">
                    <a href="admin_products.php">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </div>
                <div class="admin-menu-item">
                    <a href="admin_orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </div>
                <div class="admin-menu-item">
                    <a href="admin_customers.php">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php if (adminHasRole(['support'])): ?>
                <div class="admin-menu-item">
                    <a href="admin_tickets.php">
                        <i class="fas fa-life-ring"></i>
                        <span>Support Tickets</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php if (adminHasRole([])): // super only ?>
                <div class="admin-menu-item">
                    <a href="admin_settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
                <?php endif; ?>
                <div class="admin-menu-item">
                    <a href="../admin_logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="admin-header">
                <div class="admin-title">
                    <h1>Dashboard</h1>
                </div>
                <div class="admin-user-info">
                    <div class="admin-user-name">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_username); ?>
                    </div>
                    <div class="admin-logout">
                        <a href="../admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-info">
                        <h3>Total Products</h3>
                        <p><?php echo $stats['total_products']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <p><?php echo $stats['total_orders']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3>Total Customers</h3>
                        <p><?php echo $stats['total_customers']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-info">
                        <h3>Revenue</h3>
                        <p>PKR <?php echo number_format($stats['revenue'], 0); ?></p>
                    </div>
                </div>

                <?php if (adminHasRole(['order_manager'])): ?>
                <div class="stat-card" style="border-left: 4px solid #8B6914;">
                    <div class="stat-icon" style="background: #8B6914;"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3>Pending Orders</h3>
                        <p>
                            <?php echo $stats['pending_orders']; ?>
                            <?php if ($stats['pending_orders'] > 0): ?>
                                <a href="admin_orders.php?status=pending" style="font-size:12px; color:#8B6914; display:block; margin-top:4px;">View →</a>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (adminHasRole(['support'])): ?>
                <div class="stat-card" style="border-left: 4px solid #8B1538;">
                    <div class="stat-icon" style="background: #8B1538;"><i class="fas fa-life-ring"></i></div>
                    <div class="stat-info">
                        <h3>Open Tickets</h3>
                        <p>
                            <?php echo $stats['open_tickets']; ?>
                            <?php if ($stats['open_tickets'] > 0): ?>
                                <a href="admin_tickets.php?status=open" style="font-size:12px; color:#8B1538; display:block; margin-top:4px;">View →</a>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Orders -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Recent Orders</h2>
                    <a href="admin_orders.php">View All</a>
                </div>

                <?php if (!empty($recentOrders)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                            <td>₨ <?php echo number_format($order['total_amount'], 0); ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch (strtolower($order['status'])) {
                                    case 'pending':
                                        $statusClass = 'status-pending';
                                        break;
                                    case 'processing':
                                        $statusClass = 'status-processing';
                                        break;
                                    case 'completed':
                                        $statusClass = 'status-completed';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'status-cancelled';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="admin-action-btn view" onclick="location.href='admin_orders.php?view=<?php echo $order['id']; ?>'">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No recent orders found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>