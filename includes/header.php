<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isSubfolder = (strpos($_SERVER['PHP_SELF'], '/pages/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/actions/') !== false);
$base = $isSubfolder ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : "D'FACEEE"; ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo $base; ?>assets/images/logo.svg?v=1">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css?v=1">
</head>
<body>

<nav class="navbar">
    <a href="<?php echo $base; ?>index.php" class="nav-logo">D'FACEEE</a>
    <div class="nav-links" style="display: flex; gap: 15px; align-items: center;">
        <?php if ($currentPage !== 'booking.php' && $currentPage !== 'checkout.php'): ?>
            <a href="<?php echo $base; ?>pages/booking.php" class="btn-nav">Book Now</a>
        <?php else: ?>
            <a href="<?php echo $base; ?>index.php" class="btn-nav-outline">Back to Home</a>
        <?php endif; ?>
        
        <!-- Admin Access Button -->
        <a href="<?php echo $base; ?>admin/index.php" class="btn-nav-outline" style="font-size: 0.8rem; padding: 8px 15px; display: flex; align-items: center; gap: 5px;">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg>
            Admin
        </a>
    </div>
</nav>