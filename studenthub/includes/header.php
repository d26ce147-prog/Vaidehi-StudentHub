<?php
// Shared layout header. Set $pageTitle (and optional $crumbs) before including.
require_once __DIR__ . '/functions.php';
check_timeout();
$pageTitle = $pageTitle ?? 'StudentHub';
$root = base_url();
$cur = basename($_SERVER['SCRIPT_NAME']);
$user = current_user();
$nav = [['index.php', 'Home'], ['about.php', 'About'], ['events.php', 'Events'], ['directory.php', 'Directory'], ['faq.php', 'FAQ'], ['contact.php', 'Contact'], ['feedback.php', 'Feedback']];
if ($user) { $nav[] = ['dashboard.php', 'Dashboard']; $nav[] = ['profile.php', 'Profile']; $nav[] = ['api-demo.php', 'Live Events']; }
if ($user && $user['role'] === 'admin') { $nav[] = ['students.php', 'Students']; $nav[] = ['manage_events.php', 'Manage Events']; $nav[] = ['admin/dashboard.php', 'Admin']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="StudentHub - student & event management portal">
<title><?= e($pageTitle) ?> | StudentHub</title>
<link rel="stylesheet" href="<?= $root ?>assets/css/style.css">
<script>try{var t=localStorage.getItem('sh_theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}</script>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<header class="site-header">
  <div class="container nav-bar">
    <a class="brand" href="<?= $root ?>index.php">Student<span>Hub</span></a>
    <div class="nav-tools">
      <button class="theme-btn" id="themeToggle" aria-label="Toggle light/dark theme">🌙</button>
      <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false" aria-controls="navMenu">☰</button>
    </div>
    <nav aria-label="Main navigation" style="flex:1 1 auto">
      <ul class="nav-menu" id="navMenu" style="justify-content:flex-end">
        <?php foreach ($nav as [$href, $label]): ?>
          <li><a href="<?= $root . $href ?>" <?= $cur === basename($href) ? 'aria-current="page"' : '' ?>><?= $label ?></a></li>
        <?php endforeach; ?>
        <?php if ($user): ?>
          <li><a href="<?= $root ?>logout.php">Logout (<?= e(explode(' ', $user['name'])[0]) ?>)</a></li>
        <?php else: ?>
          <li><a href="<?= $root ?>login.php" <?= $cur === 'login.php' ? 'aria-current="page"' : '' ?>>Login</a></li>
          <li><a href="<?= $root ?>register.php" <?= $cur === 'register.php' ? 'aria-current="page"' : '' ?>>Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>
<div class="banner" id="banner" role="status"><div class="container"><span>🎉 Annual Tech Fest registrations are open — see the Events page!</span><button id="bannerClose" aria-label="Dismiss notification">&times;</button></div></div>
<div class="container">
  <?php if ($cur !== 'index.php'): ?>
  <nav class="breadcrumb" aria-label="Breadcrumb"><a href="<?= $root ?>index.php">Home</a> › <span><?= e($pageTitle) ?></span></nav>
  <?php endif; ?>
</div>
<main id="main" class="container">
<?php show_flash(); ?>
