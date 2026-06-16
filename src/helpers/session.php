<?php

function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // HTTPS only if on HTTPS
            'cookie_httponly' => true,    // No JS access
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => true,
        ]);
    }
}

function requireAdmin() {
    initSession();
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
    
    // Optional: session expiration after 30 minutes of inactivity
    $timeout = 1800;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        header('Location: /admin/login.php?expired=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}
