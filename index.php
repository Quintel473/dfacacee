<?php
$pageTitle = "D'FACEEE | ShamySpice Collection";
include 'includes/header.php';
?>

<!-- ══════════ HERO — SPLIT LAYOUT ══════════ -->
<header class="hero">
    <div class="hero-split">
        <div class="hero-text" data-aos="fade-right">
            <span class="hero-tagline">Premium Beauty Studio</span>
            <h1>D'FACEEE</h1>
            <h2>ShamySpice Collection</h2>
            <p>Where looks says it all. Experience luxury beauty treatments tailored to your unique glow.</p>
            <div class="hero-actions">
                <!-- Fixed links to relative path -->
                <a href="pages/booking.php" class="btn-primary">Book Appointment</a>
                <a href="#services" class="btn-ghost">Explore Services</a>
            </div>
            <div class="hero-stats">
                <div><strong>500+</strong><span>Happy Clients</span></div>
                <div><strong>5★</strong><span>Rated</span></div>
                <div><strong>7+</strong><span>Years Exp.</span></div>
            </div>
        </div>
        <div class="hero-visual" data-aos="fade-left">
            <div class="hero-visual-inner">
                <div class="visual-circle"></div>
                <div class="visual-card">
                    <span class="visual-icon">💄</span>
                    <h3>Signature Glam</h3>
                    <p>Book your transformation</p>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ══════════ SERVICES ══════════ -->
<section class="section section-services" id="services">
    <div class="section-header" data-aos="fade-up">
        <span class="section-label">What We Offer</span>
        <h2>Our Signature Services</h2>
        <p>Every service is crafted to make you feel and look your absolute best.</p>
    </div>

    <div class="services-list">
        <!-- Service 1 -->
        <div class="service-row" data-aos="fade-up">
            <div class="service-visual" style="background: linear-gradient(135deg, #C5A059, #A8833A);">
                <span class="service-visual-emoji">💄</span>
            </div>
            <div class="service-info">
                <span class="service-number">01</span>
                <h3>Makeup Application</h3>
                <p>Flawless, long-lasting makeup tailored to your skin tone, event, and personality. We use only premium products that photograph beautifully.</p>
                <div class="service-meta">
                    <span class="service-price">From $100</span>
                    <a href="pages/booking.php" class="btn-link">Book Now →</a>
                </div>
            </div>
        </div>

        <!-- Service 2 -->
        <div class="service-row reverse" data-aos="fade-up">
            <div class="service-visual" style="background: linear-gradient(135deg, #1A1A1A, #333);">
                <span class="service-visual-emoji">✨</span>
            </div>
            <div class="service-info">
                <span class="service-number">02</span>
                <h3>Facial Treatment</h3>
                <p>Rejuvenating facials that cleanse, hydrate, and restore your natural glow. Our treatment plans are personalized for every skin type.</p>
                <div class="service-meta">
                    <span class="service-price">From $80</span>
                    <a href="pages/booking.php" class="btn-link">Book Now →</a>
                </div>
            </div>
        </div>

        <!-- Service 3 -->
        <div class="service-row" data-aos="fade-up">
            <div class="service-visual" style="background: linear-gradient(135deg, #FDFBF7, #eee); color: #1A1A1A;">
                <span class="service-visual-emoji">👑</span>
            </div>
            <div class="service-info">
                <span class="service-number">03</span>
                <h3>Full Glam Session</h3>
                <p>Our premium package. Hair, makeup, and finishing touches for weddings, photoshoots, and special events. The full experience.</p>
                <div class="service-meta">
                    <span class="service-price">From $150</span>
                    <a href="pages/booking.php" class="btn-link">Book Now →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════ GALLERY ══════════ -->
