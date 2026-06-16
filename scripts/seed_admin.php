<?php
require_once __DIR__ . '/../src/config/database.php';

// Usage: php scripts/seed_admin.php admin password123

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

if ($argc !== 3) {
    die("Usage: php seed_admin.php <username> <password>\n");
}

$username = $argv[1];
$password = $argv[2];

$pdo = getDbConnection();

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    echo "Admin user '$username' created successfully.\n";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Integrity constraint violation
        // Update password if user exists
        $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$hash, $username]);
        echo "Admin user '$username' updated successfully.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
