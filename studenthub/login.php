<?php
// Practical 10: secure login, password_verify, session regeneration, RBAC redirect, remember-me token, last-login
$pageTitle = 'Login'; require_once 'includes/functions.php'; require_once 'includes/db.php';
try_remember_login($pdo);
if (is_logged_in()) redirect(is_admin() ? 'admin/dashboard.php' : 'dashboard.php');
$error = ''; $email = '';
if (isset($_GET['timeout'])) $error = 'Your session timed out. Please log in again.';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? ''); $pass = $_POST['password'] ?? '';
    if (!csrf_check()) $error = 'Invalid form token. Reload and try again.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') $error = 'Enter your email and password.';
    else {
        $st = $pdo->prepare('SELECT * FROM users WHERE email = ?'); $st->execute([$email]); $u = $st->fetch();
        if ($u && $u['status'] === 'active' && password_verify($pass, $u['password_hash'])) {
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
            login_user($u);
            $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$u['id']]);
            audit($pdo, 'login', $u['email']);
            if (!empty($_POST['remember'])) {
                $sel = bin2hex(random_bytes(12)); $tok = bin2hex(random_bytes(32));
                $pdo->prepare('INSERT INTO remember_tokens (user_id,selector,token_hash,expires_at) VALUES (?,?,?,DATE_ADD(NOW(), INTERVAL ' . REMEMBER_DAYS . ' DAY))')->execute([$u['id'], $sel, hash('sha256', $tok)]);
                setcookie('remember', "$sel:$tok", ['expires' => time() + REMEMBER_DAYS * 86400, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            }
            redirect($u['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php');
        }
        $error = 'Invalid credentials or inactive account.';
        usleep(300000); // slow down brute force
        if ($u) audit($pdo, 'login_failed', $email);
    }
}
require 'includes/header.php'; ?>
<h1>Login</h1>
<form method="post" class="card form-card" novalidate><?= csrf_field() ?>
  <?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <div class="form-grid">
    <div><label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="username" required></div>
    <div><label for="password">Password</label><input type="password" id="password" name="password" autocomplete="current-password" required></div>
    <div class="check-row"><label><input type="checkbox" name="remember" value="1"> Remember me for <?= REMEMBER_DAYS ?> days</label></div>
    <button class="btn" type="submit">Log in</button>
    <p class="hint">No account? <a href="register.php">Register</a><br>Demo: <code>admin@studenthub.local</code> / <code>Admin@123</code> · <code>student@studenthub.local</code> / <code>Student@123</code></p>
  </div>
</form>
<?php require 'includes/footer.php'; ?>
