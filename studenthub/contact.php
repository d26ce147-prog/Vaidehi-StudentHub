<?php
// Practical 7: PHP form processing, server-side validation, CSV storage, CSRF
$pageTitle = 'Contact'; require_once 'includes/functions.php';
$errors = []; $old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) { $errors['form'] = 'Invalid form token. Please reload and try again.'; }
    else {
        foreach ($old as $k => $_) $old[$k] = clean($_POST[$k] ?? '');
        if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", $old['name'])) $errors['name'] = 'Enter a valid name.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email.';
        if (mb_strlen($old['subject']) < 3 || mb_strlen($old['subject']) > 100) $errors['subject'] = 'Subject must be 3-100 characters.';
        if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 1000) $errors['message'] = 'Message must be 10-1000 characters.';
        if (!$errors) {
            $file = BASE_PATH . '/data/contacts.csv'; $new = !file_exists($file);
            $fh = fopen($file, 'a');
            if ($fh && flock($fh, LOCK_EX)) {
                if ($new) fputcsv($fh, ['timestamp', 'name', 'email', 'subject', 'message']);
                fputcsv($fh, [date('Y-m-d H:i:s'), $old['name'], $old['email'], $old['subject'], $old['message']]);
                flock($fh, LOCK_UN); fclose($fh);
                flash('success', 'Thanks! Your message has been saved.'); redirect('contact.php');
            } else $errors['form'] = 'Could not save your message. Try again later.';
        }
    }
}
require 'includes/header.php'; ?>
<h1>Contact us</h1>
<form method="post" class="card form-card" novalidate>
  <?= csrf_field() ?>
  <?php if (isset($errors['form'])): ?><div class="alert error"><?= e($errors['form']) ?></div><?php endif; ?>
  <div class="form-grid">
    <?php foreach ([['name','Full name','text'],['email','Email','email'],['subject','Subject','text']] as [$n,$l,$t]): ?>
    <div><label for="<?= $n ?>"><?= $l ?></label><input type="<?= $t ?>" id="<?= $n ?>" name="<?= $n ?>" value="<?= e($old[$n]) ?>" required class="<?= isset($errors[$n]) ? 'invalid' : '' ?>"><div class="error-msg"><?= e($errors[$n] ?? '') ?></div></div>
    <?php endforeach; ?>
    <div><label for="message">Message</label><textarea id="message" name="message" rows="5" required class="<?= isset($errors['message']) ? 'invalid' : '' ?>"><?= e($old['message']) ?></textarea><div class="error-msg"><?= e($errors['message'] ?? '') ?></div></div>
    <button class="btn" type="submit">Send message</button>
  </div>
</form>
<?php require 'includes/footer.php'; ?>
