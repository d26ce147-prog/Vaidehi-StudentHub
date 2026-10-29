/* StudentHub - Practical 5: Registration form frontend validation (regex, strength meter, canvas CAPTCHA) */
(function () {
  'use strict';
  const form = document.getElementById('registerForm');
  if (!form) return;

  const rules = {
    name:     { re: /^[A-Za-z][A-Za-z .'-]{2,59}$/, msg: 'Enter a valid name (3-60 letters).' },
    email:    { re: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/, msg: 'Enter a valid email address.' },
    mobile:   { re: /^[6-9]\d{9}$/, msg: 'Enter a 10-digit Indian mobile number starting with 6-9.' },
    password: { re: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/, msg: 'Min 8 chars with upper, lower, digit and symbol.' },
  };
  const $ = id => document.getElementById(id);
  const setError = (id, msg) => {
    const el = $(id), out = $('err-' + id);
    if (out) out.textContent = msg || '';
    if (el && el.classList) { el.classList.toggle('invalid', !!msg); el.classList.toggle('valid', !msg && el.value !== ''); el.setAttribute('aria-invalid', !!msg); }
    return !msg;
  };

  const validators = {
    name: () => setError('name', rules.name.re.test($('name').value.trim()) ? '' : rules.name.msg),
    email: () => setError('email', rules.email.re.test($('email').value.trim()) ? '' : rules.email.msg),
    mobile: () => setError('mobile', rules.mobile.re.test($('mobile').value.trim()) ? '' : rules.mobile.msg),
    password: () => setError('password', rules.password.re.test($('password').value) ? '' : rules.password.msg),
    confirm_password: () => setError('confirm_password', $('confirm_password').value === $('password').value && $('confirm_password').value ? '' : 'Passwords do not match.'),
    course: () => setError('course', $('course').value ? '' : 'Please select a course.'),
    year: () => setError('year', $('year').value ? '' : 'Please select a year.'),
    gender: () => setError('gender', form.querySelector('input[name=gender]:checked') ? '' : 'Please select a gender.'),
    terms: () => setError('terms', $('terms').checked ? '' : 'You must accept the terms.'),
    captcha: () => setError('captcha_input', $('captcha_input').value.trim().toUpperCase() === captchaText ? '' : 'CAPTCHA does not match.'),
  };

  // Real-time validation (keyup / change / blur)
  ['name', 'email', 'mobile', 'password', 'confirm_password'].forEach(id => {
    $(id).addEventListener('keyup', validators[id]);
    $(id).addEventListener('blur', validators[id]);
  });
  $('password').addEventListener('keyup', () => { if ($('confirm_password').value) validators.confirm_password(); });
  ['course', 'year'].forEach(id => $(id).addEventListener('change', validators[id]));
  form.querySelectorAll('input[name=gender]').forEach(r => r.addEventListener('change', validators.gender));
  $('terms').addEventListener('change', validators.terms);

  // Password strength meter
  const bar = $('strengthBar'), label = $('strengthLabel');
  $('password').addEventListener('input', e => {
    const v = e.target.value; let score = 0;
    if (v.length >= 8) score++; if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++; if (/\d/.test(v)) score++; if (/[^A-Za-z0-9]/.test(v)) score++;
    const lv = [['0%', 'transparent', ''], ['25%', '#e03131', 'Weak'], ['50%', '#f08c00', 'Fair'], ['75%', '#74b816', 'Good'], ['100%', '#2b8a3e', 'Strong']][v ? score : 0];
    bar.style.width = lv[0]; bar.style.background = lv[1]; label.textContent = lv[2] ? 'Strength: ' + lv[2] : '';
  });

  // Custom canvas CAPTCHA
  let captchaText = '';
  function drawCaptcha() {
    const c = $('captcha'), ctx = c.getContext('2d'), chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    captchaText = Array.from({ length: 5 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
    ctx.clearRect(0, 0, c.width, c.height); ctx.fillStyle = '#f1f3f5'; ctx.fillRect(0, 0, c.width, c.height);
    for (let i = 0; i < 6; i++) { ctx.strokeStyle = `hsl(${Math.random() * 360},60%,60%)`; ctx.beginPath(); ctx.moveTo(Math.random() * c.width, Math.random() * c.height); ctx.lineTo(Math.random() * c.width, Math.random() * c.height); ctx.stroke(); }
    ctx.font = 'bold 28px monospace'; ctx.textBaseline = 'middle';
    captchaText.split('').forEach((ch, i) => { ctx.save(); ctx.translate(15 + i * 24, 25); ctx.rotate((Math.random() - .5) * .5); ctx.fillStyle = '#1d2433'; ctx.fillText(ch, 0, 0); ctx.restore(); });
  }
  drawCaptcha();
  $('refreshCaptcha').addEventListener('click', () => { drawCaptcha(); $('captcha_input').value = ''; });

  form.addEventListener('submit', e => {
    const results = Object.keys(validators).map(k => validators[k]());
    if (results.includes(false)) {
      e.preventDefault();
      const first = form.querySelector('.invalid, [aria-invalid="true"]'); if (first) first.focus();
    }
  });
})();
