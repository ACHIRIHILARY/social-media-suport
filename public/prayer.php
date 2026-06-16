<?php
require_once __DIR__ . '/../src/config/app.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/csrf.php';
require_once __DIR__ . '/../src/helpers/sanitize.php';
require_once __DIR__ . '/../src/helpers/rate_limiter.php';
require_once __DIR__ . '/../src/helpers/mailer.php';

initSession();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrfToken($_POST['csrf_token'] ?? '');
    checkRateLimit('prayer', 3, 3600); // 3 per hour

    $name = sanitizeString($_POST['name'] ?? null);
    $email = sanitizeEmail($_POST['email'] ?? null);
    $subject = sanitizeString($_POST['subject'] ?? null);
    $message = sanitizeString($_POST['message'] ?? null);

    if (!$name || !$email || !$message) {
        $error = "Name, email, and message are required.";
    } elseif (mb_strlen($message) > 1000) {
        $error = "Message is too long (max 1000 characters).";
    }

    if (!$error) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO prayer_requests (name, email, subject, message, ip_address)
            VALUES (?, ?, ?, ?, INET6_ATON(?))
        ");
        $stmt->execute([$name, $email, $subject, $message, getIpAddress()]);

        // Send confirmation email to user
        sendMail($email, "We received your prayer request", "Dear $name,\n\nWe have received your prayer request and our team is praying for you.\n\nBlessings,\nHope For The Poor");
        
        // Alert admin
        sendMail(ADMIN_EMAIL, "New Prayer Request", "A new prayer request was submitted by $name ($email).\nSubject: $subject\n\nPlease log in to the dashboard to read it.");

        // PRG pattern
        header('Location: /prayer.php?success=1');
        exit;
    }
}

if (isset($_GET['success'])) {
    $success = "Your prayer request has been sent successfully. We will pray for you.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prayer Request - <?= htmlspecialchars(MAIL_FROM_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
</head>
<body style="background-color: var(--color-bg);">

<header class="site-header">
    <div class="container header-inner">
        <a href="/" class="logo"><?= htmlspecialchars(MAIL_FROM_NAME) ?></a>
        <nav>
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="/prayer.php">Prayer Request</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="section">
    <div class="container">
        <h2 class="text-center" style="color: var(--color-prayer);">Share Your Prayer Request</h2>
        <div class="cross-divider">✦</div>
        
        <div class="form-container">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control">
                </div>

                <div class="form-group">
                    <label for="message">Message * (max 1000 chars)</label>
                    <textarea id="message" name="message" class="form-control" required maxlength="1000"></textarea>
                </div>

                <button type="submit" class="btn" style="width: 100%; background-color: var(--color-prayer);">Send My Prayer</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>
