<?php
// Student dashboard: own registrations, event registration
$pageTitle = 'Dashboard'; require_once 'includes/functions.php'; require_once 'includes/db.php';
try_remember_login($pdo); require_login();
if (is_admin()) redirect('admin/dashboard.php');
$uid = current_user()['id'];
$st = $pdo->prepare('SELECT id FROM students WHERE user_id=?'); $st->execute([$uid]); $sid = $st->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sid) {
    if (!csrf_check()) flash('error', 'Invalid token.');
    else {
        $eid = (int)($_POST['event_id'] ?? 0);
        if (($_POST['action'] ?? '') === 'register') {
            $ev = $pdo->prepare("SELECT id FROM events WHERE id=? AND status='open'"); $ev->execute([$eid]);
            if (!$ev->fetch()) flash('error', 'That event is closed or does not exist.');
            else { try { $pdo->prepare('INSERT INTO registrations (student_id,event_id) VALUES (?,?)')->execute([$sid, $eid]); flash('success', 'Registered for the event!'); } catch (PDOException $e) { flash('error', 'You are already registered for this event.'); } }
        } elseif (($_POST['action'] ?? '') === 'cancel') {
            $pdo->prepare('DELETE FROM registrations WHERE student_id=? AND event_id=?')->execute([$sid, $eid]); flash('success', 'Registration cancelled.');
        }
    }
    redirect('dashboard.php');
}
$my = []; if ($sid) { $q = $pdo->prepare('SELECT e.*, r.registered_at FROM registrations r JOIN events e ON e.id=r.event_id WHERE r.student_id=? ORDER BY e.event_date'); $q->execute([$sid]); $my = $q->fetchAll(); }
$open = $pdo->query("SELECT * FROM events WHERE status='open' AND event_date >= CURDATE() ORDER BY event_date LIMIT 12")->fetchAll();
$myIds = array_column($my, 'id');
require 'includes/header.php'; ?>
<h1>Welcome, <?= e(current_user()['name']) ?> 👋</h1>
<div class="grid cols-3" style="margin-bottom:1.5rem">
  <div class="card stat"><div class="num"><?= count($my) ?></div>My registrations</div>
  <div class="card stat"><div class="num"><?= count($open) ?></div>Open events</div>
  <div class="card stat"><div class="num"><?= e(date('d M')) ?></div>Today</div>
</div>
<h2>My registered events</h2>
<?php if (!$my): ?><p class="hint">You haven't registered for any events yet.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Event</th><th>Date</th><th>Venue</th><th></th></tr></thead><tbody>
<?php foreach ($my as $r): ?><tr><td><?= e($r['title']) ?></td><td><?= e($r['event_date']) ?></td><td><?= e($r['venue']) ?></td>
<td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="event_id" value="<?= $r['id'] ?>"><button class="btn danger small" data-confirm="Cancel this registration?">Cancel</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<h2 style="margin-top:2rem">Open events</h2>
<div class="grid cols-3">
<?php foreach ($open as $ev): ?><article class="card">
  <?php if ($ev['poster']): ?><img class="poster" src="<?= UPLOAD_URL . e($ev['poster']) ?>" alt="Poster for <?= e($ev['title']) ?>"><?php endif; ?>
  <h3><?= e($ev['title']) ?></h3><p>📅 <?= e($ev['event_date']) ?> · 📍 <?= e($ev['venue']) ?></p><span class="badge"><?= e($ev['category']) ?></span>
  <?php if (!in_array($ev['id'], $myIds)): ?><form method="post" style="margin-top:.8rem"><?= csrf_field() ?><input type="hidden" name="action" value="register"><input type="hidden" name="event_id" value="<?= $ev['id'] ?>"><button class="btn small">Register</button></form>
  <?php else: ?><p class="badge active" style="margin-top:.8rem">✔ Registered</p><?php endif; ?></article>
<?php endforeach; ?></div>
<?php require 'includes/footer.php'; ?>
