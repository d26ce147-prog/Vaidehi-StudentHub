<?php $pageTitle = 'Student Directory'; $extraScripts = ['assets/js/data-render.js']; require 'includes/header.php'; ?>
<h1>Student directory</h1>
<p class="lead">Student profiles loaded from <code>data/students.json</code> with the Fetch API — search, filter by course, sort and paginate.</p>
<div id="dataApp" data-src="data/students.json" data-type="students">
  <div class="toolbar">
    <div><label for="q">Search</label><input type="search" id="q" placeholder="Search students…"></div>
    <div><label for="cat">Course</label><select id="cat"><option value="">All courses</option></select></div>
    <div><label for="sort">Sort</label><select id="sort"><option value="">Default</option><option value="name:asc">Name A–Z</option><option value="name:desc">Name Z–A</option><option value="year:asc">Year ↑</option><option value="year:desc">Year ↓</option></select></div>
  </div>
  <p id="count" class="hint" aria-live="polite"></p>
  <div id="dataStatus" class="loading" aria-live="polite"></div>
  <div id="dataList" class="grid cols-3"></div>
  <div id="pager" class="pagination" aria-label="Pagination"></div>
</div>
<?php require 'includes/footer.php'; ?>
