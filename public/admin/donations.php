<?php
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/sanitize.php';

requireAdmin();
$pdo = getDbConnection();

$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['pending','completed','failed','cancelled']) ? $_GET['status'] : '';

$where = [];
$params = [];
if ($statusFilter) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

$whereSql = count($where) > 0 ? "WHERE " . implode(' AND ', $where) : "";

// Pagination
$perPage = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$totalCount = $pdo->prepare("SELECT COUNT(*) FROM donations $whereSql");
$totalCount->execute($params);
$total = $totalCount->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT * FROM donations $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$donations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>

<header class="admin-header">
    <div class="container header-inner">
        <div class="logo" style="color: #fff;">Admin Dashboard</div>
        <nav class="admin-nav">
            <a href="dashboard.php">Overview</a>
            <a href="donations.php" style="font-weight: bold;">Donations</a>
            <a href="prayers.php">Prayers</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<div class="container" style="margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Donations Manager</h2>
        <a href="export.php?type=donations&status=<?= urlencode($statusFilter) ?>" class="btn">Export CSV</a>
    </div>
    
    <div class="filters">
        <form action="" method="GET">
            <select name="status" onchange="this.form.submit()" style="padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc;">
                <option value="">All Statuses</option>
                <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </form>
    </div>
    
    <div class="data-table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Donor Name</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($donations as $d): ?>
                <tr>
                    <td><?= date('Y-m-d H:i', strtotime($d['created_at'])) ?></td>
                    <td>
                        <?= htmlspecialchars($d['donor_name'] ?: 'Anonymous') ?>
                        <?php if($d['donor_email']): ?><br><small><?= htmlspecialchars($d['donor_email']) ?></small><?php endif; ?>
                    </td>
                    <td><?= number_format($d['amount']) ?> XAF</td>
                    <td><span class="badge badge-<?= $d['status'] ?>"><?= ucfirst($d['status']) ?></span></td>
                    <td style="font-family: monospace; font-size: 0.9em;"><?= htmlspecialchars($d['reference']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($donations)): ?>
                <tr>
                    <td colspan="5" class="text-center">No donations found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($totalPages > 1): ?>
    <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>" class="btn <?= $i === $page ? 'btn-accent' : '' ?>" style="padding: 0.5rem 1rem;"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
