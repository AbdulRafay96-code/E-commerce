<?php
/**
 * Admin auth + role guard helpers (SRS §3.4, NFR-SEC-03).
 * Include at the top of every admin page AFTER session_start().
 */

/**
 * Redirect to login if not an admin.
 */
function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../admin_login.php");
        exit();
    }
}

/**
 * Enforce role-based access. Pass an array of roles allowed to view the page.
 * 'super' is always allowed.
 *
 * Example: requireAdminRole(['order_manager']); // super + order_manager
 */
function requireAdminRole(array $allowedRoles) {
    requireAdmin();
    $role = $_SESSION['admin_role'] ?? 'super';

    if ($role === 'super') return; // super sees everything

    if (!in_array($role, $allowedRoles, true)) {
        // NFR-SEC-03 — log unauthorized attempts (stderr for now)
        error_log(sprintf(
            "Unauthorized admin access: user=%s role=%s page=%s",
            $_SESSION['admin_username'] ?? 'unknown',
            $role,
            $_SERVER['REQUEST_URI'] ?? 'unknown'
        ));
        http_response_code(403);
        echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:40px;background:#F5F0E8;color:#1A1A1A;">';
        echo '<h1 style="font-family:Playfair Display,serif;">403 &mdash; Access Denied</h1>';
        echo '<p>Your role (<strong>' . htmlspecialchars($role) . '</strong>) is not authorised to view this page.</p>';
        echo '<p><a href="admin_dashboard.php" style="color:#8B6914;">&larr; Back to dashboard</a></p>';
        echo '</body></html>';
        exit();
    }
}

/**
 * Check (without redirect) — useful for hiding menu items the user cannot visit.
 */
function adminHasRole(array $allowedRoles) {
    $role = $_SESSION['admin_role'] ?? 'super';
    if ($role === 'super') return true;
    return in_array($role, $allowedRoles, true);
}

/**
 * Convenience: current role.
 */
function adminRole() {
    return $_SESSION['admin_role'] ?? 'super';
}
