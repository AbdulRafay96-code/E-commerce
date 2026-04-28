<?php
/**
 * Admin Measurement Review (SDD §3.4 — human-in-the-loop oversight)
 *
 * Lets order_manager / super review captured measurements, see confidence,
 * and edit values when AI confidence was low. Edits are immutable: instead of
 * overwriting the original record we INSERT a new measurement row referencing
 * the original via notes, and create an audit_logs entry capturing before/after.
 */
session_start();
require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/admin_auth.php';
requireAdminRole(['order_manager']); // super always allowed

$flash = '';

// Handle edit submission — creates a NEW immutable record (SDD §3.4)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfVerify() && ($_POST['action'] ?? '') === 'edit_measurement') {
    $origId = (int)($_POST['original_id'] ?? 0);
    if ($origId > 0) {
        $orig = $conn->query("SELECT * FROM measurements WHERE id = $origId")->fetch_assoc();
        if ($orig) {
            $editedReason = trim($_POST['edit_reason'] ?? '') ?: 'Admin correction';
            $newLabel = ($orig['label'] ?? 'Measurement') . ' (admin-edited from #' . $origId . ')';
            $newValues = [];
            foreach (['chest','waist','hip','shoulder','sleeve_length','trouser_length','kameez_length','neck'] as $f) {
                $v = trim($_POST[$f] ?? '');
                $newValues[$f] = $v !== '' ? $v : null;
            }
            $combinedNotes = trim(($orig['notes'] ?? '') . "\n\n[Edited by " . ($_SESSION['admin_username'] ?? 'admin') . "] " . $editedReason);

            $stmt = $conn->prepare(
                "INSERT INTO measurements
                 (user_id, label, chest, waist, hip, shoulder, sleeve_length, trouser_length, kameez_length, neck, notes, captured_via)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'admin_edit')"
            );
            $uid = (int)$orig['user_id'];
            // 11 placeholders → 11 vars
            $stmt->bind_param(
                'issssssssss',
                $uid, $newLabel,
                $newValues['chest'], $newValues['waist'], $newValues['hip'], $newValues['shoulder'],
                $newValues['sleeve_length'], $newValues['trouser_length'], $newValues['kameez_length'], $newValues['neck'],
                $combinedNotes
            );
            $stmt->execute();
            $newId = $conn->insert_id;
            $stmt->close();

            // SDD §4.1.9 — audit with before/after diff
            auditLog('measurement.edit', 'measurement', $newId, [
                'original_id' => $origId,
                'reason' => $editedReason,
                'before' => array_intersect_key($orig, array_flip(['shoulder','chest','waist','hip','sleeve_length','kameez_length','trouser_length','neck'])),
                'after' => $newValues,
            ]);

            header("Location: admin_measurements.php?flash=" . urlencode("Edit recorded — new measurement #$newId created (original #$origId preserved)"));
            exit;
        }
    }
    header("Location: admin_measurements.php?flash=" . urlencode("Failed to find original measurement"));
    exit;
}

if (isset($_GET['flash'])) $flash = $_GET['flash'];

// Filter: 'low_confidence', 'all', 'recent'
$filter = $_GET['filter'] ?? 'low_confidence';
if (!in_array($filter, ['low_confidence', 'all', 'recent', 'edited'], true)) $filter = 'low_confidence';

