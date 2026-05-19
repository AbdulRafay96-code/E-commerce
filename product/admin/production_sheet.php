<?php
/**
 * Production Sheet (SDD §3.5, §4.1.7, §8.3 sample format)
 *
 * Renders a printable, tailor-ready summary of an order. Admins can:
 *  - View the sheet inline
 *  - Print to PDF via browser (Ctrl/Cmd+P)
 *  - "Generate" stores a row in production_sheets with admin attribution
 *
 * Access: super_admin OR order_manager
 */
session_start();
require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/admin_auth.php';
requireAdminRole(['order_manager']); // super always allowed

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    http_response_code(400);
    exit('Missing order_id');
}

// Handle "generate" — record in production_sheets table
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfVerify() && ($_POST['action'] ?? '') === 'generate') {
    $tailorNotes = trim($_POST['tailor_notes'] ?? '');
    $adminId = (int)$_SESSION['admin_id'];

    $stmt = $conn->prepare(
        "INSERT INTO production_sheets (order_id, generated_by, tailor_notes)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE generated_by = VALUES(generated_by),
                                 tailor_notes = VALUES(tailor_notes),
                                 generated_at = CURRENT_TIMESTAMP"
    );
    $stmt->bind_param('iis', $orderId, $adminId, $tailorNotes);
    $stmt->execute();
    auditLog('production_sheet.generate', 'order', $orderId, ['notes_length' => strlen($tailorNotes)]);
    $flash = "Production sheet recorded — ready to print.";
}

// Load full order context
$order = $conn->query(
    "SELECT o.*, u.email AS user_email
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     WHERE o.id = " . $orderId
)->fetch_assoc();

if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

// Order items + customization
$items = $conn->query(
    "SELECT oi.id, oi.quantity, oi.price, oi.total, p.name AS product_name, p.category, p.image
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = " . $orderId
);

$customization = $conn->query(
    "SELECT * FROM customization WHERE order_id = " . $orderId
)->fetch_assoc();

$measurements = $conn->query(
    "SELECT om.* FROM order_measurements om
     JOIN order_items oi ON oi.id = om.order_item_id
     WHERE oi.order_id = " . $orderId . "
     LIMIT 1"
)->fetch_assoc();

// Existing sheet record (if regenerating)
$sheet = $conn->query(
    "SELECT ps.*, a.username AS generated_by_name
     FROM production_sheets ps
     LEFT JOIN admins a ON a.id = ps.generated_by
     WHERE ps.order_id = " . $orderId
)->fetch_assoc();

