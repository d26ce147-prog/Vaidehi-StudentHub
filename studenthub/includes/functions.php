<?php
// Shared helpers: sessions, auth, CSRF, flash messages, audit log
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_start();
}

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function clean($v): string { return trim(strip_tags((string)$v)); }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function base_url(): string { // path to project root, e.g. /studenthub/
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    return preg_replace('#/(admin|api)/[^/]*$|/[^/]*$#', '/', $script);
}

// ---- Flash messages
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function show_flash(): void {
    foreach ($_SESSION['flash'] ?? [] as [$t, $m]) echo '<div class="alert ' . e($t) . '" role="alert" data-autohide>' . e($m) . '</div>';
    unset($_SESSION['flash']);
}

// ---- CSRF
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(?string $t = null): bool { $t = $t ?? ($_POST['csrf'] ?? ''); return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t); }

// ---- Auth
function is_logged_in(): bool { return !empty($_SESSION['user']); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }

function check_timeout(): void {
    if (is_logged_in()) {
        if (time() - ($_SESSION['last_activity'] ?? time()) > SESSION_TIMEOUT) {
            logout_user(false);
            session_start();
            flash('error', 'Session expired due to inactivity. Please log in again.');
            redirect(base_url() . 'login.php?timeout=1');
        }
        $_SESSION['last_activity'] = time();
    }
}
function require_login(): void {
    check_timeout();
    if (!is_logged_in()) { flash('error', 'Please log in to continue.'); redirect(base_url() . 'login.php'); }
}
function require_role(string $role): void {
    require_login();
    if ((current_user()['role'] ?? '') !== $role) { http_response_code(403); flash('error', 'Access denied: ' . $role . ' only.'); redirect(base_url() . 'dashboard.php'); }
}
function login_user(array $u): void {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
    $_SESSION['last_activity'] = time();
}
function logout_user(bool $clearRemember = true): void {
    if ($clearRemember && isset($_COOKIE['remember'])) {
        global $pdo;
        if (isset($pdo)) { [$sel] = explode(':', $_COOKIE['remember'] . ':'); $pdo->prepare('DELETE FROM remember_tokens WHERE selector=?')->execute([$sel]); }
        setcookie('remember', '', time() - 3600, '/');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']); }
    session_destroy();
}

// ---- Audit log (Practical 14)
function audit(PDO $pdo, string $action, string $details = ''): void {
    $uid = current_user()['id'] ?? null;
    $pdo->prepare('INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?,?,?,?)')
        ->execute([$uid, $action, mb_substr($details, 0, 255), $_SERVER['REMOTE_ADDR'] ?? null]);
}

// ---- Validation (server side, mirrors validation.js)
function validate_registration(array $d): array {
    $err = [];
    if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", $d['name'] ?? '')) $err['name'] = 'Enter a valid name (3-60 letters).';
    if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL)) $err['email'] = 'Enter a valid email address.';
    if (!preg_match('/^[6-9]\d{9}$/', $d['mobile'] ?? '')) $err['mobile'] = 'Enter a valid 10-digit mobile number.';
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $d['password'] ?? '')) $err['password'] = 'Password needs 8+ chars with upper, lower, digit and symbol.';
    if (($d['password'] ?? '') !== ($d['confirm_password'] ?? '')) $err['confirm_password'] = 'Passwords do not match.';
    if (!in_array($d['course'] ?? '', COURSES, true)) $err['course'] = 'Select a valid course.';
    if (!in_array((int)($d['year'] ?? 0), [1, 2, 3, 4], true)) $err['year'] = 'Select a valid year.';
    if (!in_array($d['gender'] ?? '', ['Male', 'Female', 'Other'], true)) $err['gender'] = 'Select a gender.';
    if (empty($d['terms'])) $err['terms'] = 'You must accept the terms.';
    return $err;
}
const COURSES = ['B.Tech CE', 'B.Tech IT', 'B.Tech CSE', 'B.Tech AIML', 'MCA', 'BCA'];

// ---- Remember-me auto login (Practical 10): random selector + hashed validator in DB
function try_remember_login(PDO $pdo): void {
    if (is_logged_in() || empty($_COOKIE['remember'])) return;
    $parts = explode(':', $_COOKIE['remember']);
    if (count($parts) !== 2) return;
    [$sel, $tok] = $parts;
    $st = $pdo->prepare('SELECT rt.*, u.id uid, u.name, u.email, u.role, u.status FROM remember_tokens rt JOIN users u ON u.id=rt.user_id WHERE rt.selector=? AND rt.expires_at > NOW()');
    $st->execute([$sel]); $row = $st->fetch();
    if ($row && $row['status'] === 'active' && hash_equals($row['token_hash'], hash('sha256', $tok))) {
        login_user(['id' => $row['uid'], 'name' => $row['name'], 'email' => $row['email'], 'role' => $row['role']]);
    }
}

function json_out(array $data, int $code = 200): never {
    http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data); exit;
}
