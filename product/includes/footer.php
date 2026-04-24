<?php
/**
 * Common Footer Component
 * Include this at the bottom of each page
 */
?>
<footer>
    <div class="container">
        <!-- Newsletter Section -->
        <div class="footer-newsletter reveal">
            <h3>Stay Updated</h3>
            <p>Subscribe for exclusive offers, new arrivals, and seasonal collections</p>
            <form class="newsletter-form" onsubmit="event.preventDefault(); this.querySelector('button').textContent='Subscribed!'; this.querySelector('input').value='';">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>

        <div class="footer-content">
            <div class="footer-section about reveal">
                <div class="footer-logo">
                    <a href="index.php" class="logo-button">
                        <h3 class="logo-text">Stitch House</h3>
                    </a>
                </div>
                <p>Your premium destination for high-quality fabrics and exceptional tailoring service in Bahawalpur.</p>
                <div class="social-icons">
                    <a href="#" class="facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="pinterest"><i class="fab fa-pinterest"></i></a>
                    <a href="#" class="youtube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section links reveal reveal-delay-1">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="order.php">Shop Now</a></li>
                    <li><a href="myorder.php">My Order</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section customer-service reveal reveal-delay-2">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="shipping-policy.php">Shipping Policy</a></li>
                    <li><a href="returns.php">Returns &amp; Exchange</a></li>
                    <li><a href="size-guide.php">Size Guide</a></li>
                    <li><a href="faqs.php">FAQs</a></li>
                </ul>
            </div>
            <div class="footer-section contact reveal reveal-delay-3">
                <h3>Contact Info</h3>
                <p><i class="fas fa-map-marker-alt"></i> IUB Baghdad-ul-Jadeed Campus, Hasilpur Road, Bahawalpur 63100, Pakistan</p>
                <p><i class="fas fa-phone"></i> 0304-2292813</p>
                <p><i class="fas fa-envelope"></i> info@stitchhouse.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Stitch House. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<div class="back-to-top">
    <i class="fas fa-arrow-up"></i>
</div>

<?php
// Floating cart quick-access (mirrors header cart count, opens drawer)
$floatCartCount = function_exists('getCartCount') ? getCartCount() : 0;
?>
<button type="button" class="cart-float" id="cart-float-btn" aria-label="Open cart" title="Your Cart">
    <i class="fas fa-shopping-bag"></i>
    <span class="cart-float-count<?php echo $floatCartCount === 0 ? ' is-empty' : ''; ?>"><?php echo $floatCartCount; ?></span>
</button>

