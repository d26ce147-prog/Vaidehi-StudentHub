/* Static-demo form handler: no server, just shows a message (and optional redirect) after valid submit */
(function () {
  'use strict';
  document.querySelectorAll('form[data-msg]').forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      if (e.defaultPrevented && form.id === 'registerForm' && form.querySelector('.invalid, [aria-invalid="true"]')) return; // validation.js blocked it
      if (form.id !== 'registerForm' && !form.checkValidity()) { form.reportValidity(); return; }
      const box = document.createElement('div'); box.className = 'alert success'; box.setAttribute('role', 'alert'); box.textContent = form.dataset.msg;
      form.prepend(box);
      if (form.dataset.redirect) setTimeout(() => { location.href = form.dataset.redirect; }, 1800);
    });
  });
})();
