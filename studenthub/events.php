<?php $pageTitle = 'Events'; $extraScripts = ['assets/js/data-render.js']; require 'includes/header.php'; ?>
<h1>Events</h1>
<p class="lead">Loaded from <code>data/events.json</code> with the Fetch API — search, filter, sort and paginate.</p>
<div id="dataApp" data-src="data/events.json" data-type="events">
  <div class="toolbar">
    <div><label for="q">Search</label><input type="search" id="q" placeholder="Search events…"></div>
    <div><label for="cat">Category</label><select id="cat"><option value="">All categories</option></select></div>
    <div><label for="sort">Sort</label><select id="sort"><option value="">Default</option><option value="title:asc">Title A–Z</option><option value="title:desc">Title Z–A</option><option value="date:asc">Date ↑</option><option value="date:desc">Date ↓</option></select></div>
  </div>
  <p id="count" class="hint" aria-live="polite"></p>
  <div id="dataStatus" class="loading" aria-live="polite"></div>
  <div id="dataList" class="grid cols-3"></div>
  <div id="pager" class="pagination" aria-label="Pagination"></div>
</div>
<?php require 'includes/footer.php'; ?>
