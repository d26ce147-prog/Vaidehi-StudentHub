<?php
// Practical 14: Admin dashboard - SQL aggregation, RBAC, dynamic menu, latest registrations, chart
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/functions.php'; require_once __DIR__ . '/../includes/db.php';
try_remember_login($pdo); require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'toggle') {
    if (csrf_check()) {
        $id = (int)$_POST['id'];
        if ($id !== current_user()['id']) { $pdo->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE id=?")->execute([$id]); audit($pdo, 'user_status_toggle', "user id $id"); flash('success', 'User status updated.'); }
        else flash('error', 'You cannot deactivate yourself.');
    }
    redirect('dashboard.php');
}
$stats = $pdo->query("SELECT (SELECT COUNT(*) FROM users) users, (SELECT COUNT(*) FROM students WHERE status='active') students, (SELECT COUNT(*) FROM events) events, (SELECT COUNT(*) FROM registrations) regs")->fetch();
$latest = $pdo->query('SELECT s.name, e.title, r.registered_at FROM registrations r JOIN students s ON s.id=r.student_id JOIN events e ON e.id=r.event_id ORDER BY r.registered_at DESC, r.id DESC LIMIT 6')->fetchAll();
$byEvent = $pdo->query('SELECT e.title, COUNT(r.id) c FROM events e LEFT JOIN registrations r ON r.event_id=e.id GROUP BY e.id ORDER BY c DESC LIMIT 6')->fetchAll();
$byCourse = $pdo->query("SELECT course, COUNT(*) c FROM students WHERE status='active' GROUP BY course ORDER BY c DESC")->fetchAll();
// Practical 8/14: monthly analytics via stored procedure
$monthly = []; try { $st = $pdo->query('CALL sp_registrations_by_month()'); $monthly = $st->fetchAll(); $st->closeCursor(); } catch (PDOException $e) { error_log($e->getMessage()); }
$mmax = max(1, ...array_map(fn($r) => (int)$r['total'], $monthly ?: [['total' => 1]]));
$users = $pdo->query('SELECT id,name,email,role,status,last_login FROM users ORDER BY id')->fetchAll();
$max = max(1, ...array_map(fn($r) => (int)$r['c'], $byEvent ?: [['c' => 1]]));
require __DIR__ . '/../includes/header.php'; ?>
<div class="two-col">
  <aside><ul class="side-menu" aria-label="Admin menu">
    <li><a class="active" href="dashboard.php">📊 Overview</a></li><li><a href="../students.php">👩‍🎓 Students</a></li><li><a href="../manage_events.php">📅 Events</a></li><li><a href="audit.php">🧾 Audit log</a></li><li><a href="../api-demo.php">⚡ Live events</a></li>
  </ul></aside>
  <section>
    <h1>Admin dashboard</h1>
    <div class="grid cols-4">
      <div class="card stat"><div class="num"><?= (int)$stats['users'] ?></div>Users</div>
      <div class="card stat"><div class="num"><?= (int)$stats['students'] ?></div>Active students</div>
      <div class="card stat"><div class="num"><?= (int)$stats['events'] ?></div>Events</div>
      <div class="card stat"><div class="num"><?= (int)$stats['regs'] ?></div>Registrations</div>
    </div>
    <div class="grid cols-2" style="margin-top:1.2rem">
      <div class="card"><h2>Registrations by event</h2><div class="bar-chart" style="margin-bottom:1.6rem" role="img" aria-label="Bar chart of registrations by event">
        <?php foreach ($byEvent as $b): ?><div class="bar" style="height:<?= max(3, round($b['c'] / $max * 100)) ?>%" title="<?= e($b['title']) ?>"><span><?= (int)$b['c'] ?></span><small><?= e(mb_substr($b['title'], 0, 8)) ?></small></div><?php endforeach; ?></div></div>
      <div class="card"><h2>Students by course</h2><ul><?php foreach ($byCourse as $c): ?><li><?= e($c['course']) ?>: <strong><?= (int)$c['c'] ?></strong></li><?php endforeach; ?></ul></div>
    </div>
    <div class="card" style="margin-top:1.2rem"><h2>Registrations by month (last 6 months)</h2>
      <?php if (!$monthly): ?><p class="hint">No data (import the updated SQL file to create the stored procedure).</p><?php else: ?>
      <div class="bar-chart" style="margin-bottom:1.6rem" role="img" aria-label="Bar chart of registrations per month">
        <?php foreach ($monthly as $m): ?><div class="bar" style="height:<?= max(3, round($m['total'] / $mmax * 100)) ?>%" title="<?= e($m['month']) ?>"><span><?= (int)$m['total'] ?></span><small><?= e($m['month']) ?></small></div><?php endforeach; ?></div><?php endif; ?></div>
    <h2 style="margin-top:1.5rem">Latest registrations</h2>
    <div class="table-wrap"><table><thead><tr><th>Student</th><th>Event</th><th>When</th></tr></thead><tbody>
      <?php foreach ($latest as $l): ?><tr><td><?= e($l['name']) ?></td><td><?= e($l['title']) ?></td><td><?= e($l['registered_at']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <h2 style="margin-top:1.5rem">Users</h2>
    <div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last login</th><th></th></tr></thead><tbody>
      <?php foreach ($users as $u): ?><tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><span class="badge <?= e($u['role']) ?>"><?= e($u['role']) ?></span></td><td><span class="badge <?= e($u['status']) ?>"><?= e($u['status']) ?></span></td><td><?= e($u['last_login'] ?? '—') ?></td>
      <td><form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="toggle"><input type="hidden" name="id" value="<?= $u['id'] ?>"><button class="btn small secondary" <?= $u['id'] == current_user()['id'] ? 'disabled' : '' ?>>Toggle</button></form></td></tr><?php endforeach; ?></tbody></table></div>
  </section>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
