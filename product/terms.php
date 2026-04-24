<?php
/**
 * Terms of Service — Stitch House
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

$pageTitle = 'Terms of Service - Stitch House';
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
            <h1>Terms of Service</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>Terms of Service</span>
            </div>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="info-panel">
                <p class="info-intro">
                    By creating an account or placing an order with Stitch House, you agree to the
                    terms below. Please read them carefully.
                </p>
                <p style="font-size: 13px; color: #888;">
                    <strong>Last updated:</strong> April 2026
                </p>

                <h2><i class="fas fa-handshake"></i> 1. Acceptance of Terms</h2>
                <p>
                    By accessing or using the Stitch House website, you agree to be bound by these
                    Terms of Service. If you do not agree, please do not use our services.
                </p>

                <h2><i class="fas fa-user"></i> 2. Your Account</h2>
                <ul class="info-list">
                    <li>You must provide accurate and up-to-date information when creating an account.</li>
                    <li>You are responsible for keeping your password secure. We cannot be held liable for unauthorized account access caused by weak or shared passwords.</li>
                    <li>One person may register only one account for personal use. Multiple accounts may be suspended at our discretion.</li>
                    <li>You must be at least 18 years of age, or have parental consent, to place an order.</li>
                </ul>

                <h2><i class="fas fa-shopping-cart"></i> 3. Orders &amp; Custom Tailoring</h2>
                <ul class="info-list">
                    <li>Every garment is custom-stitched to your measurements. Once tailoring has begun, cancellation may not be possible.</li>
                    <li>Measurement accuracy is your responsibility — whether entered manually or captured via our AI tool.</li>
                    <li>We reserve the right to cancel any order if the fabric is out of stock, payment cannot be verified, or the order violates these Terms.</li>
                    <li>Order pricing is displayed in Pakistani Rupees (PKR) and includes applicable taxes unless stated otherwise.</li>
                </ul>

                <h2><i class="fas fa-credit-card"></i> 4. Payment</h2>
                <ul class="info-list">
                    <li>Currently only Cash on Delivery (COD) is accepted. Online card payments may be added later.</li>
                    <li>Full payment is due to the courier upon delivery. Refusal to pay may result in restrictions on future orders.</li>
                </ul>

                <h2><i class="fas fa-truck"></i> 5. Delivery</h2>
                <p>
                    Delivery timelines are estimates, not guarantees. See our
                    <a href="shipping-policy.php">Shipping Policy</a> for current timelines and coverage.
                    Delays caused by courier services, public holidays, or force majeure are outside our
                    control.
                </p>

                <h2><i class="fas fa-undo"></i> 6. Returns &amp; Refunds</h2>
                <p>
                    Because every kameez is custom-made, standard return policies don't apply.
                    Manufacturing defects, wrong items, or tailoring errors are eligible for exchange,
                    re-stitching, or refund per our <a href="returns.php">Returns Policy</a>.
                </p>

                <h2><i class="fas fa-copyright"></i> 7. Intellectual Property</h2>
                <p>
                    All website content — including images, text, logos, fabric designs, and the
                    Stitch House brand — is our property or used under licence. You may not reproduce,
                    modify, or distribute any content without prior written permission.
                </p>

                <h2><i class="fas fa-shield-alt"></i> 8. Limitation of Liability</h2>
                <p>
                    Stitch House is not liable for any indirect, incidental, or consequential damages
                    arising from your use of the service. Our total liability for any claim is limited
                    to the amount you paid for the specific order giving rise to the claim.
                </p>

                <h2><i class="fas fa-ban"></i> 9. Prohibited Conduct</h2>
                <ul class="info-list">
                    <li>Submitting false or fraudulent orders.</li>
                    <li>Abusing the AI measurement system or uploading inappropriate content.</li>
                    <li>Attempting to hack, reverse engineer, or disrupt the service.</li>
                    <li>Reselling our products without authorization.</li>
                </ul>

                <h2><i class="fas fa-edit"></i> 10. Changes to These Terms</h2>
                <p>
                    We may update these Terms periodically. Material changes will be communicated via
                    email or site notice. Continued use of the service after changes constitutes
                    acceptance of the updated Terms.
                </p>

                <h2><i class="fas fa-gavel"></i> 11. Governing Law</h2>
                <p>
                    These Terms are governed by the laws of the Islamic Republic of Pakistan. Any
                    disputes will be resolved in the courts of Karachi.
                </p>

                <h2><i class="fas fa-envelope"></i> 12. Contact</h2>
                <p>
                    Questions about these Terms? <a href="contact.php">Contact our support team</a>
                    and we'll get back to you within 24 hours on working days.
                </p>

                <div class="info-callout">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        See also: <a href="privacy.php">Privacy Policy</a> ·
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
