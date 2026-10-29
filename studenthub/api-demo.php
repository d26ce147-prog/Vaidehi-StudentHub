<?php
$pageTitle = 'Live Events'; $extraScripts = ['assets/js/api-crud.js'];
require_once 'includes/functions.php'; require_once 'includes/db.php'; try_remember_login($pdo); require_login();
require 'includes/header.php'; ?>
<h1>Live events (AJAX)</h1>
<p class="lead">Powered by <code>api/events.php</code> — no page reloads.</p>
<div id="apiApp" data-api="<?= base_url() ?>api/events.php" data-csrf="<?= e(csrf_token()) ?>" data-admin="<?= is_admin() ? '1' : '0' ?>">
  <div id="msg" aria-live="polite"></div>
  <?php if (is_admin()): ?>
  <form id="evForm" class="card" style="margin-bottom:1.2rem"><input type="hidden" name="id" value="">
    <div class="form-grid two">
      <div><label for="title">Title</label><input id="title" name="title" required></div>
      <div><label for="category">Category</label><input id="category" name="category" value="General"></div>
      <div><label for="event_date">Date</label><input type="date" id="event_date" name="event_date" required></div>
      <div><label for="venue">Venue</label><input id="venue" name="venue" required></div>
      <div><label for="status">Status</label><select id="status" name="status"><option value="open">Open</option><option value="closed">Closed</option></select></div>
      <div style="align-self:end"><button class="btn" type="submit">Add event</button></div>
    </div></form>
  <?php endif; ?>
  <div class="toolbar"><div><label for="search">Search (debounced)</label><input type="search" id="search" placeholder="Search events…"></div></div>
  <div class="table-wrap"><table><thead><tr><th>#</th><th>Title</th><th>Category</th><th>Date</th><th>Venue</th><th>Status</th></tr></thead><tbody id="rows"></tbody></table></div>
</div>
<?php require 'includes/footer.php'; ?>
