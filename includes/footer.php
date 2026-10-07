<?php
// includes/footer.php - Dynamic Public Footer & Floating WhatsApp Widget

require_once __DIR__ . '/db.php';
$settings = get_all_settings();
$contact_data = get_page_data('contact');
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'ViaCart';
$tagline = !empty($settings['tagline']) ? $settings['tagline'] : 'Luxury Bags & Fine Fashion Accessories';

$db = get_db();
$footer_categories = $db->query("SELECT * FROM categories ORDER BY name ASC LIMIT 4")->fetchAll();

$raw_wa = !empty($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '12125550199';
$wa_clean_number = preg_replace('/[^0-9]/', '', $raw_wa);
?>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div class="footer-brand">
                    <h3><?= htmlspecialchars($site_name) ?></h3>
                    <p><?= htmlspecialchars($tagline) ?></p>
                    <p style="margin-top: 14px; font-size: 0.9rem; line-height: 1.6;">
                        Crafting timeless fashion statements with exceptional craftsmanship and modern aesthetics.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index">Home</a></li>
                        <li><a href="shop">Shop All Products</a></li>
                        <li><a href="deals"><i class="fa-solid fa-fire"></i> Deals & Offers</a></li>
                        <li><a href="track-order">Track Order</a></li>
                        <li><a href="contact">Contact Us</a></li>
                        <li><a href="privacy-policy">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="footer-col">
                    <h4>Categories</h4>
                    <ul>
                        <?php foreach ($footer_categories as $cat): ?>
                            <li><a href="shop?category=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="shop">View All Categories</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-col">
                    <h4>Customer Care</h4>
                    <ul>
                        <?php if (!empty($contact_data['contact_email'])): ?>
                            <li><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($contact_data['contact_email']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($contact_data['contact_phone'])): ?>
                            <li><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($contact_data['contact_phone']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($contact_data['contact_address'])): ?>
                            <li><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($contact_data['contact_address']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($contact_data['contact_hours'])): ?>
                            <li><i class="fa-solid fa-clock"></i> <?= htmlspecialchars($contact_data['contact_hours']) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <strong><?= htmlspecialchars($site_name) ?></strong>. All Rights Reserved. Built with Core PHP & MySQL.</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Chat Button -->
    <?php if (!empty($wa_clean_number)): ?>
        <a href="https://wa.me/<?= htmlspecialchars($wa_clean_number) ?>?text=<?= urlencode('*WELCOME TO VIACART* What would you like to order?') ?>" target="_blank" class="whatsapp-float" title="Chat with us on WhatsApp">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="#ffffff">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
            <span class="whatsapp-tooltip">Chat with us</span>
        </a>
    <?php endif; ?>

    <!-- Move to Top Button -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" id="moveToTopBtn" title="Go to top" style="display: none; position: fixed; bottom: 20px; left: 20px; z-index: 99; border: none; outline: none; background-color: var(--primary-color); color: white; cursor: pointer; width: 50px; height: 50px; border-radius: 50%; font-size: 18px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); transition: background-color 0.3s, transform 0.3s;">
        <i class="fa-solid fa-arrow-up"></i>
    </button>
    <script>
        let mybutton = document.getElementById("moveToTopBtn");
        window.addEventListener('scroll', function() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                mybutton.style.display = "block";
            } else {
                mybutton.style.display = "none";
            }
        });
    </script>

    <!-- Core App JS -->
    <script src="assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/app.js'); ?>"></script>
</body>
</html>
