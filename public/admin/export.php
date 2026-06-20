<?php
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/session.php';

requireAdmin();
$pdo = getDbConnection();

$type = $_GET['type'] ?? '';

if ($type === 'prayers') {
    $statusFilter = $_GET['status'] ?? '';
    $where = [];
    if ($statusFilter === 'unread') { $where[] = "is_read = 0"; } 
    elseif ($statusFilter === 'read') { $where[] = "is_read = 1 AND is_prayed = 0"; } 
    elseif ($statusFilter === 'prayed') { $where[] = "is_prayed = 1"; }
    $whereSql = count($where) > 0 ? "WHERE " . implode(' AND ', $where) : "";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=prayers_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Subject', 'Message', 'Is Read', 'Is Prayed', 'IP Address', 'Date']);
    
    $stmt = $pdo->query("SELECT id, name, email, subject, message, is_read, is_prayed, INET6_NTOA(ip_address) as ip, created_at FROM prayer_requests $whereSql ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
} else {
    die("Invalid export type.");
}
