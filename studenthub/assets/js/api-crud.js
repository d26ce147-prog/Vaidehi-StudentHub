/* Practical 13: AJAX/Fetch CRUD without page reload (debounced search, loading/success/error states) */
(function () {
  'use strict';
  const app = document.getElementById('apiApp'); if (!app) return;
  const API = app.dataset.api, CSRF = app.dataset.csrf, ADMIN = app.dataset.admin === '1';
  const tbody = document.getElementById('rows'), msg = document.getElementById('msg'), form = document.getElementById('evForm');
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const notify = (t, m) => { msg.innerHTML = `<div class="alert ${t}" role="alert">${esc(m)}</div>`; setTimeout(() => msg.innerHTML = '', 4000); };

  async function call(url, opts = {}) {
    const res = await fetch(url, { credentials: 'same-origin', ...opts });
    let data; try { data = await res.json(); } catch (e) { throw new Error('Invalid server response (' + res.status + ')'); }
    if (!res.ok || !data.success) { const err = new Error(data.message || 'Request failed'); err.errors = data.errors; throw err; }
    return data;
  }
  const post = body => call(API, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(body) });

  async function load(q = '') {
    tbody.innerHTML = '<tr><td colspan="6" class="loading">Loading…</td></tr>';
    try {
      const { data } = await call(API + '?q=' + encodeURIComponent(q));
      tbody.innerHTML = data.map(e => `<tr data-id="${e.id}"><td>${e.id}</td><td>${esc(e.title)}</td><td>${esc(e.category)}</td><td>${esc(e.event_date)}</td><td>${esc(e.venue)}</td>
        <td><span class="badge ${esc(e.status)}">${esc(e.status)}</span> ${ADMIN ? `<button class="btn small secondary" data-edit='${esc(JSON.stringify(e))}'>Edit</button> <button class="btn small danger" data-del="${e.id}">Delete</button>` : ''}</td></tr>`).join('')
        || '<tr><td colspan="6" class="loading">No events found.</td></tr>';
    } catch (err) { tbody.innerHTML = ''; notify('error', err.message); }
  }

  // debounced search
  let t; document.getElementById('search').addEventListener('input', e => { clearTimeout(t); t = setTimeout(() => load(e.target.value.trim()), 350); });

  if (ADMIN) {
    tbody.addEventListener('click', async e => {
      const d = e.target.closest('[data-del]'), ed = e.target.closest('[data-edit]');
      if (d && confirm('Delete this event?')) { try { const r = await post({ action: 'delete', id: +d.dataset.del }); notify('success', r.message); load(document.getElementById('search').value); } catch (err) { notify('error', err.message); } }
      if (ed) { const v = JSON.parse(ed.dataset.edit); Object.keys(v).forEach(k => { if (form.elements[k]) form.elements[k].value = v[k]; }); form.querySelector('button[type=submit]').textContent = 'Update event'; window.scrollTo({ top: 0, behavior: 'smooth' }); }
    });
    form.addEventListener('submit', async e => {
      e.preventDefault(); const f = Object.fromEntries(new FormData(form)); const id = +f.id; f.action = id ? 'update' : 'create';
      const btn = form.querySelector('button[type=submit]'); btn.disabled = true;
      try { const r = await post(f); notify('success', r.message); form.reset(); form.elements.id.value = ''; btn.textContent = 'Add event'; load(); }
      catch (err) { notify('error', err.message + (err.errors ? ' — ' + Object.values(err.errors).join(' ') : '')); }
      finally { btn.disabled = false; }
    });
  }
  load();
})();
