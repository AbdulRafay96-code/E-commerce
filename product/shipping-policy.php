<?php
/**
 * Shipping Policy — Stitch House
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

$pageTitle = 'Shipping Policy - Stitch House';
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
            <h1>Shipping Policy</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>Shipping Policy</span>
            </div>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="info-panel">
                <p class="info-intro">
                    At Stitch House, we deliver your custom-stitched kameez to every corner of Pakistan.
                    Here's everything you need to know about how your order reaches you.
                </p>

                <h2><i class="fas fa-truck"></i> Delivery Charges</h2>
                <ul class="info-list">
                    <li><strong>Free delivery</strong> on orders over <strong>PKR 5,000</strong>.</li>
                    <li>Flat <strong>PKR 200</strong> shipping fee for orders under PKR 5,000.</li>
                    <li>No hidden fees at checkout — what you see is what you pay.</li>
                </ul>

                <h2><i class="fas fa-clock"></i> Delivery Timelines</h2>
                <p>
                    Because every kameez is custom-stitched to your measurements, orders take longer than
                    off-the-shelf retail. Typical timelines:
                </p>
                <div class="info-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Stage</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Order confirmation</td><td>Within 24 hours</td></tr>
                            <tr><td>Fabric cutting &amp; tailoring</td><td>5–7 working days</td></tr>
                            <tr><td>Quality check</td><td>1 day</td></tr>
                            <tr><td>Dispatch &amp; delivery</td><td>2–4 working days</td></tr>
                            <tr><td><strong>Total estimate</strong></td><td><strong>8–12 working days</strong></td></tr>
                        </tbody>
                    </table>
                </div>

                <h2><i class="fas fa-map-marker-alt"></i> Coverage Area</h2>
                <p>
                    We deliver nationwide across Pakistan, including but not limited to:
                </p>
                <ul class="info-list two-col">
                    <li>Karachi</li>
                    <li>Lahore</li>
                    <li>Islamabad</li>
                    <li>Rawalpindi</li>
                    <li>Faisalabad</li>
                    <li>Multan</li>
                    <li>Peshawar</li>
                    <li>Quetta</li>
                    <li>Sialkot</li>
                    <li>Gujranwala</li>
                    <li>Hyderabad</li>
                    <li>Bahawalpur</li>
                </ul>
                <p>
                    Orders to remote areas may take an additional 2–3 working days.
                </p>

                <h2><i class="fas fa-money-bill-wave"></i> Payment on Delivery</h2>
                <p>
                    Cash on Delivery (COD) is available across all serviceable cities. Pay the full
                    amount in cash to the courier at your doorstep — no advance required.
                </p>

                <h2><i class="fas fa-box"></i> Courier Partners</h2>
                <p>
                    We ship through trusted local courier services — TCS, Leopards Courier, and
                    Pakistan Post — selected based on your city for fastest delivery.
                </p>

                <h2><i class="fas fa-search"></i> Tracking Your Order</h2>
                <p>
                    Once logged in, visit <a href="dashboard.php?tab=orders">My Account → Order History</a>
                    to see your live status timeline:
                    <em>Pending → Processing → In Tailoring → Shipped → Completed</em>.
                </p>

                <div class="info-callout">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Still have questions?</strong>
                        Head to our <a href="contact.php">Contact Us</a> page or reach us on WhatsApp —
                        our team responds within a few hours on working days.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
