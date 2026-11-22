<?php
require 'includes/db.php';
require 'includes/icons.php';
$pageTitle = "D'FACEEE | ShamySpice Collection";
include 'includes/header.php';

// Fetch Site Content
$contentStmt = $pdo->query("SELECT * FROM site_content");
$siteContent = [];
while ($row = $contentStmt->fetch(PDO::FETCH_ASSOC)) {
    $siteContent[$row['content_key']] = $row['content_value'];
}

// Fetch services for preview
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY price ASC LIMIT 3");
$homeServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ══════════ HERO ══════════ -->
<header class="hero">
    <div class="hero-split">
        <div class="hero-text" data-aos="fade-right">
            <span class="hero-tagline">Premium Beauty Studio</span>
            <h1>D'FACEEE</h1>
            <h2>ShamySpice Collection</h2>
            <p><?php echo htmlspecialchars($siteContent['hero_text'] ?? 'Where looks says it all. Experience luxury beauty treatments tailored to your unique glow.'); ?></p>
            <div class="hero-actions">
                <a href="pages/booking.php" class="btn-primary">Book Appointment</a>
                <a href="pages/services.php" class="btn-ghost">View All Services</a>
            </div>
        </div>
        <div class="hero-visual" data-aos="fade-left">
            <div class="hero-visual-inner">
                <div class="visual-circle"></div>
                <div class="visual-card">
                    <div class="visual-icon" style="color: var(--gold); width: 50px; height: 50px; margin: 0 auto 15px;">
                        <?php echo getServiceIcon('bridal-glam'); ?>
                    </div>
                    <h3>Signature Glam</h3>
                    <p>Book your transformation</p>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ══════════ SERVICES PREVIEW ══════════ -->
<section class="section section-services">
    <div class="section-header" data-aos="fade-up">
        <span class="section-label">What We Offer</span>
        <h2>Our Signature Services</h2>
        <p>Every service is crafted to make you feel and look your absolute best.</p>
    </div>

    <div class="services-list">
        <?php $i = 1; foreach($homeServices as $svc): ?>
        <div class="service-row <?php echo $i % 2 == 0 ? 'reverse' : ''; ?>" data-aos="fade-up">
            
            <!-- FIXED PATH: Removed ../ since index.php is in the root folder -->
            <div class="service-visual" style="background: <?php echo $svc['media_url'] ? "url('{$svc['media_url']}') center/cover no-repeat" : "linear-gradient(135deg, var(--gold), var(--gold-dark))"; ?>; display: flex; align-items: center; justify-content: center;">
                <?php if (!$svc['media_url']): ?>
                    <div style="width: 120px; height: 120px; color: white;">
                        <?php echo getServiceIcon($svc['icon']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="service-info">
                <span class="service-number">0<?php echo $i; ?></span>
                <h3><?php echo htmlspecialchars($svc['name']); ?></h3>
                <p><?php echo htmlspecialchars($svc['description']); ?></p>
                <div class="service-meta">
                    <span class="service-price">From $<?php echo number_format($svc['price'], 2); ?></span>
                    <a href="pages/booking.php" class="btn-link">Book Now →</a>
                </div>
            </div>
        </div>
        <?php $i++; endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 50px;" data-aos="fade-up">
        <a href="pages/services.php" class="btn-primary">View All Services & Pricing</a>
    </div>
</section>

<!-- ══════════ MY WORK (GALLERY) ══════════ -->
<section class="section section-gallery">
    <div class="section-header" data-aos="fade-up">
        <span class="section-label">My Work</span>
        <h2>Moments of Transformation</h2>
        <p>A glimpse into the looks I've brought to life.</p>
    </div>

    <div class="gallery-masonry">
        <?php 
        // Loop through gallery items stored in DB
        for ($i = 1; $i <= 6; $i++): 
            $imgUrl = $siteContent["gallery_$i"] ?? null;
            if (!$imgUrl) continue;
            $tallClass = ($i % 3 == 1) ? 'g-tall' : '';
        ?>
            <!-- FIXED PATH: Removed ../ since index.php is in the root folder -->
            <div class="gallery-card <?php echo $tallClass; ?>" data-aos="zoom-in" style="background-image: url('<?php echo htmlspecialchars($imgUrl); ?>'); background-size: cover; background-position: center;">
                <span class="gallery-label">Look <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></span>
            </div>
        <?php endfor; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>