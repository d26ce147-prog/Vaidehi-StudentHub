<?php $pageTitle = 'Home'; require 'includes/header.php'; ?>
<section class="hero" aria-labelledby="hero-title">
  <div>
    <h1 id="hero-title">Your campus, <span style="color:var(--primary)">connected.</span></h1>
    <p class="lead">StudentHub is the one-stop portal for student records, events and registrations.</p>
    <p><a class="btn" href="register.php">Get started</a> <a class="btn secondary" href="events.php">Browse events</a>
       <button class="btn secondary" data-modal="welcomeModal" type="button">Quick tour</button></p>
  </div>
  <aside class="hero-card"><h2 style="margin-top:0">Why StudentHub?</h2><ul><li>Secure login with roles</li><li>Event registration in one click</li><li>Admin dashboard &amp; audit trail</li><li>Responsive on every device</li></ul></aside>
</section>

<section aria-label="Highlights" class="slider" style="margin-bottom:2rem">
  <div class="slides">
    <div class="slide"><h2>Annual Tech Fest</h2><p>Hackathons, talks and workshops — register now.</p></div>
    <div class="slide"><h2>Placement Season</h2><p>Resume workshops and mock interviews every week.</p></div>
    <div class="slide"><h2>Cultural Week</h2><p>Music, dance and drama — everyone is welcome.</p></div>
  </div>
  <button class="slider-btn prev" aria-label="Previous slide">‹</button><button class="slider-btn next" aria-label="Next slide">›</button>
</section>

<section aria-labelledby="features"><h2 id="features">What you can do</h2>
  <div class="grid cols-3">
    <article class="card"><h3>👩‍🎓 Student records</h3><p>Admins add, search, filter and export student data with soft delete.</p></article>
    <article class="card"><h3>📅 Events</h3><p>Browse upcoming events, search by category and register instantly.</p></article>
    <article class="card"><h3>🔐 Secure access</h3><p>Hashed passwords, sessions, CSRF protection and role-based access.</p></article>
  </div>
</section>

<div class="modal-backdrop" id="welcomeModal" role="dialog" aria-modal="true" aria-labelledby="mTitle"><div class="modal">
  <h2 id="mTitle">Welcome to StudentHub 👋</h2><p>Register an account, log in and register for events. Admins manage students and events from the Admin menu.</p>
  <button class="btn" data-close type="button">Got it</button></div></div>
<?php require 'includes/footer.php'; ?>