<section class="section section-gallery">
    <div class="section-header" data-aos="fade-up">
        <span class="section-label">Our Work</span>
        <h2>Moments of Transformation</h2>
        <p>A glimpse into the looks we've brought to life.</p>
    </div>

    <div class="gallery-masonry">
        <div class="gallery-card g-tall" data-aos="zoom-in" style="background: linear-gradient(135deg, #C5A059, #A8833A);">
            <span class="gallery-tag">Bridal</span>
            <span class="gallery-label">Look 01</span>
        </div>
        <div class="gallery-card" data-aos="zoom-in" data-aos-delay="100" style="background: linear-gradient(135deg, #1A1A1A, #333);">
            <span class="gallery-tag">Glam</span>
            <span class="gallery-label">Look 02</span>
        </div>
        <div class="gallery-card" data-aos="zoom-in" data-aos-delay="200" style="background: linear-gradient(135deg, #eee, #FDFBF7); color: #1A1A1A;">
            <span class="gallery-tag">Soft Glam</span>
            <span class="gallery-label">Look 03</span>
        </div>
        <div class="gallery-card g-wide" data-aos="zoom-in" data-aos-delay="300" style="background: linear-gradient(135deg, #A8833A, #C5A059);">
            <span class="gallery-tag">Photoshoot</span>
            <span class="gallery-label">Look 04</span>
        </div>
        <div class="gallery-card" data-aos="zoom-in" data-aos-delay="400" style="background: linear-gradient(135deg, #333, #1A1A1A);">
            <span class="gallery-tag">Editorial</span>
            <span class="gallery-label">Look 05</span>
        </div>
        <div class="gallery-card g-tall" data-aos="zoom-in" data-aos-delay="500" style="background: linear-gradient(135deg, #FDFBF7, #ddd); color: #1A1A1A;">
            <span class="gallery-tag">Natural</span>
            <span class="gallery-label">Look 06</span>
        </div>
    </div>
</section>

<!-- ══════════ TESTIMONIALS ══════════ -->
<section class="section section-testimonials">
    <div class="testimonials-split">
        <div class="testimonials-intro" data-aos="fade-right">
            <span class="section-label">Kind Words</span>
            <h2>Loved by<br><em>Hundreds</em></h2>
            <p>Don't take our word for it — hear from the women who trust us with their most important moments.</p>
            <div class="rating-display">
                <span class="stars">★★★★★</span>
                <span class="rating-text">4.9 out of 5 — 200+ reviews</span>
            </div>
        </div>

        <div class="testimonials-stack" data-aos="fade-left">
            <div class="testimonial-card">
                <div class="quote">"</div>
                <p>Absolutely stunning work. I felt like a queen for my wedding day — the makeup lasted all night through tears and dancing!</p>
                <div class="testimonial-author">
                    <div class="author-avatar">A</div>
                    <div>
                        <strong>Amara J.</strong>
                        <span>Bridal Client</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="quote">"</div>
                <p>The facial treatment was pure bliss. My skin has never looked or felt better. Already booked my next session!</p>
                <div class="testimonial-author">
                    <div class="author-avatar">K</div>
                    <div>
                        <strong>Keisha M.</strong>
                        <span>Regular Client</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="quote">"</div>
                <p>Shamy is a true artist. Professional, punctual, and incredibly talented. The full glam package was worth every penny.</p>
                <div class="testimonial-author">
                    <div class="author-avatar">S</div>
                    <div>
                        <strong>Simone R.</strong>
                        <span>Event Client</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════ CTA ══════════ -->
<section class="cta-banner">
    <div class="cta-split">
        <div class="cta-text" data-aos="fade-right">
            <span class="section-label" style="color: var(--gold);">Ready to Glow?</span>
            <h2>Book Your<br>Transformation Today</h2>
            <p>Limited availability each week. Secure your spot and let us make you feel unstoppable.</p>
            <a href="pages/booking.php" class="btn-primary" style="background: var(--gold);">Book Appointment</a>
        </div>
        <div class="cta-visual" data-aos="fade-left">
            <div class="cta-badge">
                <span class="badge-icon">✨</span>
                <span class="badge-text">50% deposit secures your slot</span>
            </div>
            <div class="cta-badge">
                <span class="badge-icon">🕐</span>
                <span class="badge-text">24-hour cancellation policy</span>
            </div>
            <div class="cta-badge">
                <span class="badge-icon">💎</span>
                <span class="badge-text">Premium products only</span>
            </div>
        </div>
    </div>
</section>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true, offset: 50 });</script>

<?php include 'includes/footer.php'; ?>