<?php
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/session.php';

requireAdmin();
$pdo = getDbConnection();

$unreadPrayers = $pdo->query("SELECT COUNT(*) FROM prayer_requests WHERE is_read=0")->fetchColumn();

// Recent 5 unread prayers
$recentPrayers = $pdo->query("SELECT * FROM prayer_requests WHERE is_read=0 ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>

<header class="admin-header">
    <div class="container header-inner">
        <div class="logo" style="color: #fff;">Admin Dashboard</div>
        <nav class="admin-nav">
            <a href="dashboard.php" style="font-weight: bold;">Overview</a>
            <a href="prayers.php">Prayers</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<div class="container" style="margin-top: 2rem;">
    <h2>Overview</h2>
    
    <div class="dashboard-grid">
        <div class="widget">
            <div class="widget-value" <?= $unreadPrayers > 0 ? 'style="color: var(--color-accent);"' : '' ?>><?= $unreadPrayers ?></div>
            <div class="widget-label">Unread Prayers</div>
        </div>
    </div>
    
    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
        
        <div style="flex: 1; min-width: 300px;">
            <h3>Recent Prayer Requests</h3>
            <div class="data-table-container">
                <?php if (count($recentPrayers) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Subject</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentPrayers as $p): ?>
                        <tr>
                            <td><?= date('Y-m-d', strtotime($p['created_at'])) ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['subject'] ?: '(No subject)') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No unread prayer requests.</p>
                <?php endif; ?>
            </div>
            <a href="prayers.php" class="btn" style="font-size: 0.9rem;">View All Prayers</a>
        </div>
    </div>
</div>

</body>
</html>
