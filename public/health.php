<?php
/**
 * Health check endpoint - validates environment, DB, and Fapshi connectivity
 * Deploy to production, then hit https://yourdomain.com/health.php to verify setup
 * No credentials are exposed in output.
 */

require_once __DIR__ . '/../src/config/env.php';

$checks = [
    'env_loaded' => true,
    'db' => 'pending',
    'fapshi' => 'pending',
    'errors' => []
];

// 1. Verify required env vars are loaded
$requiredEnv = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'FAPSHI_USER', 'FAPSHI_KEY', 'SITE_URL', 'ADMIN_EMAIL'];
foreach ($requiredEnv as $var) {
    if (empty($_ENV[$var])) {
        $checks['env_loaded'] = false;
        $checks['errors'][] = "Missing environment variable: $var";
    }
}

// 2. Test DB connection
if ($checks['env_loaded']) {
    try {
        require_once __DIR__ . '/../src/config/database.php';
        $pdo = getDbConnection();
        $result = $pdo->query('SELECT 1');
        $checks['db'] = $result ? 'ok' : 'failed';
    } catch (Exception $e) {
        $checks['db'] = 'failed';
        $checks['errors'][] = 'DB error: ' . (strpos($e->getMessage(), 'password') !== false ? 'Access denied (wrong user/password/host)' : $e->getMessage());
    }
}

// 3. Test Fapshi connectivity (don't actually initiate a payment; just test the endpoint)
if ($checks['env_loaded']) {
    try {
        require_once __DIR__ . '/../src/config/fapshi.php';
        
        // Make a lightweight test call (use a GET endpoint like /balance which is read-only)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, FAPSHI_BASE_URL . '/balance');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apiuser: ' . FAPSHI_USER,
            'apikey: ' . FAPSHI_KEY,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            $checks['fapshi'] = 'failed';
            $checks['errors'][] = 'Fapshi curl error: ' . $curlError;
        } elseif ($httpCode >= 400) {
            $checks['fapshi'] = 'auth_failed';
            $checks['errors'][] = 'Fapshi auth failed (check FAPSHI_USER/FAPSHI_KEY) - HTTP ' . $httpCode;
        } else {
            $checks['fapshi'] = 'ok';
        }
    } catch (Exception $e) {
        $checks['fapshi'] = 'failed';
        $checks['errors'][] = 'Fapshi error: ' . $e->getMessage();
    }
}

// Determine overall status
$status = ($checks['env_loaded'] && $checks['db'] === 'ok' && $checks['fapshi'] === 'ok') ? 'healthy' : 'unhealthy';

// Return JSON for programmatic use
header('Content-Type: application/json');
http_response_code($status === 'healthy' ? 200 : 503);
echo json_encode([
    'status' => $status,
    'timestamp' => date('c'),
    'checks' => $checks
], JSON_PRETTY_PRINT);
?>
