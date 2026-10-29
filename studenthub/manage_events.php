<?php
// Practical 12: Event CRUD (PDO) with poster upload validation, category + status filter, audit log
$pageTitle = 'Manage Events'; require_once 'includes/functions.php'; require_once 'includes/db.php';
try_remember_login($pdo); require_role('admin');
$action = $_GET['action'] ?? 'list'; $errors = [];
$ev = ['id'=>0,'title'=>'','description'=>'','category'=>'Technical','event_date'=>date('Y-m-d', strtotime('+7 days')),'venue'=>'','status'=>'open','poster'=>null];
$categories = ['Technical','Cultural','Sports','Workshop','Seminar','General'];

function handle_upload(array &$errors): ?string {
    if (empty($_FILES['poster']) || $_FILES['poster']['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES['poster'];
    if ($f['error'] !== UPLOAD_ERR_OK) { $errors['poster'] = 'Upload failed (code ' . $f['error'] . ').'; return null; }
    if ($f['size'] > MAX_UPLOAD) { $errors['poster'] = 'File too large (max 2 MB).'; return null; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime]) || !getimagesize($f['tmp_name'])) { $errors['poster'] = 'Only JPG, PNG or WEBP images allowed.'; return null; }
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime]; // renamed: no user-controlled filename
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $name)) { $errors['poster'] = 'Could not save file.'; return null; }
    return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (csrf_check()) {
        $id = (int)$_POST['id']; $p = $pdo->prepare('SELECT poster,title FROM events WHERE id=?'); $p->execute([$id]); $r = $p->fetch();
        if ($r) { $pdo->prepare('DELETE FROM events WHERE id=?')->execute([$id]); if ($r['poster'] && is_file(UPLOAD_DIR . $r['poster'])) unlink(UPLOAD_DIR . $r['poster']); audit($pdo, 'event_delete', $r['title']); flash('success', 'Event deleted.'); }
    } else flash('error', 'Invalid token.');
    redirect('manage_events.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    if (!csrf_check()) $errors['form'] = 'Invalid token.';
    else {
        $id = (int)($_POST['id'] ?? 0);
        $ev = ['id'=>$id,'title'=>clean($_POST['title'] ?? ''),'description'=>clean($_POST['description'] ?? ''),'category'=>$_POST['category'] ?? '','event_date'=>$_POST['event_date'] ?? '','venue'=>clean($_POST['venue'] ?? ''),'status'=>$_POST['status'] ?? '','poster'=>null];
        if (mb_strlen($ev['title']) < 3 || mb_strlen($ev['title']) > 120) $errors['title'] = 'Title must be 3-120 characters.';
        if (!in_array($ev['category'], $categories, true)) $errors['category'] = 'Invalid category.';
        $d = DateTime::createFromFormat('Y-m-d', $ev['event_date']); if (!$d || $d->format('Y-m-d') !== $ev['event_date']) $errors['event_date'] = 'Enter a valid date.';
        if ($ev['venue'] === '') $errors['venue'] = 'Venue is required.';
        if (!in_array($ev['status'], ['open','closed'], true)) $errors['status'] = 'Invalid status.';
        $poster = handle_upload($errors);
        if (!$errors) {
            if ($id) {
                $old = $pdo->prepare('SELECT poster FROM events WHERE id=?'); $old->execute([$id]); $oldPoster = $old->fetchColumn();
                $pdo->prepare('UPDATE events SET title=?,description=?,category=?,event_date=?,venue=?,status=?,poster=COALESCE(?,poster) WHERE id=?')->execute([$ev['title'],$ev['description'],$ev['category'],$ev['event_date'],$ev['venue'],$ev['status'],$poster,$id]);
                if ($poster && $oldPoster && is_file(UPLOAD_DIR . $oldPoster)) unlink(UPLOAD_DIR . $oldPoster);
                audit($pdo, 'event_update', $ev['title']); flash('success', 'Event updated.');
            } else {
                $pdo->prepare('INSERT INTO events (title,description,category,event_date,venue,status,poster,created_by) VALUES (?,?,?,?,?,?,?,?)')->execute([$ev['title'],$ev['description'],$ev['category'],$ev['event_date'],$ev['venue'],$ev['status'],$poster,current_user()['id']]);
                audit($pdo, 'event_create', $ev['title']); flash('success', 'Event created.');
            }
            redirect('manage_events.php');
        }
        $action = $id ? 'edit' : 'add';
    }
}
if ($action === 'edit' && !$errors) { $s = $pdo->prepare('SELECT * FROM events WHERE id=?'); $s->execute([(int)($_GET['id'] ?? 0)]); $ev = $s->fetch(); if (!$ev) { flash('error', 'Event not found.'); redirect('manage_events.php'); } }

