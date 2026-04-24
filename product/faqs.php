<?php
/**
 * FAQs — Stitch House
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

$pageTitle = 'FAQs - Stitch House';
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
            <h1>Frequently Asked Questions</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>FAQs</span>
            </div>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="info-panel">
                <p class="info-intro">
                    Everything you need to know about ordering, customization, measurements, and
                    delivery. Can't find your answer? <a href="contact.php">Contact our support team</a>.
                </p>

                <h2><i class="fas fa-shopping-bag"></i> Ordering</h2>
                <div class="faq">
                    <details open>
                        <summary>How do I place an order?</summary>
                        <p>Browse fabrics on the <a href="order.php">Shop</a> page, click <em>Add to Order</em>
                        on the fabrics you want, then go to <em>My Order</em> (scissors icon) to customize the
                        design and enter your measurements. When ready, add to cart and checkout.</p>
                    </details>
                    <details>
                        <summary>Do I need an account to order?</summary>
                        <p>No — you can check out as a guest. But creating an account lets you track orders,
                        save measurements for reuse, and access your purchase history.</p>
                    </details>
                    <details>
                        <summary>Can I order multiple kameez in one order?</summary>
                        <p>Yes. Each kameez can have its own fabric, customization, and measurements.
                        Add them one at a time through the <em>My Order</em> wizard.</p>
                    </details>
                    <details>
                        <summary>Can I cancel or modify my order after placing it?</summary>
                        <p>Orders can be cancelled while in <em>Pending</em> or <em>Processing</em> status.
                        Once the fabric has been cut (<em>In Tailoring</em>), cancellation is no longer
                        possible. Contact support immediately if you need to change anything.</p>
                    </details>
                </div>

                <h2><i class="fas fa-tshirt"></i> Customization</h2>
                <div class="faq">
                    <details>
                        <summary>What can I customize?</summary>
                        <p>Collar, kameez style, daman shape, cuff, button placket, bottom cut, front pocket,
                        side pockets, and fit preference (slim / regular / loose). Ten steps total.</p>
                    </details>
                    <details>
                        <summary>Do different customizations change the price?</summary>
                        <p>The base price is the fabric cost. Premium options may add a small surcharge,
                        which is shown live in the subtotal as you select.</p>
                    </details>
                    <details>
                        <summary>Can I see a preview of my kameez before ordering?</summary>
                        <p>The final review step (Step 10) shows a summary of all your selections.
                        Digital 3D preview is on our roadmap for a future release.</p>
                    </details>
                </div>

                <h2><i class="fas fa-ruler"></i> Measurements</h2>
                <div class="faq">
                    <details>
                        <summary>How does the AI measurement work?</summary>
                        <p>We use Google's MediaPipe pose estimation to track body landmarks via your
                        webcam. After a short calibration using your height, the system calculates all 8
                        measurements automatically. Full details in our <a href="size-guide.php">Size Guide</a>.</p>
                    </details>
                    <details>
                        <summary>Is AI measurement accurate?</summary>
                        <p>Under good lighting with a stable pose at ~6 feet distance, AI measurements are
                        typically within ±0.5 inch of a tape measure. For best results, wear fitted clothes
                        and follow the on-screen distance indicator.</p>
                    </details>
                    <details>
                        <summary>Can I enter measurements manually instead?</summary>
                        <p>Yes. The measurement form accepts manual entry for all 8 fields, with helpful
                        hints for each.</p>
                    </details>
                    <details>
                        <summary>What if I don't know my measurements?</summary>
                        <p>Use our standard <a href="size-guide.php">size chart</a> as a starting point, or
                        use the AI webcam capture which figures it out for you.</p>
                    </details>
                    <details>
                        <summary>Do I need to re-enter measurements every order?</summary>
                        <p>No. Logged-in customers can save measurement sets in
                        <a href="dashboard.php?tab=measurements">My Measurements</a> and reuse them across
                        every future order.</p>
                    </details>
                </div>

                <h2><i class="fas fa-truck"></i> Delivery</h2>
                <div class="faq">
                    <details>
                        <summary>How long does delivery take?</summary>
                        <p>Typical turnaround is 8–12 working days from order placement: 5–7 days for
                        tailoring + 2–4 days for shipping. See our <a href="shipping-policy.php">Shipping
                        Policy</a> for details.</p>
                    </details>
                    <details>
                        <summary>How much is shipping?</summary>
                        <p>Flat PKR 200. Free on orders over PKR 5,000.</p>
                    </details>
                    <details>
                        <summary>Do you ship internationally?</summary>
                        <p>Not yet — currently we serve Pakistan only. International shipping is planned
                        for a future phase.</p>
                    </details>
                    <details>
                        <summary>How do I track my order?</summary>
                        <p>Log in and go to <a href="dashboard.php?tab=orders">My Account → Order History</a>.
                        A visual timeline shows your order's current stage: Pending, Processing,
                        In Tailoring, Shipped, or Completed.</p>
                    </details>
                </div>

                <h2><i class="fas fa-credit-card"></i> Payment</h2>
                <div class="faq">
                    <details>
                        <summary>What payment methods do you accept?</summary>
                        <p>Currently Cash on Delivery (COD) only. Online card/bank payments are planned
                        for a future release.</p>
                    </details>
                    <details>
                        <summary>Is there any advance payment required?</summary>
                        <p>No — pay the full amount in cash to the courier when your order arrives.</p>
                    </details>
                </div>

                <h2><i class="fas fa-life-ring"></i> Support &amp; Issues</h2>
                <div class="faq">
                    <details>
                        <summary>What if my kameez doesn't fit?</summary>
                        <p>If the fit issue is due to our tailoring error, we re-stitch for free within 14
                        days of delivery. See <a href="returns.php">Returns &amp; Exchange</a>.</p>
                    </details>
                    <details>
                        <summary>How do I contact support?</summary>
                        <p>Submit a support ticket via <a href="contact.php">Contact Us</a>. You'll get a
                        ticket number for tracking, and our team typically responds within 24 hours.</p>
                    </details>
                    <details>
                        <summary>I forgot my password — how do I reset it?</summary>
                        <p>Password reset is coming in a future release. For now, reach out via
                        <a href="contact.php">Contact Us</a> and we'll help you manually.</p>
                    </details>
                </div>

                <div class="info-callout">
                    <i class="fas fa-question-circle"></i>
                    <div>
                        <strong>Didn't find your answer?</strong>
                        Head to our <a href="contact.php">Contact Us</a> page or WhatsApp us at the
                        number in the footer. We're here to help.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
