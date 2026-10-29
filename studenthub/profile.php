<?php
$pageTitle = 'Profile'; require_once 'includes/functions.php'; require_once 'includes/db.php';
try_remember_login($pdo); require_login();
$uid = current_user()['id'];
$st = $pdo->prepare('SELECT u.*, s.mobile, s.course, s.year, s.gender, s.id sid FROM users u LEFT JOIN students s ON s.user_id=u.id WHERE u.id=?'); $st->execute([$uid]); $p = $st->fetch();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) $errors['form'] = 'Invalid token.';
    else {
        $name = clean($_POST['name'] ?? ''); $mobile = clean($_POST['mobile'] ?? ''); $course = $_POST['course'] ?? ''; $year = (int)($_POST['year'] ?? 0);
        if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", $name)) $errors['name'] = 'Invalid name.';
        if ($p['sid']) { if (!preg_match('/^[6-9]\d{9}$/', $mobile)) $errors['mobile'] = 'Invalid mobile.'; if (!in_array($course, COURSES, true)) $errors['course'] = 'Invalid course.'; if ($year < 1 || $year > 4) $errors['year'] = 'Invalid year.'; }
        if (!$errors) {
            $pdo->prepare('UPDATE users SET name=? WHERE id=?')->execute([$name, $uid]);
            if ($p['sid']) $pdo->prepare('UPDATE students SET name=?, mobile=?, course=?, year=? WHERE id=?')->execute([$name, $mobile, $course, $year, $p['sid']]);
            $_SESSION['user']['name'] = $name; audit($pdo, 'profile_update', $p['email']);
            flash('success', 'Profile updated.'); redirect('profile.php');
        }
    }
}
require 'includes/header.php'; ?>
<h1>My profile</h1>
<form method="post" class="card form-card" novalidate><?= csrf_field() ?>
  <?php if (isset($errors['form'])): ?><div class="alert error"><?= e($errors['form']) ?></div><?php endif; ?>
  <div class="form-grid">
    <div><label for="email">Email (cannot be changed)</label><input id="email" value="<?= e($p['email']) ?>" disabled></div>
    <p>Role: <span class="badge <?= e($p['role']) ?>"><?= e($p['role']) ?></span> · Last login: <?= e($p['last_login'] ?? '—') ?></p>
    <div><label for="name">Name</label><input id="name" name="name" value="<?= e($p['name']) ?>" required><div class="error-msg"><?= e($errors['name'] ?? '') ?></div></div>
    <?php if ($p['sid']): ?>
    <div><label for="mobile">Mobile</label><input id="mobile" name="mobile" value="<?= e($p['mobile']) ?>" maxlength="10"><div class="error-msg"><?= e($errors['mobile'] ?? '') ?></div></div>
    <div class="form-grid two"><div><label for="course">Course</label><select id="course" name="course"><?php foreach (COURSES as $c): ?><option <?= $p['course'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
    <div><label for="year">Year</label><select id="year" name="year"><?php for ($y = 1; $y <= 4; $y++): ?><option value="<?= $y ?>" <?= $p['year'] == $y ? 'selected' : '' ?>>Year <?= $y ?></option><?php endfor; ?></select></div></div>
    <?php endif; ?>
    <button class="btn">Save changes</button>
  </div>
</form>
<?php require 'includes/footer.php'; ?>
