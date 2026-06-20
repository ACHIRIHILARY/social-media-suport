<?php
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/session.php';
require_once __DIR__ . '/../../src/helpers/csrf.php';

requireAdmin();
$pdo = getDbConnection();

// Handle Actions (Mark read, Mark prayed)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrfToken($_POST['csrf_token'] ?? '');
    
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if ($id && $action) {
        if ($action === 'mark_read') {
            $pdo->prepare("UPDATE prayer_requests SET is_read = 1 WHERE id = ?")->execute([$id]);
        } elseif ($action === 'mark_prayed') {
            $pdo->prepare("UPDATE prayer_requests SET is_read = 1, is_prayed = 1 WHERE id = ?")->execute([$id]);
        }
    }
    header('Location: prayers.php');
    exit;
}

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$where = [];
if ($statusFilter === 'unread') {
    $where[] = "is_read = 0";
} elseif ($statusFilter === 'read') {
    $where[] = "is_read = 1 AND is_prayed = 0";
} elseif ($statusFilter === 'prayed') {
    $where[] = "is_prayed = 1";
}
$whereSql = count($where) > 0 ? "WHERE " . implode(' AND ', $where) : "";

$perPage = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$totalCount = $pdo->query("SELECT COUNT(*) FROM prayer_requests $whereSql")->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$stmt = $pdo->prepare("SELECT * FROM prayer_requests $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute();
$prayers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prayers - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <style>
        .prayer-message {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 0.5rem;
            white-space: pre-wrap;
            display: none;
            border-left: 3px solid var(--color-prayer);
        }
    </style>
    <script>
        function toggleMessage(id) {
            var el = document.getElementById('msg-' + id);
            el.style.display = (el.style.display === 'block') ? 'none' : 'block';
        }
    </script>
</head>
<body>

<header class="admin-header">
    <div class="container header-inner">
        <div class="logo" style="color: #fff;">Admin Dashboard</div>
        <nav class="admin-nav">
            <a href="dashboard.php">Overview</a>
            <a href="prayers.php" style="font-weight: bold;">Prayers</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<div class="container" style="margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Prayer Requests Manager</h2>
        <a href="export.php?type=prayers&status=<?= urlencode($statusFilter) ?>" class="btn">Export CSV</a>
    </div>
    
    <div class="filters">
        <form action="" method="GET">
            <select name="status" onchange="this.form.submit()" style="padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc;">
                <option value="">All Prayers</option>
                <option value="unread" <?= $statusFilter === 'unread' ? 'selected' : '' ?>>Unread</option>
                <option value="read" <?= $statusFilter === 'read' ? 'selected' : '' ?>>Read</option>
                <option value="prayed" <?= $statusFilter === 'prayed' ? 'selected' : '' ?>>Prayed For</option>
            </select>
        </form>
    </div>
    
    <div class="data-table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($prayers as $p): ?>
                <tr>
                    <td><?= date('Y-m-d H:i', strtotime($p['created_at'])) ?></td>
                    <td>
                        <?= htmlspecialchars($p['name']) ?><br>
                        <small><?= htmlspecialchars($p['email']) ?></small>
                    </td>
                    <td>
                        <?php if($p['is_prayed']): ?>
                            <span class="badge badge-prayed">Prayed For</span>
                        <?php elseif($p['is_read']): ?>
                            <span class="badge badge-read">Read</span>
                        <?php else: ?>
                            <span class="badge badge-unread">Unread</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="toggleMessage(<?= $p['id'] ?>)">View Message</button>
                        
                        <div id="msg-<?= $p['id'] ?>" class="prayer-message">
                            <strong>Subject:</strong> <?= htmlspecialchars($p['subject'] ?: '(None)') ?><br><br>
                            <?= nl2br(htmlspecialchars($p['message'])) ?>
                            
                            <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                <?php if(!$p['is_read']): ?>
                                <form method="POST" style="display:inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="action" value="mark_read">
                                    <button type="submit" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: #6c757d;">Mark as Read</button>
                                </form>
                                <?php endif; ?>
                                <?php if(!$p['is_prayed']): ?>
                                <form method="POST" style="display:inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="action" value="mark_prayed">
                                    <button type="submit" class="btn btn-accent" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Mark as Prayed</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($prayers)): ?>
                <tr>
                    <td colspan="4" class="text-center">No prayer requests found.</td>
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
