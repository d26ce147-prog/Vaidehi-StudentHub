<?php
// Practical 7: JSON file storage
$pageTitle = 'Feedback'; require_once 'includes/functions.php';
$errors = []; $old = ['name' => '', 'rating' => '', 'comments' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) $errors['form'] = 'Invalid form token.';
    else {
        foreach ($old as $k => $_) $old[$k] = clean($_POST[$k] ?? '');
        if (mb_strlen($old['name']) < 2) $errors['name'] = 'Please enter your name.';
        if (!in_array((int)$old['rating'], [1, 2, 3, 4, 5], true)) $errors['rating'] = 'Choose a rating from 1 to 5.';
        if (mb_strlen($old['comments']) < 5) $errors['comments'] = 'Please add a few words.';
        if (!$errors) {
            $file = BASE_PATH . '/data/feedback.json';
            $fh = fopen($file, 'c+');
            if ($fh && flock($fh, LOCK_EX)) {
                $all = json_decode(stream_get_contents($fh), true) ?: [];
                $all[] = ['time' => date('c'), 'name' => $old['name'], 'rating' => (int)$old['rating'], 'comments' => $old['comments']];
                ftruncate($fh, 0); rewind($fh); fwrite($fh, json_encode($all, JSON_PRETTY_PRINT)); flock($fh, LOCK_UN); fclose($fh);
                flash('success', 'Thank you for your feedback!'); redirect('feedback.php');
            } else $errors['form'] = 'Could not store feedback.';
        }
    }
}
$stored = file_exists(BASE_PATH . '/data/feedback.json') ? array_reverse(json_decode(file_get_contents(BASE_PATH . '/data/feedback.json'), true) ?: []) : [];
require 'includes/header.php'; ?>
<h1>Feedback</h1>
<form method="post" class="card form-card" novalidate><?= csrf_field() ?>
  <?php if (isset($errors['form'])): ?><div class="alert error"><?= e($errors['form']) ?></div><?php endif; ?>
  <div class="form-grid">
    <div><label for="name">Name</label><input id="name" name="name" type="text" value="<?= e($old['name']) ?>" required><div class="error-msg"><?= e($errors['name'] ?? '') ?></div></div>
    <fieldset><legend>Rating</legend><div class="radio-row"><?php for ($i = 1; $i <= 5; $i++): ?><label><input type="radio" name="rating" value="<?= $i ?>" <?= (int)$old['rating'] === $i ? 'checked' : '' ?>> <?= str_repeat('★', $i) ?></label><?php endfor; ?></div><div class="error-msg"><?= e($errors['rating'] ?? '') ?></div></fieldset>
    <div><label for="comments">Comments</label><textarea id="comments" name="comments" rows="4"><?= e($old['comments']) ?></textarea><div class="error-msg"><?= e($errors['comments'] ?? '') ?></div></div>
    <button class="btn" type="submit">Submit feedback</button>
  </div>
</form>
<?php if ($stored): ?><h2 style="margin-top:2rem">Recent feedback</h2><div class="grid cols-3">
<?php foreach (array_slice($stored, 0, 6) as $f): ?><article class="card"><strong><?= e($f['name']) ?></strong> <span aria-label="<?= (int)$f['rating'] ?> stars"><?= str_repeat('★', (int)$f['rating']) ?></span><p><?= e($f['comments']) ?></p></article><?php endforeach; ?></div><?php endif; ?>
<?php require 'includes/footer.php'; ?>
