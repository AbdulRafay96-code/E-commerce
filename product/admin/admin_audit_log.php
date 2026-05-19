<?php
/**
 * Admin Audit Log Viewer (SDD §4.1.8 + §4.1.9)
 *
 * Surfaces both:
 *  - audit_logs (admin actions across orders/products/tickets)
 *  - order_status_logs (per-order status timeline)
 *
 * Access: super_admin only — full visibility into all admin activity.
 */
session_start();
require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/admin_auth.php';
requireAdminRole([]); // super only — empty allowed list means non-super blocked

$tab = $_GET['tab'] ?? 'audit';
if (!in_array($tab, ['audit', 'status'], true)) $tab = 'audit';

// Recent audit log entries
$auditRows = [];
if ($tab === 'audit') {
    $res = $conn->query(
        "SELECT al.*, a.username AS admin_name, a.role AS admin_role
         FROM audit_logs al
         LEFT JOIN admins a ON a.id = al.admin_id
         ORDER BY al.id DESC
         LIMIT 200"
    );
    while ($r = $res->fetch_assoc()) $auditRows[] = $r;
}

// Recent status logs
$statusRows = [];
if ($tab === 'status') {
    $res = $conn->query(
        "SELECT osl.*, a.username AS admin_name
         FROM order_status_logs osl
         LEFT JOIN admins a ON a.id = osl.changed_by
         ORDER BY osl.id DESC
         LIMIT 200"
    );
    while ($r = $res->fetch_assoc()) $statusRows[] = $r;
}

$pageTitle = 'Audit Log - Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../variables.css">
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../admin-tickets-orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .tabs { display: flex; gap: 0; margin: 20px 0 0; border-bottom: 2px solid #e0e0e0; }
        .tab {
            padding: 10px 20px;
            background: transparent;
            color: #666;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;
        }
        .tab.active {
            color: var(--gold-color);
            border-bottom-color: var(--gold-color);
        }
        .audit-table { width: 100%; border-collapse: collapse; margin-top: 18px; font-size: 13px; }
        .audit-table th, .audit-table td { padding: 9px 12px; border-bottom: 1px solid #eee; vertical-align: top; }
        .audit-table th { background: #f8f5e8; color: #333; text-align: left; }
        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            background: #f0e8c8;
            color: #6b5b15;
        }
        .pill.role-super { background: #f8d7da; color: #721c24; }
        .pill.role-order_manager { background: #d4edda; color: #155724; }
        .pill.role-support { background: #d1ecf1; color: #0c5460; }
        .details-cell { max-width: 380px; word-break: break-word; font-family: monospace; font-size: 11px; color: #555; }
        .empty { padding: 40px; text-align: center; color: #999; }
    </style>
</head>
<body style="background:#f5f5f0;">

<div style="max-width:1200px; margin:0 auto; padding:30px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
        <h1 style="margin:0;">Audit Log</h1>
        <a href="admin_dashboard.php" style="color:#666; text-decoration:none; font-size:13px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <p style="color:#777; font-size:13px;">Full history of admin actions and order status changes (SDD §4.1.8 / §4.1.9). Showing latest 200.</p>

    <div class="tabs">
        <a class="tab <?php echo $tab === 'audit' ? 'active' : ''; ?>" href="?tab=audit">
            <i class="fas fa-list"></i> Admin Actions (<?php echo $tab === 'audit' ? count($auditRows) : '—'; ?>)
        </a>
        <a class="tab <?php echo $tab === 'status' ? 'active' : ''; ?>" href="?tab=status">
            <i class="fas fa-history"></i> Order Status History (<?php echo $tab === 'status' ? count($statusRows) : '—'; ?>)
        </a>
    </div>

    <?php if ($tab === 'audit'): ?>
        <?php if (empty($auditRows)): ?>
            <div class="empty">No audit entries yet. Actions are logged automatically as admins modify orders, products, or tickets.</div>
        <?php else: ?>
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Admin</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditRows as $r): ?>
                        <tr>
                            <td><?php echo date('d M Y H:i', strtotime($r['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($r['admin_name'] ?? '—'); ?></td>
                            <td><span class="pill role-<?php echo htmlspecialchars($r['admin_role'] ?? ''); ?>">
                                <?php echo htmlspecialchars($r['admin_role'] ?? '—'); ?>
                            </span></td>
                            <td><b><?php echo htmlspecialchars($r['action']); ?></b></td>
                            <td>
                                <?php if ($r['entity_type']): ?>
                                    <?php echo htmlspecialchars($r['entity_type']); ?>
                                    <?php if ($r['entity_id']): ?> #<?php echo (int)$r['entity_id']; ?><?php endif; ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td class="details-cell"><?php echo htmlspecialchars($r['details'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($r['ip_address'] ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    <?php else: ?>
        <?php if (empty($statusRows)): ?>
            <div class="empty">No status changes recorded.</div>
        <?php else: ?>
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Order</th>
                        <th>New Status</th>
                        <th>Changed By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($statusRows as $r): ?>
                        <tr>
                            <td><?php echo date('d M Y H:i', strtotime($r['changed_at'])); ?></td>
                            <td><a href="admin_orders.php#order-<?php echo (int)$r['order_id']; ?>">#<?php echo (int)$r['order_id']; ?></a></td>
                            <td><b><?php echo ucwords(str_replace('_', ' ', $r['status'])); ?></b></td>
                            <td><?php echo htmlspecialchars($r['admin_name'] ?? '<system / customer>'); ?></td>
                            <td><?php echo htmlspecialchars($r['notes'] ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>

