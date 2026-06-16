<?php
require_once __DIR__ . '/../src/config/app.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/config/fapshi.php';
require_once __DIR__ . '/../src/helpers/mailer.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Depending on Fapshi API, they might send JSON body or form data
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (!$data || !isset($data['transId'])) {
    http_response_code(400);
    exit;
}

$transId = $data['transId'];

// Re-verify with Fapshi (never trust webhook payload blindly)
$verifyRes = callFapshi('/payment-status/' . urlencode($transId), [], 'GET');

if ($verifyRes['status'] >= 200 && $verifyRes['status'] < 300) {
    $status = $verifyRes['body']['status'] ?? 'pending';
    
    // Map Fapshi status to our enum ('SUCCESSFUL' -> 'completed', 'FAILED' -> 'failed', 'CREATED' -> 'pending')
    // We assume 'SUCCESSFUL' based on typical gateways. Adjust to Fapshi's exact string if different.
    $dbStatus = 'pending';
    if (strtoupper($status) === 'SUCCESSFUL') {
        $dbStatus = 'completed';
    } elseif (strtoupper($status) === 'FAILED') {
        $dbStatus = 'failed';
    }
    
    $pdo = getDbConnection();
    
    // Fetch current donation
    $stmt = $pdo->prepare("SELECT id, status, donor_email, donor_name, amount FROM donations WHERE reference = ?");
    $stmt->execute([$transId]);
    $donation = $stmt->fetch();
    
    if ($donation && $donation['status'] !== $dbStatus) {
        // Update status
        $updateStmt = $pdo->prepare("UPDATE donations SET status = ?, fapshi_payload = ? WHERE id = ?");
        $updateStmt->execute([$dbStatus, json_encode($verifyRes['body']), $donation['id']]);
        
        // If completed and has email, send thank you
        if ($dbStatus === 'completed' && !empty($donation['donor_email'])) {
            $nameStr = $donation['donor_name'] ? " " . $donation['donor_name'] : "";
            sendMail(
                $donation['donor_email'],
                "Thank you for your donation",
                "Dear$nameStr,\n\nThank you for your generous gift of " . $donation['amount'] . " XAF. God bless you.\n\nHope For The Poor"
            );
        }
    }
}

// Always respond 200 to acknowledge webhook receipt
http_response_code(200);
echo "OK";
