<?php
/**
 * Customer Dashboard — SRS §3.2.5 (Account Management), §3.4 Customer class
 * Three tabs: Profile (updateProfile), Orders (viewOrderHistory), Measurements (CRUD).
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

// Gate: customer must be logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId   = (int)getUserId();
$pageTitle = 'My Account - Stitch House';
$pageStyles = ['dashboard.css'];

$tab   = $_GET['tab'] ?? 'profile';
$tab   = in_array($tab, ['profile','orders','measurements'], true) ? $tab : 'profile';
$flash = '';
$error = '';

// Welcome flash for freshly registered users
if (isset($_GET['welcome'])) {
    $flash = 'Welcome to Stitch House, ' . htmlspecialchars(getUserName()) . '! Your account is ready. Take a minute to add your measurements — it makes future orders one-click.';
}

// ---- Handle POST actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) {
        $error = 'Invalid request token. Please refresh and try again.';
    } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || $email === '' || $phone === '') {
            $error = 'Name, email, and phone are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if email is taken by another user
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
            $stmt->bind_param("si", $email, $userId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'That email is already in use by another account.';
            } else {
                $stmt = $conn->prepare(
                    "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?"
                );
                $stmt->bind_param("ssssi", $name, $email, $phone, $address, $userId);
                if ($stmt->execute()) {
                    $_SESSION['user_name']  = $name;
                    $_SESSION['user_email'] = $email;
                    $flash = 'Profile updated successfully.';
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
        $tab = 'profile';
    }

    elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $error = 'Please fill in all password fields.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if (!$row || !password_verify($current, $row['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hash, $userId);
                if ($stmt->execute()) {
                    $flash = 'Password changed successfully.';
                } else {
                    $error = 'Failed to change password.';
                }
            }
        }
        $tab = 'profile';
    }

    elseif ($action === 'save_measurement') {
        $measurementId = (int)($_POST['measurement_id'] ?? 0);
        $label = trim($_POST['label'] ?? 'Default');
        if ($label === '') $label = 'Default';
        $fields = ['chest','waist','hip','shoulder','sleeve_length','trouser_length','kameez_length','neck'];
        $values = [];
        foreach ($fields as $f) $values[$f] = trim($_POST[$f] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($measurementId > 0) {
            $stmt = $conn->prepare(
                "UPDATE measurements SET label=?, chest=?, waist=?, hip=?, shoulder=?,
                 sleeve_length=?, trouser_length=?, kameez_length=?, neck=?, notes=?, captured_via='manual'
                 WHERE id=? AND user_id=?"
            );
            $stmt->bind_param("ssssssssssii",
                $label, $values['chest'], $values['waist'], $values['hip'], $values['shoulder'],
                $values['sleeve_length'], $values['trouser_length'], $values['kameez_length'],
                $values['neck'], $notes, $measurementId, $userId
            );
            $stmt->execute();
            $flash = 'Measurement updated.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO measurements
                 (user_id, label, chest, waist, hip, shoulder, sleeve_length, trouser_length, kameez_length, neck, notes, captured_via)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'manual')"
            );
            $stmt->bind_param("issssssssss",
                $userId, $label, $values['chest'], $values['waist'], $values['hip'], $values['shoulder'],
                $values['sleeve_length'], $values['trouser_length'], $values['kameez_length'],
                $values['neck'], $notes
            );
            $stmt->execute();
            $flash = 'Measurement saved.';
        }
        $tab = 'measurements';
    }

    elseif ($action === 'delete_measurement') {
        $measurementId = (int)($_POST['measurement_id'] ?? 0);
        if ($measurementId > 0) {
            $stmt = $conn->prepare("DELETE FROM measurements WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $measurementId, $userId);
            $stmt->execute();
            $flash = 'Measurement deleted.';
        }
        $tab = 'measurements';
    }
    } // end else (csrfVerify)
}

// ---- Load data for view ----
$stmt = $conn->prepare("SELECT id, name, email, phone, address, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Orders with item counts
$orders = [];
$stmt = $conn->prepare(
    "SELECT o.id, o.order_date, o.total_amount, o.status, o.payment_method,
            COUNT(oi.id) AS item_count
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     WHERE o.user_id = ?
     GROUP BY o.id
     ORDER BY o.order_date DESC"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Saved measurements
$stmt = $conn->prepare(
    "SELECT id, label, chest, waist, hip, shoulder, sleeve_length, trouser_length,
            kameez_length, neck, captured_via, notes, updated_at
     FROM measurements WHERE user_id = ? ORDER BY updated_at DESC"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$measurements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// If editing a specific measurement, preload it
$editingMeasurement = null;
if (isset($_GET['edit']) && $tab === 'measurements') {
    $editId = (int)$_GET['edit'];
    foreach ($measurements as $m) {
        if ((int)$m['id'] === $editId) { $editingMeasurement = $m; break; }
    }
}

$statusMeta = [
    'pending'      => ['Pending',      '#8B6914'],
    'processing'   => ['Processing',   '#C9A96E'],
    'in_tailoring' => ['In Tailoring', '#5B4A34'],
    'shipped'      => ['Shipped',      '#3B6B8B'],
    'completed'    => ['Completed',    '#2D5A4A'],
    'cancelled'    => ['Cancelled',    '#A4343A'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="page-banner">
        <div class="container">
            <h1>My Account</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>My Account</span>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-layout">
                <!-- Sidebar -->
                <aside class="dashboard-sidebar">
                    <div class="profile-card">
                        <div class="profile-avatar"><i class="fas fa-user"></i></div>
                        <h3><?php echo e($user['name']); ?></h3>
                        <p><?php echo e($user['email']); ?></p>
                        <small>Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></small>
                    </div>
                    <nav class="dashboard-nav">
                        <a href="?tab=profile" class="<?php echo $tab==='profile'?'active':''; ?>"><i class="fas fa-user-edit"></i> Profile</a>
                        <a href="?tab=orders" class="<?php echo $tab==='orders'?'active':''; ?>"><i class="fas fa-box"></i> Order History <span class="nav-count"><?php echo count($orders); ?></span></a>
                        <a href="?tab=measurements" class="<?php echo $tab==='measurements'?'active':''; ?>"><i class="fas fa-ruler"></i> My Measurements <span class="nav-count"><?php echo count($measurements); ?></span></a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </nav>
                </aside>

                <!-- Main content -->
                <main class="dashboard-main">
                    <?php if ($flash): ?><div class="dashboard-flash success"><?php echo e($flash); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="dashboard-flash error"><?php echo e($error); ?></div><?php endif; ?>

                    <!-- PROFILE TAB -->
                    <?php if ($tab === 'profile'): ?>
                    <div class="dashboard-panel">
                        <h2>Profile Information</h2>
                        <form method="post" class="dashboard-form">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" value="<?php echo e($user['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" value="<?php echo e($user['email']); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="tel" name="phone" value="<?php echo e($user['phone']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Shipping Address</label>
                                    <input type="text" name="address" value="<?php echo e($user['address']); ?>" placeholder="Street, City">
                                </div>
                            </div>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                        </form>

                        <hr style="margin:30px 0; border:none; border-top:1px solid #eee;">

                        <h2>Change Password</h2>
                        <form method="post" class="dashboard-form">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Current Password</label>
                                    <input type="password" name="current_password" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" minlength="8" required>
                                </div>
                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirm_password" minlength="8" required>
                                </div>
                            </div>
                            <button type="submit" class="btn-primary"><i class="fas fa-key"></i> Change Password</button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- ORDERS TAB -->
                    <?php if ($tab === 'orders'): ?>
                    <div class="dashboard-panel">
                        <h2>Order History</h2>
                        <?php if (empty($orders)): ?>
                            <div class="dashboard-empty">
                                <i class="fas fa-box-open"></i>
                                <p>You haven't placed any orders yet.</p>
                                <a href="order.php" class="btn-primary">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $o):
                                $meta = $statusMeta[$o['status']] ?? [ucfirst($o['status']), '#7A7267'];
                                // Fetch items
                                $iStmt = $conn->prepare(
                                    "SELECT oi.product_id, oi.quantity, oi.price, oi.total, p.name AS product_name
                                     FROM order_items oi
                                     LEFT JOIN products p ON p.id = oi.product_id
                                     WHERE oi.order_id = ?"
                                );
                                $iStmt->bind_param("i", $o['id']);
                                $iStmt->execute();
                                $items = $iStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            ?>
                            <div class="order-card">
                                <div class="order-card-header">
                                    <div>
                                        <strong>Order #<?php echo (int)$o['id']; ?></strong>
                                        <span class="order-date"><?php echo date('d M Y, h:i A', strtotime($o['order_date'])); ?></span>
                                    </div>
                                    <span class="order-status-chip" style="background:<?php echo $meta[1]; ?>; color:#fff;"><?php echo $meta[0]; ?></span>
                                </div>
                                <div class="order-card-body">
                                    <div class="order-timeline">
                                        <?php
                                        $flow = ['pending','processing','in_tailoring','shipped','completed'];
                                        $currentIdx = array_search($o['status'], $flow, true);
                                        if ($o['status'] === 'cancelled') $currentIdx = -1;
                                        foreach ($flow as $idx => $step):
                                            $active = ($currentIdx !== false && $idx <= $currentIdx);
                                        ?>
                                            <div class="timeline-step <?php echo $active ? 'active' : ''; ?>">
                                                <div class="dot"></div>
                                                <span><?php echo ucwords(str_replace('_',' ', $step)); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <table class="order-items-mini">
                                        <?php foreach ($items as $it): ?>
                                        <tr>
                                            <td><?php echo e($it['product_name'] ?? $it['product_id']); ?></td>
                                            <td>× <?php echo (int)$it['quantity']; ?></td>
                                            <td style="text-align:right;">PKR <?php echo number_format((float)$it['total'], 0); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                    <div class="order-card-footer">
                                        <span><i class="fas fa-credit-card"></i> <?php echo ucwords(str_replace('_',' ', $o['payment_method'])); ?></span>
                                        <strong>Total: PKR <?php echo number_format((float)$o['total_amount'], 0); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- MEASUREMENTS TAB -->
                    <?php if ($tab === 'measurements'): ?>
                    <div class="dashboard-panel">
                        <h2>My Measurements</h2>
                        <p class="panel-intro">Save your measurements once and reuse them on every order. All values in inches.</p>

                        <?php if ($editingMeasurement || isset($_GET['new'])): ?>
                            <?php $m = $editingMeasurement ?? []; ?>
                            <form method="post" class="dashboard-form measurement-form">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="save_measurement">
                                <input type="hidden" name="measurement_id" value="<?php echo (int)($m['id'] ?? 0); ?>">
                                <div class="form-row">
                                    <div class="form-group" style="flex:1;">
                                        <label>Label</label>
                                        <input type="text" name="label" value="<?php echo e($m['label'] ?? 'Default'); ?>" placeholder="e.g. Default, Formal kurta" required>
                                    </div>
                                </div>
                                <div class="measurement-grid">
                                    <?php
                                    $mFields = [
                                        'chest' => 'Chest',
                                        'waist' => 'Waist',
                                        'hip' => 'Hip',
                                        'shoulder' => 'Shoulder',
                                        'sleeve_length' => 'Sleeve Length',
                                        'trouser_length' => 'Trouser Length',
                                        'kameez_length' => 'Kameez Length',
                                        'neck' => 'Neck',
                                    ];
                                    foreach ($mFields as $field => $label): ?>
                                        <div class="form-group">
                                            <label><?php echo $label; ?></label>
                                            <input type="text" name="<?php echo $field; ?>" value="<?php echo e($m[$field] ?? ''); ?>" placeholder="e.g. 38">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" rows="2" placeholder="Any special notes..."><?php echo e($m['notes'] ?? ''); ?></textarea>
                                </div>
                                <div style="display:flex; gap:10px;">
                                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save</button>
                                    <a href="?tab=measurements" class="btn-secondary">Cancel</a>
                                </div>
                            </form>
                        <?php else: ?>
                            <div style="text-align:right; margin-bottom:15px;">
                                <a href="?tab=measurements&amp;new=1" class="btn-primary"><i class="fas fa-plus"></i> Add New Set</a>
                            </div>

                            <?php if (empty($measurements)): ?>
                                <div class="dashboard-empty">
                                    <i class="fas fa-ruler"></i>
                                    <p>No saved measurements yet. Add a set to reuse on every order.</p>
                                </div>
                            <?php else: ?>
                                <div class="measurement-cards">
                                    <?php foreach ($measurements as $m): ?>
                                    <div class="measurement-card">
                                        <div class="measurement-card-header">
                                            <h4><i class="fas fa-ruler"></i> <?php echo e($m['label']); ?></h4>
                                            <span class="capture-badge capture-<?php echo e($m['captured_via']); ?>">
                                                <?php echo $m['captured_via'] === 'ai' ? 'AI' : 'Manual'; ?>
                                            </span>
                                        </div>
                                        <table class="measurement-table">
                                            <?php foreach (['chest','waist','hip','shoulder','sleeve_length','trouser_length','kameez_length','neck'] as $f):
                                                if (!empty($m[$f])): ?>
                                                <tr>
                                                    <td><?php echo ucwords(str_replace('_',' ', $f)); ?></td>
                                                    <td><?php echo e($m[$f]); ?>″</td>
                                                </tr>
                                            <?php endif; endforeach; ?>
                                        </table>
                                        <?php if (!empty($m['notes'])): ?>
                                            <p class="measurement-notes"><em><?php echo e($m['notes']); ?></em></p>
                                        <?php endif; ?>
                                        <div class="measurement-actions">
                                            <a href="?tab=measurements&amp;edit=<?php echo (int)$m['id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                                            <form method="post" onsubmit="return confirm('Delete this measurement set?');" style="display:inline;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="action" value="delete_measurement">
                                                <input type="hidden" name="measurement_id" value="<?php echo (int)$m['id']; ?>">
                                                <button type="submit" class="link-danger"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        </div>
                                        <small class="measurement-updated">Updated <?php echo date('d M Y', strtotime($m['updated_at'])); ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
