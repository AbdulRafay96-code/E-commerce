<?php
/**
 * Size Guide — Stitch House
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

$pageTitle = 'Size Guide - Stitch House';
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
            <h1>Size Guide</h1>
            <div class="breadcrumb">
                <a href="index.php">Home</a> / <span>Size Guide</span>
            </div>
        </div>
    </section>

    <section class="info-section">
        <div class="container">
            <div class="info-panel">
                <p class="info-intro">
                    A well-fitting shalwar kameez starts with accurate measurements. Use this guide
                    to take your own measurements, or let our AI do it for you via webcam.
                </p>

                <div class="info-callout" style="background: var(--bg-light); border-left-color: var(--gold-color);">
                    <i class="fas fa-camera"></i>
                    <div>
                        <strong>Skip the tape measure.</strong>
                        Our AI can capture all 8 measurements in under a minute using your webcam —
                        just stand ~6 feet away and follow on-screen guidance. Available at the
                        measurement step during checkout.
                    </div>
                </div>

                <h2><i class="fas fa-ruler"></i> How to Measure Yourself</h2>
                <p>
                    You'll need a soft measuring tape and ideally a friend to help. Wear fitted
                    clothes (or measure over a thin t-shirt) so the tape sits close to the body.
                </p>
                <ul class="info-list">
                    <li><strong>Chest:</strong> Measure around the fullest part of your chest, keeping the tape horizontal.</li>
                    <li><strong>Waist:</strong> Around your natural waistline (the narrowest point, usually just above the belly button).</li>
                    <li><strong>Hip:</strong> Around the fullest part of your hips, feet together.</li>
                    <li><strong>Shoulder:</strong> Across your back, from the tip of one shoulder to the other.</li>
                    <li><strong>Sleeve Length:</strong> From shoulder tip, down the outside of your arm, to your wrist bone.</li>
                    <li><strong>Kameez Length:</strong> From shoulder, straight down to where you want the kameez to end (typically knee level).</li>
                    <li><strong>Trouser Length:</strong> From your natural waist, straight down to ankle bone.</li>
                    <li><strong>Neck:</strong> Around the base of your neck, where the collar will sit. Keep it snug, not tight.</li>
                </ul>

                <h2><i class="fas fa-table"></i> Standard Size Chart</h2>
                <p>
                    If you're unsure of your exact measurements, this chart gives a starting point.
                    All measurements in inches.
                </p>
                <div class="info-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Chest</th>
                                <th>Waist</th>
                                <th>Hip</th>
                                <th>Shoulder</th>
                                <th>Sleeve</th>
                                <th>Kameez</th>
                                <th>Trouser</th>
                                <th>Neck</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>S</strong></td><td>36</td><td>30</td><td>36</td><td>17</td><td>23</td><td>40</td><td>40</td><td>14</td></tr>
                            <tr><td><strong>M</strong></td><td>38</td><td>32</td><td>38</td><td>17.5</td><td>23.5</td><td>41</td><td>41</td><td>14.5</td></tr>
                            <tr><td><strong>L</strong></td><td>40</td><td>34</td><td>40</td><td>18</td><td>24</td><td>42</td><td>42</td><td>15</td></tr>
                            <tr><td><strong>XL</strong></td><td>42</td><td>36</td><td>42</td><td>18.5</td><td>24.5</td><td>43</td><td>43</td><td>15.5</td></tr>
                            <tr><td><strong>XXL</strong></td><td>44</td><td>38</td><td>44</td><td>19</td><td>25</td><td>44</td><td>44</td><td>16</td></tr>
                            <tr><td><strong>3XL</strong></td><td>46</td><td>40</td><td>46</td><td>19.5</td><td>25.5</td><td>45</td><td>45</td><td>16.5</td></tr>
                        </tbody>
                    </table>
                </div>

                <h2><i class="fas fa-user-tie"></i> Fit Preferences</h2>
                <p>
                    Once you've picked a size, choose how the garment should sit on you:
                </p>
                <ul class="info-list">
                    <li><strong>Slim Fit</strong> — Tapered through the body for a sharp, modern silhouette. Best with lighter fabrics like cotton and lawn.</li>
                    <li><strong>Regular Fit</strong> — Our most popular choice. Comfortable without being loose. Suits every fabric and body type.</li>
                    <li><strong>Loose Fit</strong> — Extra room through the chest and waist. Traditional relaxed silhouette, best for boski and khaddar.</li>
                </ul>

                <h2><i class="fas fa-lightbulb"></i> Measuring Tips</h2>
                <ul class="info-list">
                    <li>Stand naturally — don't suck in, don't puff out.</li>
                    <li>Keep the tape parallel to the floor for circumferences.</li>
                    <li>Measure twice; if the numbers disagree, take the average.</li>
                    <li>When in doubt, round up by half an inch — easier to take in than let out.</li>
                    <li>Add your notes field at checkout if you want a specific fit preference beyond slim/regular/loose.</li>
                </ul>

                <div class="info-callout">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Save your measurements once, reuse forever.</strong>
                        Logged-in customers can save measurement sets in
                        <a href="dashboard.php?tab=measurements">My Account → My Measurements</a> — no
                        need to re-enter on future orders.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
</body>
</html>
