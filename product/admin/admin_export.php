<?php
/**
 * CSV Reporting Exports (SDD §3.8 — admin reporting)
 *
 * Exports order, product, customer, and audit-log data as CSV for offline
 * analysis. Streams output directly so memory stays bounded for large datasets.
 *
 * Usage: admin_export.php?type=orders|products|customers|audit
 * Access: super only — exporting customer data is sensitive.
 */
session_start();
require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/admin_auth.php';
requireAdminRole([]); // super only

$type = $_GET['type'] ?? '';
$valid = ['orders', 'products', 'customers', 'audit'];
if (!in_array($type, $valid, true)) {
    http_response_code(400);
    exit("Invalid export type. Use one of: " . implode(', ', $valid));
}

// Audit-log this export action for compliance
auditLog('export.csv', $type, null, ['source' => $_SERVER['REMOTE_ADDR'] ?? null]);

$filename = "stitch_house_{$type}_" . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
// BOM for Excel UTF-8 compatibility
fwrite($out, "\xEF\xBB\xBF");

switch ($type) {
    case 'orders':
        fputcsv($out, ['Order ID','Date','Customer','Email','Phone','City','Status','Payment','Total (PKR)']);
        $sql = "SELECT o.id, o.order_date, COALESCE(u.name, o.name) AS customer, COALESCE(u.email, o.email) AS email,
                       o.phone, o.city, o.status, o.payment_method, o.total_amount
                FROM orders o LEFT JOIN users u ON u.id = o.user_id
                ORDER BY o.id DESC";
        $res = $conn->query($sql);
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [
                $r['id'], $r['order_date'], $r['customer'], $r['email'],
                $r['phone'], $r['city'], $r['status'], $r['payment_method'],
                number_format((float)$r['total_amount'], 2, '.', '')
            ]);
        }
        break;

    case 'products':
        fputcsv($out, ['ID','Name','Category','Price','Stock','Reorder Level','Status']);
        $sql = "SELECT id, name, category, price, stock_quantity, reorder_level FROM products ORDER BY category, id";
        $res = $conn->query($sql);
        while ($r = $res->fetch_assoc()) {
            $status = $r['stock_quantity'] <= 0 ? 'OUT_OF_STOCK'
                : ($r['stock_quantity'] <= $r['reorder_level'] ? 'LOW_STOCK' : 'OK');
            fputcsv($out, [
                $r['id'], $r['name'], $r['category'],
                number_format((float)$r['price'], 2, '.', ''),
                $r['stock_quantity'], $r['reorder_level'], $status
            ]);
        }
        break;

    case 'customers':
        fputcsv($out, ['ID','Name','Email','Phone','Joined','Total Orders','Total Spent (PKR)']);
        $sql = "SELECT u.id, u.name, u.email, u.phone, u.created_at,
                       (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count,
                       COALESCE((SELECT SUM(total_amount) FROM orders WHERE user_id = u.id AND status <> 'cancelled'), 0) AS total_spent
                FROM users u ORDER BY u.id";
        $res = $conn->query($sql);
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [
                $r['id'], $r['name'], $r['email'], $r['phone'], $r['created_at'],
                $r['order_count'], number_format((float)$r['total_spent'], 2, '.', '')
            ]);
        }
        break;

    case 'audit':
        fputcsv($out, ['ID','Date','Admin','Action','Entity','Entity ID','Details','IP']);
        $sql = "SELECT al.*, a.username AS admin_name FROM audit_logs al
                LEFT JOIN admins a ON a.id = al.admin_id
                ORDER BY al.id DESC LIMIT 5000";
        $res = $conn->query($sql);
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [
                $r['id'], $r['created_at'], $r['admin_name'] ?? '—',
                $r['action'], $r['entity_type'] ?? '', $r['entity_id'] ?? '',
                $r['details'] ?? '', $r['ip_address'] ?? ''
            ]);
        }
        break;
}

fclose($out);
exit;
