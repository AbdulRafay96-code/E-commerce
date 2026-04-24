<?php
/**
 * Privacy Policy — Stitch House
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

$pageTitle = 'Privacy Policy - Stitch House';
$pageStyles = ['info-page.css'];
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
            <h1>Privacy Policy</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>Privacy Policy</span>
            </div>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="info-panel">
                <p class="info-intro">
                    Your privacy matters. This policy explains what information we collect, how we use
                    it, and the choices you have. In plain English, not lawyer-speak.
                </p>
                <p style="font-size: 13px; color: #888;">
                    <strong>Last updated:</strong> April 2026
                </p>

                <h2><i class="fas fa-database"></i> 1. What We Collect</h2>
                <p>We only collect data we need to deliver your order and improve your experience.</p>
                <ul class="info-list">
                    <li><strong>Account info:</strong> name, email, phone, shipping address.</li>
                    <li><strong>Measurements:</strong> body measurements (manually entered or captured via AI webcam) and saved presets.</li>
                    <li><strong>Order history:</strong> fabrics purchased, customizations chosen, payment method, order status.</li>
                    <li><strong>Support interactions:</strong> tickets you submit and our responses.</li>
                    <li><strong>Technical data:</strong> basic session info (cookie with your session ID), IP address for security logging.</li>
                </ul>

                <h2><i class="fas fa-video"></i> 2. Webcam &amp; AI Measurement</h2>
                <ul class="info-list">
                    <li>When you use the AI measurement feature, your webcam feed is processed <strong>entirely in your browser</strong> using Google's MediaPipe Pose library.</li>
                    <li><strong>No video is uploaded</strong> to our servers or stored anywhere. Only the final calculated measurements (numbers) are sent when you click Save.</li>
                    <li>You can always opt for manual measurement entry instead.</li>
                </ul>

                <h2><i class="fas fa-tools"></i> 3. How We Use Your Data</h2>
                <ul class="info-list">
                    <li>To tailor and deliver your order.</li>
                    <li>To provide order tracking and account features.</li>
                    <li>To respond to support inquiries.</li>
                    <li>To detect and prevent fraud or abuse.</li>
                    <li>To improve our service (aggregated analytics only — no individual profiling).</li>
                </ul>

                <h2><i class="fas fa-user-shield"></i> 4. What We Don't Do</h2>
                <ul class="info-list">
                    <li>We <strong>never sell</strong> your personal data to third parties.</li>
                    <li>We <strong>never share</strong> your measurements with anyone outside our tailoring team.</li>
                    <li>We <strong>never</strong> send marketing spam. Emails are limited to order status and support responses.</li>
                </ul>

                <h2><i class="fas fa-share-alt"></i> 5. Third Parties We Rely On</h2>
                <p>To deliver the service, we share minimum necessary data with:</p>
                <ul class="info-list">
                    <li><strong>Courier partners</strong> (TCS, Leopards, Pakistan Post): name, address, phone, order contents label.</li>
                    <li><strong>MediaPipe</strong> (Google): runs locally in your browser — Google receives no order or identity data from us.</li>
                    <li><strong>Hosting provider:</strong> stores our database and serves the website.</li>
                </ul>

                <h2><i class="fas fa-lock"></i> 6. How We Protect Your Data</h2>
                <ul class="info-list">
                    <li>Passwords are hashed with bcrypt — we never store plain text.</li>
                    <li>All traffic is served over HTTPS in production (encrypted end-to-end).</li>
                    <li>Role-based access control — only authorized staff can view customer data.</li>
                    <li>Unauthorized admin access attempts are logged and monitored.</li>
                </ul>

                <h2><i class="fas fa-cookie-bite"></i> 7. Cookies</h2>
                <p>We use a minimal set of cookies:</p>
                <ul class="info-list">
                    <li><strong>Session cookie (PHPSESSID):</strong> required for login and cart persistence. Expires when you close the browser.</li>
                    <li><strong>Local storage:</strong> stores your cart items and floating-cart button position — entirely on your device.</li>
                    <li>No third-party tracking cookies, no advertising pixels, no social plugins that spy on you.</li>
                </ul>

                <h2><i class="fas fa-user-check"></i> 8. Your Rights</h2>
                <p>You have the right to:</p>
                <ul class="info-list">
                    <li><strong>Access</strong> the personal data we hold about you — view it anytime in your dashboard.</li>
                    <li><strong>Correct</strong> inaccurate data — edit profile in <a href="dashboard.php?tab=profile">My Account</a>.</li>
                    <li><strong>Delete</strong> your account and data — contact support to initiate.</li>
                    <li><strong>Export</strong> your order history and measurements — request via <a href="contact.php">Contact Us</a>.</li>
                </ul>

                <h2><i class="fas fa-child"></i> 9. Children's Privacy</h2>
                <p>
                    Our service is intended for users 18 and older. We do not knowingly collect data
                    from children under 18 without parental consent.
                </p>

                <h2><i class="fas fa-history"></i> 10. Data Retention</h2>
                <ul class="info-list">
                    <li>Active accounts: data retained as long as your account is active.</li>
                    <li>Deleted accounts: personal data removed within 30 days, except where required for legal/tax records.</li>
                    <li>Order history: retained for 7 years for tax and accounting compliance.</li>
                </ul>

                <h2><i class="fas fa-edit"></i> 11. Changes to This Policy</h2>
                <p>
                    If we change how we handle your data, we'll update this page and notify you if the
                    change is material. The "Last updated" date at the top will always reflect the
                    current version.
                </p>

                <h2><i class="fas fa-envelope"></i> 12. Questions?</h2>
                <p>
                    Reach out via <a href="contact.php">Contact Us</a>. We aim to respond to all
                    privacy-related inquiries within 48 hours.
                </p>

                <div class="info-callout">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        See also: <a href="terms.php">Terms of Service</a> ·
                        <a href="shipping-policy.php">Shipping</a> ·
                        <a href="returns.php">Returns</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
