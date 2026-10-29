<?php
// Practical 11: Student CRUD with MySQLi prepared statements, search, filter, pagination, CSV export, soft delete
$pageTitle = 'Students'; require_once 'includes/functions.php'; require_once 'includes/db.php';
try_remember_login($pdo); require_role('admin');
$action = $_GET['action'] ?? 'list'; $errors = []; $row = ['id'=>0,'name'=>'','email'=>'','mobile'=>'','course'=>COURSES[0],'year'=>1,'gender'=>'Male','status'=>'active'];

// ---- Export CSV
if ($action === 'export') {
    header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="students_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w'); fputcsv($out, ['ID','Name','Email','Mobile','Course','Year','Gender','Status']);
    $r = $mysqli->query("SELECT id,name,email,mobile,course,year,gender,status FROM students ORDER BY id");
    while ($x = $r->fetch_row()) fputcsv($out, $x); exit;
}

// ---- Delete (soft) / Restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['do'] ?? '', ['delete','restore'], true)) {
    if (!csrf_check()) flash('error', 'Invalid token.');
    else {
        $id = (int)$_POST['id']; $new = $_POST['do'] === 'delete' ? 'deleted' : 'active';
        $st = $mysqli->prepare('UPDATE students SET status=? WHERE id=?'); $st->bind_param('si', $new, $id); $st->execute();
        audit($pdo, "student_$new", "student id $id"); flash('success', $new === 'deleted' ? 'Student moved to deleted (soft delete).' : 'Student restored.');
    }
    redirect('students.php');
}

// ---- Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    if (!csrf_check()) $errors['form'] = 'Invalid token.';
    else {
        $id = (int)($_POST['id'] ?? 0);
        $row = ['id'=>$id,'name'=>clean($_POST['name'] ?? ''),'email'=>clean($_POST['email'] ?? ''),'mobile'=>clean($_POST['mobile'] ?? ''),'course'=>$_POST['course'] ?? '','year'=>(int)($_POST['year'] ?? 0),'gender'=>$_POST['gender'] ?? '','status'=>'active'];
        if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", $row['name'])) $errors['name'] = 'Invalid name.';
        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email.';
        if (!preg_match('/^[6-9]\d{9}$/', $row['mobile'])) $errors['mobile'] = 'Invalid mobile number.';
        if (!in_array($row['course'], COURSES, true)) $errors['course'] = 'Invalid course.';
        if ($row['year'] < 1 || $row['year'] > 4) $errors['year'] = 'Invalid year.';
        if (!in_array($row['gender'], ['Male','Female','Other'], true)) $errors['gender'] = 'Invalid gender.';
        if (!$errors) {
            $dup = $mysqli->prepare('SELECT id FROM students WHERE email=? AND id<>?'); $dup->bind_param('si', $row['email'], $id); $dup->execute();
            if ($dup->get_result()->num_rows) $errors['email'] = 'Email already used by another student.';
        }
        if (!$errors) {
            if ($id) {
                $st = $mysqli->prepare('UPDATE students SET name=?,email=?,mobile=?,course=?,year=?,gender=? WHERE id=?');
                $st->bind_param('ssssisi', $row['name'], $row['email'], $row['mobile'], $row['course'], $row['year'], $row['gender'], $id); $st->execute();
                audit($pdo, 'student_update', "student id $id"); flash('success', 'Student updated successfully.');
            } else {
                $st = $mysqli->prepare('INSERT INTO students (name,email,mobile,course,year,gender) VALUES (?,?,?,?,?,?)');
                $st->bind_param('ssssis', $row['name'], $row['email'], $row['mobile'], $row['course'], $row['year'], $row['gender']); $st->execute();
                audit($pdo, 'student_create', $row['email']); flash('success', 'Student added successfully.');
            }
            redirect('students.php');
        }
        $action = $id ? 'edit' : 'add';
    }
}
if ($action === 'edit' && !$errors) {
    $st = $mysqli->prepare('SELECT * FROM students WHERE id=?'); $id = (int)($_GET['id'] ?? 0); $st->bind_param('i', $id); $st->execute();
    $row = $st->get_result()->fetch_assoc(); if (!$row) { flash('error', 'Student not found.'); redirect('students.php'); }
}

