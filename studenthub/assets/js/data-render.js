/* StudentHub - Practical 6: Fetch external JSON, search, filter, sort, pagination, localStorage cache */
(function () {
  'use strict';
  const root = document.getElementById('dataApp');
  if (!root) return;
  const src = root.dataset.src, type = root.dataset.type, PAGE = 6;
  const list = document.getElementById('dataList'), status = document.getElementById('dataStatus'), pager = document.getElementById('pager');
  const KEY = type === 'students' ? 'course' : 'category';
  const q = document.getElementById('q'), cat = document.getElementById('cat'), sortSel = document.getElementById('sort');
  let all = [], page = 1;
  const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  async function load() {
    status.textContent = 'Loading…';
    try {
      const res = await fetch(src);
      if (!res.ok) throw new Error('HTTP ' + res.status);
      all = await res.json();
      try { localStorage.setItem('sh_cache_' + src, JSON.stringify(all)); } catch (e) {}
      status.textContent = '';
    } catch (err) {
      let cached = null; try { cached = localStorage.getItem('sh_cache_' + src); } catch (e) {}
      if (cached) { all = JSON.parse(cached); status.textContent = 'Offline-like mode: showing cached data.'; }
      else { status.innerHTML = '<div class="alert error">Could not load data: ' + esc(err.message) + '</div>'; return; }
    }
    if (cat) { [...new Set(all.map(i => i[KEY]))].sort().forEach(c => cat.insertAdjacentHTML('beforeend', `<option>${esc(c)}</option>`)); }
    render();
  }

  function card(i) {
    if (type === 'faqs') return `<article class="card"><h3>${esc(i.question)}</h3><p>${esc(i.answer)}</p><span class="badge">${esc(i.category)}</span></article>`;
    if (type === 'students') return `<article class="card"><h3>${esc(i.name)}</h3><p><span class="badge">${esc(i.course)}</span> <span class="badge">Year ${esc(i.year)}</span> <span class="badge">${esc(i.gender)}</span></p>
      <p>✉ ${esc(i.email)}<br>📞 ${esc(i.mobile)}</p></article>`;
    return `<article class="card"><h3>${esc(i.title)}</h3><p><span class="badge ${esc(i.status)}">${esc(i.status)}</span> <span class="badge">${esc(i.category)}</span></p>
      <p>📅 ${esc(i.date)}<br>📍 ${esc(i.venue)}</p><p class="hint">${esc(i.description)}</p></article>`;
  }

  function render() {
    const term = (q.value || '').toLowerCase().trim();
    let rows = all.filter(i => (!cat || !cat.value || i[KEY] === cat.value) &&
      JSON.stringify(Object.values(i)).toLowerCase().includes(term));
    if (sortSel && sortSel.value) {
      const [k, dir] = sortSel.value.split(':');
      rows.sort((a, b) => String(a[k]).localeCompare(String(b[k])) * (dir === 'desc' ? -1 : 1));
    }
    const pages = Math.max(1, Math.ceil(rows.length / PAGE)); if (page > pages) page = pages;
    list.innerHTML = rows.slice((page - 1) * PAGE, page * PAGE).map(card).join('') || '<p class="loading">No records match your search.</p>';
    pager.innerHTML = Array.from({ length: pages }, (_, n) => `<button class="${n + 1 === page ? 'current' : ''}" data-p="${n + 1}">${n + 1}</button>`).join('');
    document.getElementById('count').textContent = rows.length + ' result(s)';
  }
  pager.addEventListener('click', e => { const p = e.target.dataset.p; if (p) { page = +p; render(); } });
  [q, cat, sortSel].forEach(el => el && el.addEventListener('input', () => { page = 1; render(); }));
  load();
})();
