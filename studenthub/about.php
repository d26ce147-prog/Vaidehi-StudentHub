<?php $pageTitle = 'About'; require 'includes/header.php'; ?>
<h1>About StudentHub</h1>
<p class="lead">A semester project for Web Development Frameworks (ITUE203) at CHARUSAT that grows practical by practical into a complete full-stack portal.</p>
<div class="grid cols-2">
  <section class="card"><h2>User roles</h2><ul><li><strong>Visitor</strong> – view public pages, register, contact.</li><li><strong>Student</strong> – dashboard, profile, event registration.</li><li><strong>Admin</strong> – student CRUD, event CRUD, dashboard, audit log.</li></ul></section>
  <section class="card"><h2>Tech stack</h2><p>HTML5, CSS3 (Grid &amp; Flexbox), JavaScript ES6, PHP, MySQL (PDO + MySQLi), Fetch API, XAMPP.</p></section>
</div>
<section class="card" style="margin-top:1.2rem"><h2>Sitemap</h2>
<ul><li>Public: Home, About, Events, FAQ, Contact, Feedback, Login, Register</li><li>Student: Dashboard, Profile, Live Events (AJAX)</li><li>Admin: Students, Manage Events, Admin Dashboard, Audit Log</li></ul></section>
<?php require 'includes/footer.php'; ?>
