<?php
/**
 * Homepage - Stitch House
 */
require_once 'db_connection.php';
require_once 'includes/functions.php';
session_start();
initializeCart();

$pageTitle = 'Stitch House - Premium Fabric Store';
$criticalBg = '#1A1A1A'; // dark hero carousel at top
$cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Carousel -->
    <section class="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active-slide" style="background-image: url('images/1.jpg')">
                <div class="carousel-caption">
                    <h2>Premium Quality Fabrics</h2>
                    <p>Discover our exclusive collection of finest fabrics</p>
                    <a href="order.php" class="btn-shop-now">Shop Now</a>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('images/2.jpg')">
                <div class="carousel-caption">
                    <h2>Handcrafted Excellence</h2>
                    <p>Every thread tells a story of quality and craftsmanship</p>
                    <a href="order.php" class="btn-shop-now">Shop Now</a>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('images/3.jpg')">
                <div class="carousel-caption">
                    <h2>New Seasonal Collection</h2>
                    <p>Explore our latest arrivals for this season</p>
                    <a href="order.php" class="btn-shop-now">Shop Now</a>
                </div>
            </div>
        </div>
        <div class="carousel-controls">
            <div class="carousel-control prev"><i class="fas fa-chevron-left"></i></div>
            <div class="carousel-control next"><i class="fas fa-chevron-right"></i></div>
        </div>
        <div class="carousel-dots"></div>
    </section>

    <!-- Marquee Banner -->
    <section class="moving-text-banner">
        <div class="moving-text-container">
            <div class="moving-text gold-text">
                <span>STITCH HOUSE</span>
                <i class="fas fa-diamond"></i>
                <span>PREMIUM FABRICS</span>
                <i class="fas fa-diamond"></i>
                <span>SUMMER COLLECTION 2025</span>
                <i class="fas fa-diamond"></i>
                <span>CRAFTED WITH CARE</span>
                <i class="fas fa-diamond"></i>
                <span>STITCH HOUSE</span>
                <i class="fas fa-diamond"></i>
                <span>PREMIUM FABRICS</span>
                <i class="fas fa-diamond"></i>
                <span>SUMMER COLLECTION 2025</span>
                <i class="fas fa-diamond"></i>
                <span>CRAFTED WITH CARE</span>
                <i class="fas fa-diamond"></i>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonial-quotes">
        <div class="container">
            <div class="testimonial-carousel reveal">
                <div class="testimonial-slide active">
                    <i class="fas fa-quote-left"></i>
                    <p>The finest fabrics from Stitch House have transformed our wardrobe with unmatched quality and elegance. Exceptional craftsmanship and premium materials.</p>
                    <span class="testimonial-author">— Ahmed Khan, Lahore</span>
                </div>
                <div class="testimonial-slide">
                    <i class="fas fa-quote-left"></i>
                    <p>I ordered custom-tailored Khaddar fabric and the measurements were perfect. The AI measurement tool saved me a trip to the tailor. Highly recommended!</p>
                    <span class="testimonial-author">— Fatima Syed, Karachi</span>
                </div>
                <div class="testimonial-slide">
                    <i class="fas fa-quote-left"></i>
                    <p>Stitch House offers the best collection of Boski and Wash & Wear fabrics in Pakistan. Their customer service is outstanding and delivery is always on time.</p>
                    <span class="testimonial-author">— Hassan Malik, Islamabad</span>
                </div>
                <div class="testimonial-dots"></div>
            </div>
        </div>
    </section>

    <!-- Stats Counter -->
    <section class="stats-counter-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" data-target="8" data-suffix="+">0</div>
                    <div class="stat-label">Fabric Types</div>
                </div>
                <div class="stat-divider" aria-hidden="true"></div>
                <div class="stat-item">
                    <div class="stat-number" data-target="32" data-suffix="+">0</div>
                    <div class="stat-label">Products</div>
                </div>
                <div class="stat-divider" aria-hidden="true"></div>
                <div class="stat-item">
                    <div class="stat-number" data-target="1500" data-suffix="+">0</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-divider" aria-hidden="true"></div>
                <div class="stat-item">
                    <div class="stat-number" data-target="5" data-suffix=" Stars">0</div>
                    <div class="stat-label">Customer Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Fabrics - Bento Grid -->
    <section class="featured-products">
        <div class="container">
            <h2 class="reveal">Featured Fabrics</h2>
            <div class="bento-grid">
                <?php
                $fabrics = [
                    ['link' => 'cotton-section', 'image' => 'c1.jpg', 'name' => 'COTTON', 'desc' => 'Everyday comfort', 'full' => 'Premium quality cotton fabrics for comfortable everyday wear. Soft, breathable, and perfect for all seasons.', 'class' => 'bento-large'],
                    ['link' => 'lattha-section', 'image' => 'c2.webp', 'name' => 'LATTHA', 'desc' => 'Classic elegance', 'full' => 'Traditional lattha fabrics with a refined texture. Ideal for formal and semi-formal occasions.', 'class' => ''],
                    ['link' => 'karandi-section', 'image' => 'c3.jpg', 'name' => 'KARANDI', 'desc' => 'Luxurious feel', 'full' => 'Premium karandi with a rich, luxurious drape. Perfect for winter elegance and festive wear.', 'class' => ''],
                    ['link' => 'boski-section', 'image' => 'c4.jpg', 'name' => 'BOSKI', 'desc' => 'Traditional finest', 'full' => 'Fine boski fabric known for its smooth finish and traditional appeal. A staple of Pakistani menswear.', 'class' => ''],
                    ['link' => 'khaddar-section', 'image' => 'c8.webp', 'name' => 'KHADDAR', 'desc' => 'Winter warmth', 'full' => 'Warm and textured khaddar fabrics. Handwoven quality perfect for the winter season.', 'class' => ''],
                    ['link' => 'linen-section', 'image' => 'c5.jpg', 'name' => 'LINEN', 'desc' => 'Breathable premium', 'full' => 'Premium linen fabrics that offer breathable comfort with a naturally elegant texture.', 'class' => ''],
                    ['link' => 'washandwear-section', 'image' => 'c6.jpg', 'name' => 'WASH & WEAR', 'desc' => 'Low maintenance', 'full' => 'Easy-care wash and wear fabrics. Wrinkle-resistant, durable, and perfect for daily professional wear.', 'class' => ''],
                    ['link' => 'silk-section', 'image' => 'c7.webp', 'name' => 'SILK', 'desc' => 'Pure luxury', 'full' => 'Luxurious silk fabrics with a lustrous sheen. Reserved for special occasions and premium tailoring.', 'class' => '']
                ];

                foreach ($fabrics as $i => $fabric):
                ?>
                <div class="bento-card <?php echo $fabric['class']; ?> reveal reveal-delay-<?php echo min($i + 1, 8); ?>">
                    <div class="bento-flip-inner">
                        <div class="bento-front">
                            <img src="images/<?php echo $fabric['image']; ?>" alt="<?php echo $fabric['name']; ?> Fabric" loading="lazy">
                            <div class="bento-overlay">
                                <h3><?php echo $fabric['name']; ?></h3>
                                <p><?php echo $fabric['desc']; ?></p>
                            </div>
                        </div>
                        <div class="bento-back">
                            <h3><?php echo $fabric['name']; ?></h3>
                            <p><?php echo $fabric['full']; ?></p>
                            <a href="order.php#<?php echo $fabric['link']; ?>" class="flip-cta">Shop Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all-container" style="margin-top: 40px;">
                <a href="order.php" class="btn-view-all">View All Products</a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>

    <script>
    // Enhanced Carousel (opacity + Ken Burns)
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.carousel-item');
        const prevBtn = document.querySelector('.carousel-control.prev');
        const nextBtn = document.querySelector('.carousel-control.next');
        const dotsContainer = document.querySelector('.carousel-dots');
        let currentSlide = 0;
        const totalSlides = slides.length;

        // Create dots
        slides.forEach((_, i) => {
            const dot = document.createElement('button');
            dot.className = 'carousel-dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', () => goToSlide(i));
            dotsContainer.appendChild(dot);
        });

        function goToSlide(index) {
            if (index < 0) index = totalSlides - 1;
            else if (index >= totalSlides) index = 0;

            slides.forEach(s => s.classList.remove('active-slide'));
            document.querySelectorAll('.carousel-dot').forEach(d => d.classList.remove('active'));

            slides[index].classList.add('active-slide');
            document.querySelectorAll('.carousel-dot')[index].classList.add('active');
            currentSlide = index;
        }

        if (prevBtn) prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));

        setInterval(() => goToSlide(currentSlide + 1), 6000);
    });
    </script>
</body>
</html>
