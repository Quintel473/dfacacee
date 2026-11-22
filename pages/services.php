<?php
require '../includes/db.php';
require '../includes/icons.php';
$pageTitle = "Our Services | D'FACEEE";
include '../includes/header.php';

$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY price ASC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section section-services" style="padding-top: 120px;">
    <div class="section-header" data-aos="fade-up">
        <span class="section-label">Menu</span>
        <h2>Services & Pricing</h2>
        <p>Discover the perfect look for your next event.</p>
    </div>

    <div class="services-catalog">
        <?php foreach($services as $svc): ?>
            <div class="catalog-card" data-aos="fade-up">
                <div class="catalog-media">
                    <?php if ($svc['media_url']): ?>
                        <?php if ($svc['media_type'] === 'video'): ?>
                            <video src="../<?php echo htmlspecialchars($svc['media_url']); ?>" autoplay loop muted playsinline></video>
                        <?php else: ?>
                            <img src="../<?php echo htmlspecialchars($svc['media_url']); ?>" alt="<?php echo htmlspecialchars($svc['name']); ?>">
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="catalog-placeholder" style="color: var(--gold);">
                            <?php echo getServiceIcon($svc['icon']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="catalog-info">
                    <div class="catalog-header">
                        <h3><?php echo htmlspecialchars($svc['name']); ?></h3>
                        <span class="catalog-price">$<?php echo number_format($svc['price'], 2); ?></span>
                    </div>
                    <p class="catalog-desc"><?php echo htmlspecialchars($svc['description']); ?></p>
                    <div class="catalog-footer">
                        <span class="catalog-duration">⏱ <?php echo htmlspecialchars($svc['duration']); ?></span>
                        <a href="booking.php" class="btn-primary" style="padding: 10px 20px; font-size: 0.85rem;">Book Now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>