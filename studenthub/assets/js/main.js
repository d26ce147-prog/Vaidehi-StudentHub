/* StudentHub - Practical 4: DOM, events, localStorage UI interactivity */
(function () {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const safeGet = k => { try { return localStorage.getItem(k); } catch (e) { return null; } };
  const safeSet = (k, v) => { try { localStorage.setItem(k, v); } catch (e) {} };

  // Theme switcher (restored from localStorage)
  const root = document.documentElement;
  const savedTheme = safeGet('sh_theme');
  if (savedTheme) root.setAttribute('data-theme', savedTheme);
  const themeBtn = $('#themeToggle');
  const syncThemeBtn = () => { if (themeBtn) themeBtn.textContent = root.getAttribute('data-theme') === 'dark' ? '☀️' : '🌙'; };
  syncThemeBtn();
  if (themeBtn) themeBtn.addEventListener('click', () => {
    const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next); safeSet('sh_theme', next); syncThemeBtn();
  });

  // Hamburger menu
  const burger = $('#hamburger'), menu = $('#navMenu');
  if (burger && menu) burger.addEventListener('click', () => {
    const open = menu.classList.toggle('open');
    burger.setAttribute('aria-expanded', open);
  });

  // Notification banner (dismissal remembered)
  const banner = $('#banner');
  if (banner) {
    if (safeGet('sh_banner') === 'closed') banner.remove();
    else $('#bannerClose').addEventListener('click', () => { banner.remove(); safeSet('sh_banner', 'closed'); });
  }

  // Collapsible FAQ (accessible)
  $$('.faq-item').forEach(item => {
    const btn = $('.faq-q', item);
    btn.addEventListener('click', () => {
      const open = item.classList.toggle('open');
      btn.setAttribute('aria-expanded', open);
    });
  });

  // Modal popup
  const openers = $$('[data-modal]');
  openers.forEach(o => o.addEventListener('click', () => {
    const m = document.getElementById(o.dataset.modal);
    if (m) { m.classList.add('open'); const f = m.querySelector('button, a, input'); f && f.focus(); }
  }));
  $$('.modal-backdrop').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m || e.target.hasAttribute('data-close')) m.classList.remove('open'); });
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') $$('.modal-backdrop.open').forEach(m => m.classList.remove('open')); });

  // Slider
  const slider = $('.slider');
  if (slider) {
    const track = $('.slides', slider), count = $$('.slide', slider).length; let idx = 0, timer;
    const go = n => { idx = (n + count) % count; track.style.transform = `translateX(-${idx * 100}%)`; };
    $('.prev', slider).addEventListener('click', () => { go(idx - 1); reset(); });
    $('.next', slider).addEventListener('click', () => { go(idx + 1); reset(); });
    const reset = () => { clearInterval(timer); timer = setInterval(() => go(idx + 1), 5000); };
    reset();
  }

  // Auto-hide flash alerts
  $$('.alert[data-autohide]').forEach(a => setTimeout(() => a.remove(), 6000));

  // Confirm delete
  document.addEventListener('click', e => {
    const t = e.target.closest('[data-confirm]');
    if (t && !confirm(t.dataset.confirm)) e.preventDefault();
  });
})();
