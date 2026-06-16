<?php
require_once __DIR__ . '/../src/config/app.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - <?= htmlspecialchars(MAIL_FROM_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body style="background-color: var(--color-bg);">

<header class="site-header">
    <div class="container header-inner">
        <a href="/" class="logo"><?= htmlspecialchars(MAIL_FROM_NAME) ?></a>
        <nav>
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="/donate.php">Donate</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="section text-center" style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
    <div class="container">
        <h1 style="font-size: 3rem; color: var(--color-accent); margin-bottom: 1rem;">Thank You!</h1>
        <p style="font-size: 1.25rem; max-width: 600px; margin: 0 auto 2rem auto;">
            Your transaction has been processed. We deeply appreciate your support. 
            May your gift bring hope and be multiplied back to you.
        </p>
        <a href="/" class="btn">Return to Home</a>
    </div>
</section>

</body>
</html>
