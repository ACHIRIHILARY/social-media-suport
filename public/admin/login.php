<?php
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/csrf.php';
require_once __DIR__ . '/../../src/helpers/rate_limiter.php';

initSession();

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$msg = isset($_GET['expired']) ? 'Session expired. Please log in again.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrfToken($_POST['csrf_token'] ?? '');
    checkRateLimit('login', 5, 900); // 5 attempts per 15 mins

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerate session id on privilege escalation
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['last_activity'] = time();
        
        $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="admin-login-wrapper">

<div class="form-container" style="width: 100%; max-width: 400px;">
    <h2 class="text-center" style="margin-bottom: 2rem;">Admin Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($msg): ?>
        <div class="alert alert-error"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <?= csrfField() ?>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn" style="width: 100%;">Log In</button>
    </form>
    
    <div class="text-center" style="margin-top: 1.5rem;">
        <a href="/" style="font-size: 0.9rem;">&larr; Back to Site</a>
    </div>
</div>

</body>
</html>