// ---- List with search / filter / pagination
$q = clean($_GET['q'] ?? ''); $fc = $_GET['course'] ?? ''; $fs = $_GET['status'] ?? 'active'; $page = max(1, (int)($_GET['page'] ?? 1)); $per = 8;
$where = ['1=1']; $types = ''; $vals = [];
if ($q !== '') { $where[] = '(name LIKE ? OR email LIKE ? OR course LIKE ?)'; $types .= 'sss'; $l = "%$q%"; array_push($vals, $l, $l, $l); }
if ($fc !== '') { $where[] = 'course = ?'; $types .= 's'; $vals[] = $fc; }
if (in_array($fs, ['active','deleted'], true)) { $where[] = 'status = ?'; $types .= 's'; $vals[] = $fs; }
$w = implode(' AND ', $where);
$cs = $mysqli->prepare("SELECT COUNT(*) FROM students WHERE $w"); if ($types) $cs->bind_param($types, ...$vals); $cs->execute(); $total = (int)$cs->get_result()->fetch_row()[0];
$pages = max(1, (int)ceil($total / $per)); $page = min($page, $pages); $off = ($page - 1) * $per;
$ls = $mysqli->prepare("SELECT * FROM students WHERE $w ORDER BY id DESC LIMIT $per OFFSET $off"); if ($types) $ls->bind_param($types, ...$vals); $ls->execute();
$rows = $ls->get_result()->fetch_all(MYSQLI_ASSOC);
$qs = fn($p) => '?' . http_build_query(['q'=>$q,'course'=>$fc,'status'=>$fs,'page'=>$p]);
require 'includes/header.php'; ?>
<h1>Student management</h1>
<?php if (in_array($action, ['add','edit'], true)): ?>
<form method="post" class="card form-card" novalidate><?= csrf_field() ?><input type="hidden" name="do" value="save"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
  <h2><?= $row['id'] ? 'Edit student' : 'Add student' ?></h2>
  <?php if (isset($errors['form'])): ?><div class="alert error"><?= e($errors['form']) ?></div><?php endif; ?>
  <div class="form-grid">
    <?php foreach ([['name','Name','text'],['email','Email','email'],['mobile','Mobile','tel']] as [$n,$l,$t]): ?>
    <div><label for="<?= $n ?>"><?= $l ?></label><input type="<?= $t ?>" id="<?= $n ?>" name="<?= $n ?>" value="<?= e($row[$n]) ?>" class="<?= isset($errors[$n]) ? 'invalid' : '' ?>"><div class="error-msg"><?= e($errors[$n] ?? '') ?></div></div>
    <?php endforeach; ?>
    <div class="form-grid two"><div><label for="course">Course</label><select id="course" name="course"><?php foreach (COURSES as $c): ?><option <?= $row['course'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
    <div><label for="year">Year</label><select id="year" name="year"><?php for ($y=1;$y<=4;$y++): ?><option value="<?= $y ?>" <?= (int)$row['year']===$y?'selected':'' ?>>Year <?= $y ?></option><?php endfor; ?></select></div></div>
    <div><label for="gender">Gender</label><select id="gender" name="gender"><?php foreach (['Male','Female','Other'] as $g): ?><option <?= $row['gender']===$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?></select></div>
    <div><button class="btn">Save</button> <a class="btn secondary" href="students.php">Cancel</a></div>
  </div>
</form>
<?php else: ?>
<form class="toolbar" method="get">
  <div><label for="q">Search</label><input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Name, email or course"></div>
  <div><label for="course">Course</label><select id="course" name="course"><option value="">All</option><?php foreach (COURSES as $c): ?><option <?= $fc===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
  <div><label for="status">Status</label><select id="status" name="status"><option value="active" <?= $fs==='active'?'selected':'' ?>>Active</option><option value="deleted" <?= $fs==='deleted'?'selected':'' ?>>Deleted</option><option value="all" <?= $fs==='all'?'selected':'' ?>>All</option></select></div>
  <button class="btn">Filter</button> <a class="btn secondary" href="students.php?action=add">+ Add</a> <a class="btn secondary" href="students.php?action=export">⬇ CSV</a>
</form>
<p class="hint"><?= $total ?> record(s)</p>
<div class="table-wrap"><table><thead><tr><th>#</th><th>Name</th><th>Email</th><th>Mobile</th><th>Course</th><th>Yr</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= $r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['mobile']) ?></td><td><?= e($r['course']) ?></td><td><?= $r['year'] ?></td><td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
<td style="white-space:nowrap"><a class="btn small secondary" href="students.php?action=edit&id=<?= $r['id'] ?>">Edit</a>
<form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
<?php if ($r['status'] === 'active'): ?><input type="hidden" name="do" value="delete"><button class="btn small danger" data-confirm="Delete <?= e($r['name']) ?>?">Delete</button>
<?php else: ?><input type="hidden" name="do" value="restore"><button class="btn small">Restore</button><?php endif; ?></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="8" class="loading">No students found.</td></tr><?php endif; ?></tbody></table></div>
<nav class="pagination" aria-label="Pagination"><?php for ($i=1;$i<=$pages;$i++): ?><a class="<?= $i===$page?'current':'' ?>" href="<?= e($qs($i)) ?>"><?= $i ?></a><?php endfor; ?></nav>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