$existingNotes = $sheet['tailor_notes'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Production Sheet — Order #<?php echo $orderId; ?></title>
    <style>
        :root {
            --gold: #B8932F;
            --ink: #1A1A1A;
            --paper: #FFFEF8;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f5f5f0;
            color: var(--ink);
            margin: 0;
            padding: 30px 20px;
        }
        .sheet {
            max-width: 820px;
            margin: 0 auto;
            background: var(--paper);
            padding: 40px 50px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #ddd;
        }
        .sheet-header {
            text-align: center;
            border-bottom: 3px double var(--gold);
            padding-bottom: 18px;
            margin-bottom: 30px;
        }
        .sheet-header .brand {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: 1px;
        }
        .sheet-header .gold { color: var(--gold); }
        .sheet-header .subtitle {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #666;
            margin-top: 6px;
        }
        .sheet-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 24px;
            font-size: 13px;
            margin-bottom: 28px;
            background: #FAF6E8;
            padding: 16px 20px;
            border-left: 4px solid var(--gold);
        }
        .sheet-meta b { color: var(--gold); }
        h2 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--gold);
            border-bottom: 1px solid #e0d8b8;
            padding-bottom: 6px;
            margin: 28px 0 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 8px;
        }
        td, th {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f5efd9;
            color: var(--ink);
            font-weight: 600;
        }
        .measure-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px 24px;
            font-size: 14px;
            padding: 8px 0;
        }
        .measure-grid div {
            border-bottom: 1px dotted #ccc;
            padding: 4px 0;
            display: flex;
            justify-content: space-between;
        }
        .measure-grid b { color: var(--gold); }
        .notes-area {
            background: #FFFBEC;
            border: 1px dashed #c8b96a;
            padding: 14px 18px;
            min-height: 70px;
            font-size: 13px;
            white-space: pre-wrap;
        }
        .signature-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 50px;
            font-size: 12px;
        }
        .sig-box {
            border-top: 1px solid #888;
            padding-top: 4px;
            text-align: center;
            color: #666;
        }
        .footer-note {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .actions {
            max-width: 820px;
            margin: 0 auto 16px;
            text-align: right;
        }
        .actions a, .actions button {
            display: inline-block;
            padding: 10px 18px;
            background: var(--ink);
            color: var(--gold);
            border: 1px solid var(--gold);
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            margin-left: 6px;
        }
        .actions .primary {
            background: var(--gold);
            color: white;
        }
        .flash {
            max-width: 820px;
            margin: 0 auto 16px;
            background: #E8F5E9;
            border-left: 4px solid #2D5A4A;
            padding: 10px 18px;
            font-size: 13px;
        }
        .gen-form {
            max-width: 820px;
            margin: 0 auto 18px;
            background: white;
            padding: 16px 20px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .gen-form textarea {
            width: 100%;
            min-height: 70px;
            padding: 10px;
            font-family: inherit;
            font-size: 13px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        /* Print rules — hide chrome, force the sheet onto its own page */
        @media print {
            body { background: white; padding: 0; }
            .actions, .gen-form, .flash, .no-print { display: none !important; }
            .sheet { box-shadow: none; border: none; padding: 30px 40px; }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>
<body>

<?php if ($flash): ?>
    <div class="flash no-print"><?php echo htmlspecialchars($flash); ?></div>
<?php endif; ?>

<div class="actions no-print">
    <a href="admin_orders.php"><i class="fas fa-arrow-left"></i> Back to Orders</a>
    <button class="primary" onclick="window.print()">Print / Save as PDF</button>
</div>

<form class="gen-form no-print" method="post">
    <?php echo csrfField(); ?>
    <input type="hidden" name="action" value="generate">
    <label for="tailor_notes" style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">
        Tailor Notes (optional — appears on the printed sheet):
    </label>
    <textarea name="tailor_notes" id="tailor_notes" placeholder="e.g. Reinforce shoulder seams. Use 1cm seam allowance throughout..."><?php echo htmlspecialchars($existingNotes); ?></textarea>
    <button type="submit" class="primary">
        <?php echo $sheet ? 'Update Sheet' : 'Generate Sheet'; ?>
    </button>
    <?php if ($sheet): ?>
        <span style="font-size:12px; color:#666; margin-left:14px;">
            Last generated by <b><?php echo htmlspecialchars($sheet['generated_by_name'] ?? 'unknown'); ?></b>
            on <?php echo htmlspecialchars($sheet['generated_at']); ?>
        </span>
    <?php endif; ?>
</form>

<div class="sheet">
    <div class="sheet-header">
        <div class="brand">Stitch <span class="gold">House</span></div>
        <div class="subtitle">Production Sheet · Tailor Reference</div>
    </div>

    <div class="sheet-meta">
        <div><b>Order ID:</b> #<?php echo $orderId; ?></div>
        <div><b>Order Date:</b> <?php echo date('d M Y', strtotime($order['order_date'])); ?></div>
        <div><b>Customer:</b> <?php echo htmlspecialchars($order['name']); ?></div>
        <div><b>Phone:</b> <?php echo htmlspecialchars($order['phone']); ?></div>
        <div><b>Email:</b> <?php echo htmlspecialchars($order['user_email'] ?? $order['email']); ?></div>
        <div><b>Status:</b> <?php echo ucwords(str_replace('_', ' ', $order['status'])); ?></div>
        <div style="grid-column:1 / -1;"><b>Shipping Address:</b> <?php echo htmlspecialchars($order['address'] . ', ' . $order['city']); ?></div>
    </div>

    <h2>Garment(s)</h2>
    <table>
        <thead>
            <tr>
                <th style="width:50%">Item / Fabric</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($it = $items->fetch_assoc()): ?>
                <tr>
                    <td><b><?php echo htmlspecialchars($it['product_name']); ?></b></td>
                    <td><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $it['category']))); ?></td>
                    <td><?php echo (int)$it['quantity']; ?></td>
                    <td>PKR <?php echo number_format((float)$it['price']); ?></td>
                    <td>PKR <?php echo number_format((float)$it['total']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if ($customization): ?>
        <h2>Customization</h2>
        <div class="measure-grid">
            <div><span>Collar Style</span><b><?php echo htmlspecialchars($customization['collar_style'] ?? '—'); ?></b></div>
            <div><span>Cuff Style</span><b><?php echo htmlspecialchars($customization['cuff_style'] ?? '—'); ?></b></div>
            <div><span>Kurta Style</span><b><?php echo htmlspecialchars($customization['kurta_style'] ?? '—'); ?></b></div>
            <div><span>Daman</span><b><?php echo htmlspecialchars($customization['daman_style'] ?? '—'); ?></b></div>
            <div><span>Placket</span><b><?php echo htmlspecialchars($customization['placket_style'] ?? '—'); ?></b></div>
            <div><span>Bottom</span><b><?php echo htmlspecialchars($customization['bottom_style'] ?? '—'); ?></b></div>
            <div><span>Front Pockets</span><b><?php echo (int)($customization['front_pockets'] ?? 0); ?></b></div>
            <div><span>Side Pockets</span><b><?php echo (int)($customization['side_pockets'] ?? 0); ?></b></div>
            <div><span>Fit Preference</span><b><?php echo htmlspecialchars($customization['fit_preference'] ?? 'Regular'); ?></b></div>
        </div>
    <?php endif; ?>

    <?php if ($measurements): ?>
        <h2>Body Measurements (inches)</h2>
        <div class="measure-grid">
            <div><span>Shoulder</span><b><?php echo htmlspecialchars($measurements['shoulder'] ?? '—'); ?></b></div>
            <div><span>Chest</span><b><?php echo htmlspecialchars($measurements['chest'] ?? '—'); ?></b></div>
            <div><span>Waist</span><b><?php echo htmlspecialchars($measurements['waist'] ?? '—'); ?></b></div>
            <div><span>Hip</span><b><?php echo htmlspecialchars($measurements['hip'] ?? '—'); ?></b></div>
            <div><span>Sleeve Length</span><b><?php echo htmlspecialchars($measurements['sleeve_length'] ?? '—'); ?></b></div>
            <div><span>Kameez Length</span><b><?php echo htmlspecialchars($measurements['kameez_length'] ?? '—'); ?></b></div>
            <div><span>Trouser Length</span><b><?php echo htmlspecialchars($measurements['trouser_length'] ?? '—'); ?></b></div>
            <div><span>Neck</span><b><?php echo htmlspecialchars($measurements['neck'] ?? '—'); ?></b></div>
        </div>
        <?php if (!empty($measurements['notes'])): ?>
            <div style="margin-top:12px; font-size:12px; color:#555;">
                <b>Customer Notes:</b> <?php echo htmlspecialchars($measurements['notes']); ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <h2>Body Measurements</h2>
        <div style="font-size:13px; color:#999; padding:8px 0;">No measurements recorded for this order.</div>
    <?php endif; ?>

    <h2>Tailor Notes</h2>
    <div class="notes-area"><?php echo $existingNotes ? htmlspecialchars($existingNotes) : 'No additional instructions.'; ?></div>

    <div class="signature-row">
        <div class="sig-box">Tailor Signature</div>
        <div class="sig-box">Date Completed</div>
    </div>

    <div class="footer-note">
        Stitch House · Generated <?php echo date('d M Y H:i'); ?> · Order #<?php echo $orderId; ?>
        <?php if ($sheet): ?>
            · Sheet generated by <?php echo htmlspecialchars($sheet['generated_by_name'] ?? 'admin'); ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
