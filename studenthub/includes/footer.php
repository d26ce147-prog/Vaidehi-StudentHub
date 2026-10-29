</main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div><strong>StudentHub</strong><p>Student & event management portal — CHARUSAT, ITUE203 Web Development Frameworks.</p></div>
    <div><strong>Links</strong><br><a href="<?= base_url() ?>about.php">About</a><br><a href="<?= base_url() ?>faq.php">FAQ</a><br><a href="<?= base_url() ?>contact.php">Contact</a></div>
    <div><strong>Account</strong><br><a href="<?= base_url() ?>login.php">Login</a><br><a href="<?= base_url() ?>register.php">Register</a></div>
  </div>
  <div class="container"><small>&copy; <?= date('Y') ?> StudentHub. All rights reserved.</small></div>
</footer>
<script src="<?= base_url() ?>assets/js/main.js"></script>
<?php foreach ($extraScripts ?? [] as $s): ?><script src="<?= base_url() . e($s) ?>"></script><?php endforeach; ?>
</body>
</html>
