<?php
require_once __DIR__ . '/../src/config/database.php';

$pdo = getDbConnection();

try {
    $pdo->exec("DROP TABLE IF EXISTS donations");
    echo "Dropped old donations table.\n";
    
    // Also drop prayer_requests and rate_limit to be safe
    $pdo->exec("DROP TABLE IF EXISTS prayer_requests");
    echo "Dropped old prayer_requests table.\n";
    
    $pdo->exec("DROP TABLE IF EXISTS rate_limit");
    echo "Dropped old rate_limit table.\n";
    
} catch (PDOException $e) {
    echo "Error dropping tables: " . $e->getMessage() . "\n";
}
