<?php
// Practical 5 + 9: registration form, frontend+backend validation, duplicate check, password_hash, MySQLi prepared statements
$pageTitle = 'Register'; $extraScripts = ['assets/js/validation.js'];
require_once 'includes/functions.php'; require_once 'includes/db.php';
$errors = []; $old = array_fill_keys(['name','email','mobile','course','year','gender'], '');
if (is_logged_in()) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { $errors['form'] = 'Invalid form token. Reload the page and try again.'; }
    else {
        $d = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
        foreach ($old as $k => $_) $old[$k] = clean($d[$k] ?? '');
        $errors = validate_registration($d);
        if (!$errors) {
            // duplicate email check
            $st = $mysqli->prepare('SELECT id FROM users WHERE email = ?'); $st->bind_param('s', $d['email']); $st->execute();
            if ($st->get_result()->num_rows > 0) $errors['email'] = 'This email is already registered.';
            $st->close();
        }
        if (!$errors) {
            $hash = password_hash($d['password'], PASSWORD_DEFAULT);
            $mysqli->begin_transaction();
            try {
                $role = 'student';
                $u = $mysqli->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
                $u->bind_param('ssss', $old['name'], $d['email'], $hash, $role); $u->execute(); $uid = $mysqli->insert_id; $u->close();
                $yr = (int)$d['year'];
                $s = $mysqli->prepare('INSERT INTO students (user_id,name,email,mobile,course,year,gender) VALUES (?,?,?,?,?,?,?)');
                $s->bind_param('issssis', $uid, $old['name'], $d['email'], $d['mobile'], $d['course'], $yr, $d['gender']); $s->execute(); $s->close();
                $a = $mysqli->prepare("INSERT INTO audit_logs (user_id,action,details,ip_address) VALUES (?,'register',?,?)");
                $det = 'New registration: ' . $d['email']; $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $a->bind_param('iss', $uid, $det, $ip); $a->execute(); $a->close();
                $mysqli->commit();
                flash('success', 'Registration successful! You can now log in.'); redirect('login.php');
            } catch (mysqli_sql_exception $ex) {
                $mysqli->rollback();
                $errors['form'] = ($ex->getCode() === 1062) ? 'This email is already registered.' : 'Registration failed. Please try again.';
                error_log($ex->getMessage());
            }
        }
    }
}
require 'includes/header.php';
function fe($errors, $k) { return '<div class="error-msg" id="err-' . $k . '" aria-live="polite">' . e($errors[$k] ?? '') . '</div>'; }
?>
<h1>Create your account</h1>
<form id="registerForm" method="post" class="card form-card" novalidate>
  <?= csrf_field() ?>
  <?php if (isset($errors['form'])): ?><div class="alert error"><?= e($errors['form']) ?></div><?php endif; ?>
  <div class="form-grid">
    <div><label for="name">Full name</label><input type="text" id="name" name="name" value="<?= e($old['name']) ?>" autocomplete="name" required><?= fe($errors,'name') ?></div>
    <div class="form-grid two">
      <div><label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($old['email']) ?>" autocomplete="email" required><?= fe($errors,'email') ?></div>
      <div><label for="mobile">Mobile</label><input type="tel" id="mobile" name="mobile" value="<?= e($old['mobile']) ?>" inputmode="numeric" maxlength="10" required><?= fe($errors,'mobile') ?></div>
    </div>
    <div class="form-grid two">
      <div><label for="password">Password</label><input type="password" id="password" name="password" autocomplete="new-password" required aria-describedby="strengthLabel"><div class="strength"><div id="strengthBar"></div></div><small class="hint" id="strengthLabel"></small><?= fe($errors,'password') ?></div>
      <div><label for="confirm_password">Confirm password</label><input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required><?= fe($errors,'confirm_password') ?></div>
    </div>
    <div class="form-grid two">
      <div><label for="course">Course</label><select id="course" name="course" required><option value="">Select…</option><?php foreach (COURSES as $c): ?><option <?= $old['course'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select><?= fe($errors,'course') ?></div>
      <div><label for="year">Year</label><select id="year" name="year" required><option value="">Select…</option><?php for ($y = 1; $y <= 4; $y++): ?><option value="<?= $y ?>" <?= $old['year'] == $y ? 'selected' : '' ?>>Year <?= $y ?></option><?php endfor; ?></select><?= fe($errors,'year') ?></div>
    </div>
    <fieldset><legend>Gender</legend><div class="radio-row" id="gender"><?php foreach (['Male','Female','Other'] as $g): ?><label><input type="radio" name="gender" value="<?= $g ?>" <?= $old['gender'] === $g ? 'checked' : '' ?>> <?= $g ?></label><?php endforeach; ?></div><?= fe($errors,'gender') ?></fieldset>
    <div><label for="captcha_input">CAPTCHA</label><div class="captcha-row"><canvas id="captcha" width="140" height="50" aria-label="CAPTCHA image"></canvas><button type="button" class="btn secondary small" id="refreshCaptcha">↻ New</button><input type="text" id="captcha_input" autocomplete="off" style="max-width:140px" placeholder="Type code"></div><?= fe($errors,'captcha_input') ?></div>
    <div class="check-row"><label><input type="checkbox" id="terms" name="terms" value="1" <?= !empty($_POST['terms']) ? 'checked' : '' ?>> I accept the terms and conditions</label><?= fe($errors,'terms') ?></div>
    <button class="btn" type="submit">Register</button>
    <p class="hint">Already have an account? <a href="login.php">Log in</a></p>
  </div>
</form>
<?php require 'includes/footer.php'; ?>
