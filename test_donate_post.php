<?php
require_once __DIR__ . '/src/config/app.php';
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/config/fapshi.php';
require_once __DIR__ . '/src/helpers/csrf.php';
require_once __DIR__ . '/src/helpers/sanitize.php';
require_once __DIR__ . '/src/helpers/rate_limiter.php';

// Force display of errors for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Simulate a POST to donate.php by directly calling the same logic
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'amount' => '100',
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '1234567890',
    'csrf_token' => getCsrfToken(),
];

ob_start();
require __DIR__ . '/public/donate.php';
$content = ob_get_clean();
file_put_contents(__DIR__ . '/test_donate_post_output.html', $content);
echo "Done. Output written to test_donate_post_output.html\n";
