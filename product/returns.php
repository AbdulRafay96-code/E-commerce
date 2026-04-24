<?php
/**
 * Returns & Exchange Policy — Stitch House
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

$pageTitle = 'Returns & Exchange - Stitch House';
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
            <h1>Returns &amp; Exchange</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>Returns &amp; Exchange</span>
            </div>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="info-panel">
                <p class="info-intro">
                    Every Stitch House kameez is custom-stitched to your exact measurements — which
                    means standard "no-questions-asked" returns don't apply the way they do for
                    ready-to-wear. That said, we stand behind our work. Here's how we make it right.
                </p>

                <h2><i class="fas fa-check-circle"></i> What We Cover</h2>
                <ul class="info-list">
                    <li><strong>Manufacturing defects</strong> — torn stitching, missing buttons, fabric flaws: full exchange or refund.</li>
                    <li><strong>Wrong item delivered</strong> — wrong color, wrong style, wrong size: full exchange at our cost.</li>
                    <li><strong>Fit issues from our tailoring error</strong> — if we got the measurements wrong, free re-stitching.</li>
                    <li><strong>Damaged in transit</strong> — report within 48 hours with photos; we handle the courier claim.</li>
                </ul>

                <h2><i class="fas fa-times-circle"></i> What We Don't Cover</h2>
                <ul class="info-list">
                    <li>Change of mind after the garment has been cut and stitched.</li>
                    <li>Fit issues caused by incorrect measurements you provided.</li>
                    <li>Minor color variations (monitor displays vary from real fabric).</li>
                    <li>Damage caused by improper washing or wear after delivery.</li>
                </ul>

                <h2><i class="fas fa-calendar-alt"></i> Timeline</h2>
                <div class="info-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Window</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Report defect or wrong item</td><td>Within <strong>7 days</strong> of delivery</td></tr>
                            <tr><td>Request fit re-stitching</td><td>Within <strong>14 days</strong> of delivery</td></tr>
                            <tr><td>Pickup of returned item</td><td>2–3 working days after report</td></tr>
                            <tr><td>Inspection &amp; decision</td><td>3–4 working days after pickup</td></tr>
                            <tr><td>Refund processing</td><td>5–7 business days after approval</td></tr>
                        </tbody>
                    </table>
                </div>

                <h2><i class="fas fa-tools"></i> Free Re-Stitching (Fit Adjustment)</h2>
                <p>
                    If the kameez doesn't fit right because of a tailoring error on our end, we'll
                    alter it at no cost. Typical adjustments we handle:
                </p>
                <ul class="info-list two-col">
                    <li>Taking in the waist or chest</li>
                    <li>Shortening sleeve or kameez length</li>
                    <li>Widening the collar or cuff</li>
                    <li>Adjusting trouser length</li>
                </ul>
                <p>
                    Major reworks (changing size grade, different collar style, etc.) may incur a
                    partial fee — we'll quote before proceeding.
                </p>

                <h2><i class="fas fa-hand-holding-usd"></i> Refunds</h2>
                <ul class="info-list">
                    <li>COD orders: refunded via bank transfer (you'll share account details).</li>
                    <li>Online payments: refunded to the original payment method.</li>
                    <li>Shipping charges are refunded only if the fault was on our side.</li>
                </ul>

                <h2><i class="fas fa-comments"></i> How to Start a Return or Exchange</h2>
                <ol class="info-list">
                    <li>Visit our <a href="contact.php">Contact Us</a> page.</li>
                    <li>Submit a ticket with your <strong>Order ID</strong> and photos of the issue.</li>
                    <li>Our support team (usually within 24 hours) will confirm next steps.</li>
                    <li>We arrange free courier pickup from your address.</li>
                    <li>After inspection, we process the replacement, re-stitching, or refund.</li>
                </ol>

                <div class="info-callout">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Tip:</strong>
                        Use our <a href="dashboard.php?tab=measurements">saved measurements feature</a>
                        — once your measurements are on file, every future order uses the exact same
                        numbers, drastically reducing fit issues.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
