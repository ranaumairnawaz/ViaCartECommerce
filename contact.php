<?php
// contact.php - Dynamic Contact Us Page

$current_page_key = 'contact';
require_once __DIR__ . '/includes/db.php';

$contact_data = get_page_data('contact');
$msg_sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg_sent = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:50px; padding-bottom:100px;">
    <div class="section-title-wrap">
        <span class="section-tag">GET IN TOUCH</span>
        <h1 class="section-title"><?= htmlspecialchars($contact_data['title'] ?: 'Contact Us') ?></h1>
        <p class="section-subtitle">Have a question or feedback? We would love to hear from you.</p>
    </div>

    <div class="layout-contact">
        <!-- Contact Info Box -->
        <div class="surface-box">
            <h3 style="font-size:1.4rem; font-weight:800; margin-bottom:20px; color:var(--secondary-color);">Our Head Office</h3>
            
            <?php if (!empty($contact_data['content'])): ?>
                <p style="color:var(--text-muted); margin-bottom:30px; line-height:1.7;">
                    <?= nl2br(htmlspecialchars($contact_data['content'])) ?>
                </p>
            <?php endif; ?>

            <div style="display:flex; flex-direction:column; gap:20px;">
                <?php if (!empty($contact_data['contact_address'])): ?>
                    <div style="display:flex; gap:16px; align-items:start;">
                        <div style="width:44px; height:44px; background:rgba(59, 130, 246, 0.1); color:var(--primary-color); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <strong style="display:block; font-size:1rem;">Location Address</strong>
                            <span style="color:var(--text-muted); font-size:0.95rem;"><?= htmlspecialchars($contact_data['contact_address']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($contact_data['contact_email'])): ?>
                    <div style="display:flex; gap:16px; align-items:start;">
                        <div style="width:44px; height:44px; background:rgba(59, 130, 246, 0.1); color:var(--primary-color); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <strong style="display:block; font-size:1rem;">Email Support</strong>
                            <span style="color:var(--text-muted); font-size:0.95rem;"><?= htmlspecialchars($contact_data['contact_email']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($contact_data['contact_phone'])): ?>
                    <div style="display:flex; gap:16px; align-items:start;">
                        <div style="width:44px; height:44px; background:rgba(59, 130, 246, 0.1); color:var(--primary-color); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <strong style="display:block; font-size:1rem;">Call Hotline</strong>
                            <span style="color:var(--text-muted); font-size:0.95rem;"><?= htmlspecialchars($contact_data['contact_phone']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($contact_data['contact_hours'])): ?>
                    <div style="display:flex; gap:16px; align-items:start;">
                        <div style="width:44px; height:44px; background:rgba(59, 130, 246, 0.1); color:var(--primary-color); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div>
                            <strong style="display:block; font-size:1rem;">Working Hours</strong>
                            <span style="color:var(--text-muted); font-size:0.95rem;"><?= htmlspecialchars($contact_data['contact_hours']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Interactive Form -->
        <div class="surface-box">
            <h3 style="font-size:1.4rem; font-weight:800; margin-bottom:20px; color:var(--secondary-color);">Send Us A Message</h3>

            <?php if ($msg_sent): ?>
                <div style="background:#dcfce7; color:#166534; padding:20px; border-radius:12px; font-weight:600; margin-bottom:20px;">
                    <i class="fa-solid fa-circle-check"></i> Thank you for reaching out! Your message has been received. Our team will get back to you shortly.
                </div>
            <?php endif; ?>

            <form method="POST" action="contact">
                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="John Doe">
                </div>

                <div class="grid-2" style="gap:16px;">
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Order Inquiry">
                    </div>
                </div>

                <div class="form-group">
                    <label>Your Message *</label>
                    <textarea name="message" class="form-control" required placeholder="How can we help you?"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; padding:14px; justify-content:center;">
                    Send Message &rarr;
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
