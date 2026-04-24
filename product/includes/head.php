<?php
/**
 * Common Head Component
 * Include this in the <head> section of each page
 *
 * Usage:
 * $pageTitle = "Page Title";
 * $pageStyles = ['cart.css', 'checkout.css']; // optional additional CSS
 * include 'includes/head.php';
 */

$pageTitle = $pageTitle ?? 'Stitch House - Premium Fabric Store';
$pageStyles = $pageStyles ?? [];
// Critical paint background — dark for pages with a dark hero/banner at the top
$criticalBg = $criticalBg ?? '#F5F0E8';
// Cache bust: use CSS file mtime so browsers fetch fresh CSS on every edit
$cssVer = fn($f) => file_exists(__DIR__ . '/../' . $f) ? filemtime(__DIR__ . '/../' . $f) : time();
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="<?php echo e($criticalBg); ?>">
<title><?php echo e($pageTitle); ?></title>
<style>html,body{background-color:<?php echo e($criticalBg); ?>;margin:0;}</style>
<link rel="stylesheet" href="variables.css?v=<?php echo $cssVer('variables.css'); ?>">
<link rel="stylesheet" href="styles.css?v=<?php echo $cssVer('styles.css'); ?>">
<?php foreach ($pageStyles as $style): ?>
<link rel="stylesheet" href="<?php echo e($style); ?>?v=<?php echo $cssVer($style); ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500;600;700&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="3d-effects.css?v=<?php echo $cssVer('3d-effects.css'); ?>">
