<?php
$pageTitle = 'Audit Log';
require_once __DIR__ . '/../includes/functions.php'; require_once __DIR__ . '/../includes/db.php';
try_remember_login($pdo); require_role('admin');
$logs = $pdo->query('SELECT a.*, u.email FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 200')->fetchAll();
require __DIR__ . '/../includes/header.php'; ?>
<h1>Audit log</h1><p><a href="dashboard.php">← Back to dashboard</a></p>
<div class="table-wrap"><table><thead><tr><th>#</th><th>User</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th></tr></thead><tbody>
<?php foreach ($logs as $l): ?><tr><td><?= $l['id'] ?></td><td><?= e($l['email'] ?? 'system') ?></td><td><span class="badge"><?= e($l['action']) ?></span></td><td><?= e($l['details']) ?></td><td><?= e($l['ip_address']) ?></td><td><?= e($l['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
