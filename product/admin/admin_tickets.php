<?php
/**
 * Admin Support Tickets — SRS §3.2.7
 */
require_once '../db_connection.php';
require_once '../includes/functions.php';
session_start();
require_once 'admin_auth.php';
requireAdminRole(['support']); // super + support

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'] ?? '';

$flash = '';

// Handle ticket update (status + admin response)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!csrfVerify()) {
        $flash = 'Invalid request token. Please refresh and try again.';
    } else {
    $ticketId = (int)($_POST['id'] ?? 0);

    if ($_POST['action'] === 'respond' && $ticketId > 0) {
        $response = trim($_POST['admin_response'] ?? '');
        $status   = $_POST['status'] ?? 'in_progress';
        $allowed  = ['open','in_progress','resolved','closed'];
        if (!in_array($status, $allowed, true)) $status = 'in_progress';

        $resolvedAt = ($status === 'resolved' || $status === 'closed') ? date('Y-m-d H:i:s') : null;

        $stmt = $conn->prepare(
            "UPDATE contact_messages
             SET admin_response = ?, status = ?, resolved_at = ?
             WHERE id = ?"
        );
        $stmt->bind_param("sssi", $response, $status, $resolvedAt, $ticketId);
        $stmt->execute();
        $flash = "Ticket #$ticketId updated.";
    }
    }
}

// Filter
$filter = $_GET['status'] ?? 'all';
$allowedFilters = ['all','open','in_progress','resolved','closed'];
if (!in_array($filter, $allowedFilters, true)) $filter = 'all';

$query = "SELECT id, ticket_number, user_id, name, email, phone, subject, message,
                 status, admin_response, created_at, resolved_at
          FROM contact_messages";
if ($filter !== 'all') {
    $query .= " WHERE status = '" . $conn->real_escape_string($filter) . "'";
}
$query .= " ORDER BY FIELD(status,'open','in_progress','resolved','closed'), created_at DESC";
$result = $conn->query($query);

// Count per status for sidebar pills
$counts = ['open'=>0,'in_progress'=>0,'resolved'=>0,'closed'=>0];
$countRes = $conn->query("SELECT status, COUNT(*) c FROM contact_messages GROUP BY status");
if ($countRes) {
    while ($r = $countRes->fetch_assoc()) {
        $counts[$r['status']] = (int)$r['c'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - Stitch House Admin</title>
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
                <?php if (adminHasRole(['order_manager'])): ?>
                <div class="admin-menu-item"><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i><span>Orders</span></a></div>
                <?php endif; ?>
                <div class="admin-menu-item active"><a href="admin_tickets.php"><i class="fas fa-life-ring"></i><span>Support Tickets</span></a></div>
                <div class="admin-menu-item"><a href="../admin_logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
            </div>
        </div>

        <div class="admin-content">
            <div class="admin-header">
                <div class="admin-title"><h1>Support Tickets</h1></div>
                <div class="admin-user-info">
                    <div class="admin-user-name"><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_username); ?></div>
                </div>
            </div>

            <?php if ($flash): ?><div class="flash"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>

            <div class="tickets-toolbar">
                <?php
                $total = array_sum($counts);
                $pills = [
                    'all' => ['All', $total],
                    'open' => ['Open', $counts['open']],
                    'in_progress' => ['In Progress', $counts['in_progress']],
                    'resolved' => ['Resolved', $counts['resolved']],
                    'closed' => ['Closed', $counts['closed']],
                ];
                foreach ($pills as $key => [$label, $n]):
                ?>
                    <a href="?status=<?php echo $key; ?>" class="<?php echo $filter === $key ? 'active' : ''; ?>"><?php echo $label; ?><span class="cnt">(<?php echo $n; ?>)</span></a>
                <?php endforeach; ?>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($t = $result->fetch_assoc()): ?>
                    <div class="ticket-row status-<?php echo htmlspecialchars($t['status']); ?>">
                        <div class="ticket-header">
                            <div>
                                <span class="ticket-number"><?php echo htmlspecialchars($t['ticket_number'] ?: ('TKT-' . str_pad($t['id'],6,'0',STR_PAD_LEFT))); ?></span>
                                <span class="ticket-status"><?php echo str_replace('_',' ', $t['status']); ?></span>
                            </div>
                            <button type="button" class="toggle-respond" onclick="this.closest('.ticket-row').classList.toggle('expanded')">
                                <i class="fas fa-reply"></i> Respond
                            </button>
                        </div>
                        <div class="ticket-subject"><?php echo htmlspecialchars($t['subject']); ?></div>
                        <div class="ticket-meta">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($t['name']); ?>
                            · <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($t['email']); ?>
                            <?php if ($t['phone']): ?> · <i class="fas fa-phone"></i> <?php echo htmlspecialchars($t['phone']); ?><?php endif; ?>
                            · <i class="fas fa-clock"></i> <?php echo htmlspecialchars($t['created_at']); ?>
                        </div>
                        <div class="ticket-message"><?php echo nl2br(htmlspecialchars($t['message'])); ?></div>

                        <?php if (!empty($t['admin_response'])): ?>
                        <div class="ticket-response-existing">
                            <strong><i class="fas fa-check"></i> Response:</strong><br>
                            <?php echo nl2br(htmlspecialchars($t['admin_response'])); ?>
                            <?php if ($t['resolved_at']): ?>
                                <div style="font-size:11px; margin-top:4px; opacity:0.7;">Resolved: <?php echo htmlspecialchars($t['resolved_at']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <form class="ticket-response-form" method="post">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="respond">
                            <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                            <textarea name="admin_response" placeholder="Write response to customer..."><?php echo htmlspecialchars($t['admin_response'] ?? ''); ?></textarea>
                            <div style="margin-top:8px;">
                                <select name="status">
                                    <option value="open"         <?php echo $t['status']==='open'?'selected':''; ?>>Open</option>
                                    <option value="in_progress"  <?php echo $t['status']==='in_progress'?'selected':''; ?>>In Progress</option>
                                    <option value="resolved"     <?php echo $t['status']==='resolved'?'selected':''; ?>>Resolved</option>
                                    <option value="closed"       <?php echo $t['status']==='closed'?'selected':''; ?>>Closed</option>
                                </select>
                                <button type="submit"><i class="fas fa-save"></i> Save</button>
                            </div>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="ticket-row">No tickets found for this filter.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