$where = '1=1';
if ($filter === 'low_confidence') {
    $where = "m.confidence_score IS NOT NULL AND m.confidence_score < 0.75";
} elseif ($filter === 'edited') {
    $where = "m.captured_via = 'admin_edit'";
} elseif ($filter === 'recent') {
    $where = "m.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

$sql = "SELECT m.*, u.name AS user_name, u.email AS user_email
        FROM measurements m
        LEFT JOIN users u ON u.id = m.user_id
        WHERE $where
        ORDER BY m.id DESC
        LIMIT 100";
$rows = [];
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) $rows[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Measurement Review — Admin</title>
    <link rel="stylesheet" href="../variables.css">
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../admin-tickets-orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .filter-tabs { display: flex; gap: 0; margin: 16px 0; border-bottom: 2px solid #e0e0e0; }
        .filter-tabs a { padding: 10px 18px; color: #666; text-decoration: none; border-bottom: 3px solid transparent; font-weight: 600; font-size: 13px; }
        .filter-tabs a.active { color: var(--gold-color); border-bottom-color: var(--gold-color); }
        .meas-row {
            background: white; border: 1px solid #e0e0e0; border-radius: 8px;
            padding: 14px 18px; margin-bottom: 12px; display: grid;
            grid-template-columns: auto 1fr auto auto; gap: 14px; align-items: center;
        }
        .meas-row .conf-pill {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 11px; font-weight: 600;
        }
        .conf-high { background: #d4edda; color: #155724; }
        .conf-mid  { background: #fff3cd; color: #856404; }
        .conf-low  { background: #f8d7da; color: #721c24; }
        .conf-na   { background: #e9ecef; color: #495057; }
        .edit-form { background: #FAF6E8; padding: 18px; margin-top: 8px; border-radius: 6px; display: none; }
        .edit-form.open { display: block; }
        .edit-form input, .edit-form textarea {
            padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;
        }
        .edit-form .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 12px; }
        .edit-form label { font-size: 12px; color: #555; display: block; }
        .meta { color: #888; font-size: 12px; }
        .source-pill {
            display: inline-block; padding: 2px 8px; border-radius: 10px;
            font-size: 10px; font-weight: 600; margin-right: 6px;
        }
        .source-ai { background: #e3f2fd; color: #0d47a1; }
        .source-manual { background: #f3e5f5; color: #4a148c; }
        .source-admin_edit { background: #fff3e0; color: #e65100; }
    </style>
</head>
<body style="background:#f5f5f0;">

<div style="max-width:1200px; margin:0 auto; padding:30px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
        <h1 style="margin:0;">Measurement Review</h1>
        <a href="admin_dashboard.php" style="color:#666; text-decoration:none; font-size:13px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <p style="color:#777; font-size:13px;">Review AI-captured measurements. Edit values when confidence is low (SDD §3.4 human-in-the-loop). Edits are immutable — original records preserved.</p>

    <?php if ($flash): ?>
        <div style="background:#E8F5E9; border-left:4px solid #2D5A4A; padding:10px 18px; font-size:13px; margin-bottom:16px;">
            <?php echo htmlspecialchars($flash); ?>
        </div>
    <?php endif; ?>

    <div class="filter-tabs">
        <a class="<?php echo $filter === 'low_confidence' ? 'active' : ''; ?>" href="?filter=low_confidence">
            <i class="fas fa-exclamation-triangle"></i> Needs Review (Confidence &lt; 75%)
        </a>
        <a class="<?php echo $filter === 'recent' ? 'active' : ''; ?>" href="?filter=recent">
            <i class="fas fa-clock"></i> Last 7 Days
        </a>
        <a class="<?php echo $filter === 'edited' ? 'active' : ''; ?>" href="?filter=edited">
            <i class="fas fa-pencil"></i> Admin Edits
        </a>
        <a class="<?php echo $filter === 'all' ? 'active' : ''; ?>" href="?filter=all">All</a>
    </div>

    <?php if (empty($rows)): ?>
        <div style="padding:40px; text-align:center; color:#999; background:white; border-radius:8px;">
            No measurements match this filter.
        </div>
    <?php else: foreach ($rows as $r):
        $conf = $r['confidence_score'];
        if ($conf === null) { $confClass = 'conf-na'; $confLabel = 'N/A'; }
        elseif ($conf >= 0.8) { $confClass = 'conf-high'; $confLabel = number_format($conf*100, 0) . '%'; }
        elseif ($conf >= 0.6) { $confClass = 'conf-mid'; $confLabel = number_format($conf*100, 0) . '%'; }
        else { $confClass = 'conf-low'; $confLabel = number_format($conf*100, 0) . '%'; }
    ?>
        <div class="meas-row">
            <div>
                <span class="source-pill source-<?php echo htmlspecialchars($r['captured_via'] ?? 'manual'); ?>">
                    <?php echo strtoupper($r['captured_via'] ?? 'manual'); ?>
                </span>
                <span class="conf-pill <?php echo $confClass; ?>">conf: <?php echo $confLabel; ?></span>
            </div>
            <div>
                <strong>#<?php echo (int)$r['id']; ?> · <?php echo htmlspecialchars($r['label'] ?? 'Measurement'); ?></strong>
                <div class="meta">
                    <?php echo htmlspecialchars($r['user_name'] ?? '—'); ?>
                    (<?php echo htmlspecialchars($r['user_email'] ?? ''); ?>)
                    · <?php echo date('d M Y H:i', strtotime($r['created_at'])); ?>
                </div>
                <div class="meta" style="margin-top:4px;">
                    Sh: <?php echo $r['shoulder'] ?: '—'; ?>"
                    · Ch: <?php echo $r['chest'] ?: '—'; ?>"
                    · Wa: <?php echo $r['waist'] ?: '—'; ?>"
                    · Hp: <?php echo $r['hip'] ?: '—'; ?>"
                    · Sl: <?php echo $r['sleeve_length'] ?: '—'; ?>"
                    · Km: <?php echo $r['kameez_length'] ?: '—'; ?>"
                    · Tr: <?php echo $r['trouser_length'] ?: '—'; ?>"
                    · Nk: <?php echo $r['neck'] ?: '—'; ?>"
                </div>
            </div>
            <div>
                <button onclick="document.getElementById('edit-<?php echo (int)$r['id']; ?>').classList.toggle('open')"
                        style="background:#1A1A1A; color:var(--gold-color); border:1px solid var(--gold-color); padding:6px 14px; border-radius:6px; cursor:pointer; font-size:12px;">
                    <i class="fas fa-pencil"></i> Edit
                </button>
            </div>
        </div>

        <form method="post" id="edit-<?php echo (int)$r['id']; ?>" class="edit-form">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="edit_measurement">
            <input type="hidden" name="original_id" value="<?php echo (int)$r['id']; ?>">
            <div class="grid">
                <div><label>Shoulder</label><input type="text" name="shoulder" value="<?php echo htmlspecialchars($r['shoulder'] ?? ''); ?>" style="width:100%;"></div>
                <div><label>Chest</label><input type="text" name="chest" value="<?php echo htmlspecialchars($r['chest'] ?? ''); ?>" style="width:100%;"></div>
                <div><label>Waist</label><input type="text" name="waist" value="<?php echo htmlspecialchars($r['waist'] ?? ''); ?>" style="width:100%;"></div>
                <div><label>Hip</label><input type="text" name="hip" value="<?php echo htmlspecialchars($r['hip'] ?? ''); ?>" style="width:100%;"></div>
                <div><label>Sleeve</label><input type="text" name="sleeve_length" value="<?php echo htmlspecialchars($r['sleeve_length'] ?? ''); ?>" style="width:100%;"></div>
                <div><label>Kameez</label><input type="text" name="kameez_length" value="<?php echo htmlspecialchars($r['kameez_length'] ?? ''); ?>" style="width:100%;"></div>
                <div><label>Trouser</label><input type="text" name="trouser_length" value="<?php echo htmlspecialchars($r['trouser_length'] ?? ''); ?>" style="width:100%;"></div>
                <div><label>Neck</label><input type="text" name="neck" value="<?php echo htmlspecialchars($r['neck'] ?? ''); ?>" style="width:100%;"></div>
            </div>
            <label style="font-size:12px; color:#555; display:block; margin-bottom:4px;">Reason for edit (audit trail):</label>
            <input type="text" name="edit_reason" placeholder="e.g. Customer reported tight fit; AI under-measured chest" style="width:100%; margin-bottom:10px;" required>
            <button type="submit" style="background:var(--gold-color); color:white; border:none; padding:8px 18px; border-radius:6px; cursor:pointer; font-size:13px;">
                <i class="fas fa-save"></i> Save Edited Version
            </button>
        </form>
    <?php endforeach; endif; ?>
</div>

</body>
</html>