$fcat = $_GET['category'] ?? ''; $fst = $_GET['status'] ?? '';
$sql = 'SELECT * FROM events WHERE 1=1'; $par = [];
if ($fcat !== '') { $sql .= ' AND category=?'; $par[] = $fcat; } if (in_array($fst, ['open','closed'], true)) { $sql .= ' AND status=?'; $par[] = $fst; }
$list = $pdo->prepare($sql . ' ORDER BY event_date DESC'); $list->execute($par); $events = $list->fetchAll();
require 'includes/header.php'; ?>
<h1>Event management</h1>
<?php if (in_array($action, ['add','edit'], true)): ?>
<form method="post" enctype="multipart/form-data" class="card form-card" novalidate><?= csrf_field() ?><input type="hidden" name="do" value="save"><input type="hidden" name="id" value="<?= (int)$ev['id'] ?>">
  <h2><?= $ev['id'] ? 'Edit event' : 'Add event' ?></h2>
  <?php if (isset($errors['form'])): ?><div class="alert error"><?= e($errors['form']) ?></div><?php endif; ?>
  <div class="form-grid">
    <div><label for="title">Title</label><input id="title" name="title" value="<?= e($ev['title']) ?>"><div class="error-msg"><?= e($errors['title'] ?? '') ?></div></div>
    <div><label for="description">Description</label><textarea id="description" name="description" rows="3"><?= e($ev['description']) ?></textarea></div>
    <div class="form-grid two"><div><label for="category">Category</label><select id="category" name="category"><?php foreach ($categories as $c): ?><option <?= $ev['category']===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
    <div><label for="status">Status</label><select id="status" name="status"><option value="open" <?= $ev['status']==='open'?'selected':'' ?>>Open</option><option value="closed" <?= $ev['status']==='closed'?'selected':'' ?>>Closed</option></select></div></div>
    <div class="form-grid two"><div><label for="event_date">Date</label><input type="date" id="event_date" name="event_date" value="<?= e($ev['event_date']) ?>"><div class="error-msg"><?= e($errors['event_date'] ?? '') ?></div></div>
    <div><label for="venue">Venue</label><input id="venue" name="venue" value="<?= e($ev['venue']) ?>"><div class="error-msg"><?= e($errors['venue'] ?? '') ?></div></div></div>
    <div><label for="poster">Poster (JPG/PNG/WEBP, max 2 MB)</label><input type="file" id="poster" name="poster" accept="image/jpeg,image/png,image/webp"><div class="error-msg"><?= e($errors['poster'] ?? '') ?></div>
      <?php if (!empty($ev['poster'])): ?><p class="hint">Current: <?= e($ev['poster']) ?></p><?php endif; ?></div>
    <div><button class="btn">Save</button> <a class="btn secondary" href="manage_events.php">Cancel</a></div>
  </div>
</form>
<?php else: ?>
<form class="toolbar" method="get">
  <div><label for="category">Category</label><select id="category" name="category"><option value="">All</option><?php foreach ($categories as $c): ?><option <?= $fcat===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
  <div><label for="status">Status</label><select id="status" name="status"><option value="">All</option><option value="open" <?= $fst==='open'?'selected':'' ?>>Open</option><option value="closed" <?= $fst==='closed'?'selected':'' ?>>Closed</option></select></div>
  <button class="btn">Filter</button> <a class="btn secondary" href="manage_events.php?action=add">+ Add event</a>
</form>
<div class="grid cols-3">
<?php foreach ($events as $x): ?><article class="card">
  <?php if ($x['poster']): ?><img class="poster" src="<?= UPLOAD_URL . e($x['poster']) ?>" alt="Poster for <?= e($x['title']) ?>" loading="lazy"><?php endif; ?>
  <h3><?= e($x['title']) ?></h3><p><span class="badge <?= e($x['status']) ?>"><?= e($x['status']) ?></span> <span class="badge"><?= e($x['category']) ?></span></p><p>📅 <?= e($x['event_date']) ?><br>📍 <?= e($x['venue']) ?></p>
  <a class="btn small secondary" href="manage_events.php?action=edit&id=<?= $x['id'] ?>">Edit</a>
  <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="btn small danger" data-confirm="Delete this event?">Delete</button></form></article>
<?php endforeach; if (!$events): ?><p class="loading">No events found.</p><?php endif; ?></div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
