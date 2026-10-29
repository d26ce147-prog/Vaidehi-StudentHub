<?php $pageTitle = 'FAQ'; require 'includes/header.php';
$faqs = json_decode(file_get_contents(__DIR__ . '/data/faqs.json'), true) ?: []; ?>
<h1>Frequently Asked Questions</h1>
<div style="max-width:800px">
<?php foreach ($faqs as $i => $f): ?>
  <div class="faq-item"><button class="faq-q" aria-expanded="false" aria-controls="faq<?= $i ?>"><?= e($f['question']) ?><span aria-hidden="true">+</span></button>
  <div class="faq-a" id="faq<?= $i ?>" role="region"><p><?= e($f['answer']) ?></p></div></div>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>
