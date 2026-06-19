<?php
require_once __DIR__ . '/../src/config/app.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/fapshi.php';
require_once __DIR__ . '/../src/helpers/csrf.php';
require_once __DIR__ . '/../src/helpers/sanitize.php';
require_once __DIR__ . '/../src/helpers/rate_limiter.php';

    if (!$error) {
        // Prepare Fapshi payload and initiate payment first (safer: avoid orphan DB rows)
        $redirectUrl = SITE_URL . '/thank-you.php';
        $payload = [
            'amount' => $amount,
            'email' => $email ?? 'anonymous@example.com',
            'redirectUrl' => $redirectUrl,
            'message' => 'Donation to Hope For The Poor'
        ];

        $fapshiRes = callFapshi('/initiate-pay', $payload);

        if (!($fapshiRes['status'] >= 200 && $fapshiRes['status'] < 300 && isset($fapshiRes['body']['link']) && isset($fapshiRes['body']['transId']))) {
            error_log('Fapshi initiate-pay failed: ' . json_encode($fapshiRes));
            $error = "Payment gateway error. Please try again later.";
        } else {
            // Fapshi returned a transaction id; persist the donation with that reference
            $transId = $fapshiRes['body']['transId'];
            try {
                $pdo = getDbConnection();
                $stmt = $pdo->prepare(
                    "INSERT INTO donations (reference, amount, donor_name, donor_email, donor_phone, ip_address) VALUES (?, ?, ?, ?, ?, INET6_ATON(?))"
                );
                $stmt->execute([$transId, $amount, $name, $email, $phone, getIpAddress()]);
                // redirect user to Fapshi payment link
                header('Location: ' . $fapshiRes['body']['link']);
                exit;
            } catch (Exception $e) {
                // DB failed after Fapshi succeeded: try to expire the payment and log
                error_log('Donate DB insert failed after Fapshi success: ' . $e->getMessage());
                // attempt to expire the payment to avoid dangling transactions
                try {
                    callFapshi('/expire-pay', ['transId' => $transId]);
                } catch (Exception $ex) {
                    error_log('Failed to call expire-pay: ' . $ex->getMessage());
                }
                $error = "A server error occurred. Payment was not completed. Please contact support.";
            }
        }
    }
    if (!$error) {
        // Call Fapshi
        $redirectUrl = SITE_URL . '/thank-you.php';
        $payload = [
            'amount' => $amount,
            'email' => $email ?? 'anonymous@example.com',
            'redirectUrl' => $redirectUrl,
            'message' => 'Donation to Hope For The Poor'
        ];

        $fapshiRes = callFapshi('/initiate-pay', $payload);
        
        if ($fapshiRes['status'] >= 200 && $fapshiRes['status'] < 300 && isset($fapshiRes['body']['link']) && isset($fapshiRes['body']['transId'])) {
            // Update reference
            $stmt = $pdo->prepare("UPDATE donations SET reference = ? WHERE id = ?");
            $stmt->execute([$fapshiRes['body']['transId'], $donationId]);
            
            header('Location: ' . $fapshiRes['body']['link']);
            exit;
        } else {
            error_log('Fapshi initiate-pay failed: ' . json_encode($fapshiRes));
            if (!empty($fapshiRes['curl_error'])) {
                error_log('Fapshi curl error: ' . $fapshiRes['curl_error']);
            }
            $stmt = $pdo->prepare("UPDATE donations SET status = 'failed', fapshi_payload = ? WHERE id = ?");
            $stmt->execute([json_encode($fapshiRes['body']), $donationId]);
            $error = "Payment gateway error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - <?= htmlspecialchars(MAIL_FROM_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <script>
        function toggleAnonymous() {
            var isAnon = document.getElementById('anonymous').checked;
            document.getElementById('name').disabled = isAnon;
            document.getElementById('email').disabled = isAnon;
            if(isAnon) {
                document.getElementById('name').value = '';
                document.getElementById('email').value = '';
            }
        }
    </script>
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

<section class="section">
    <div class="container">
        <h2 class="text-center">Make a Donation</h2>
        <div class="cross-divider">✦</div>
        
        <div class="form-container">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="amount">Amount (XAF) *</label>
                    <input type="number" id="amount" name="amount" class="form-control" min="100" step="1" required placeholder="Min 100">
                </div>

                <div class="form-check">
                    <input type="checkbox" id="anonymous" name="anonymous" onchange="toggleAnonymous()">
                    <label for="anonymous" style="margin:0; font-weight: normal;">Donate Anonymously</label>
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Your full name">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="For receipt">
                </div>

                <div class="form-group">
                    <label for="phone">Phone (Optional)</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="For mobile money prompt">
                </div>

                <button type="submit" class="btn btn-accent" style="width: 100%;">Give Now</button>
            </form>
        </div>
    </div>
</section>

</body>
</html>
