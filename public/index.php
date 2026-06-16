<?php require_once __DIR__ . '/../src/config/app.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(MAIL_FROM_NAME) ?> - Donation & Prayer Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="/" class="logo"><?= htmlspecialchars(MAIL_FROM_NAME) ?></a>
        <nav>
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="/donate.php">Donate</a></li>
                <li><a href="/prayer.php">Prayer Request</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="container">
        <h1>Hope For The Poor</h1>
        <p>A lightweight, devotion-inspired platform. Join hands with us to bring hope and light to those in need.</p>
        <div class="hero-buttons">
            <a href="/donate.php" class="btn">Give Now</a>
            <a href="/prayer.php" class="btn btn-accent">Send Prayer</a>
        </div>
    </div>
</section>

<div class="cross-divider">✦</div>

<section class="section section-bg-white text-center">
    <div class="container">
        <h2>Our Mission</h2>
        <p style="max-width: 700px; margin: 0 auto;">
            We believe that every small act of love and charity ripples through eternity. 
            By giving to Hope For The Poor, you directly impact lives that need it most.
        </p>
    </div>
</section>

<div class="cross-divider">✦</div>

<section class="section text-center">
    <div class="container">
        <h2>How to Donate</h2>
        <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 2rem; flex-wrap: wrap;">
            <div style="background: #fff; padding: 2rem; border-radius: 6px; box-shadow: var(--shadow); flex: 1; min-width: 250px;">
                <h3>1. Choose Amount</h3>
                <p>Decide what you'd like to give (minimum 100 XAF).</p>
            </div>
            <div style="background: #fff; padding: 2rem; border-radius: 6px; box-shadow: var(--shadow); flex: 1; min-width: 250px;">
                <h3>2. Pay Securely</h3>
                <p>Complete your donation safely via Fapshi.</p>
            </div>
            <div style="background: #fff; padding: 2rem; border-radius: 6px; box-shadow: var(--shadow); flex: 1; min-width: 250px;">
                <h3>3. Receive Confirmation</h3>
                <p>Get a thank-you note directly to your inbox.</p>
            </div>
        </div>
    </div>
</section>

<section class="section text-center" style="background-color: var(--color-prayer); color: #fff;">
    <div class="container">
        <h2 style="color: #fff;">We Pray For You</h2>
        <p style="color: #eee; margin-bottom: 2rem;">No matter what you're facing, you are not alone. Share your burden.</p>
        <a href="/prayer.php" class="btn" style="background-color: #fff; color: var(--color-prayer);">Share Your Request</a>
    </div>
</section>

<footer class="site-footer" id="contact">
    <div class="container">
        <h3>Contact Us</h3>
        <p>Email: <?= htmlspecialchars(ADMIN_EMAIL) ?></p>
        <p>WhatsApp/SMS: +237 654 045 897 | +237 680 828 762</p>
        <div style="margin: 1.5rem 0; display: flex; justify-content: center; gap: 1rem;">
            <?php if (SOCIAL_FACEBOOK): ?><a href="<?= htmlspecialchars(SOCIAL_FACEBOOK) ?>">Facebook</a><?php endif; ?>
            <?php if (SOCIAL_INSTAGRAM): ?><a href="<?= htmlspecialchars(SOCIAL_INSTAGRAM) ?>">Instagram</a><?php endif; ?>
            <?php if (SOCIAL_TWITTER): ?><a href="<?= htmlspecialchars(SOCIAL_TWITTER) ?>">Twitter</a><?php endif; ?>
            <?php if (SOCIAL_YOUTUBE): ?><a href="<?= htmlspecialchars(SOCIAL_YOUTUBE) ?>">YouTube</a><?php endif; ?>
        </div>
        <p style="font-size: 0.9rem; margin-top: 2rem;">&copy; <?= date('Y') ?> <?= htmlspecialchars(MAIL_FROM_NAME) ?>. Powered with Faith.</p>
    </div>
</footer>

</body>
</html>
