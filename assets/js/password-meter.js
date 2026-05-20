/* ─────────────────────────────────────────────────────────────────────────
 * password-meter.js — Live strength indicator for register.php
 *
 * Loaded at the bottom of register.php. No dependencies.
 * Path: assets/js/password-meter.js
 *
 * Hook: looks for #signup-password, #meter, #meter-helper. If any is
 * missing it bails silently — safe to include on other pages too.
 * ───────────────────────────────────────────────────────────────────────── */

(function () {
  const pw     = document.getElementById('signup-password');
  const meter  = document.getElementById('meter');
  const helper = document.getElementById('meter-helper');
  if (!pw || !meter || !helper) return;

  const LABELS = [
    'Too short',
    'Weak — add length',
    'Better — add a symbol',
    'Strong',
    'Excellent',
  ];

  function score(s) {
    let n = 0;
    if (s.length >= 10) n++;
    if (/[A-Z]/.test(s) && /[a-z]/.test(s)) n++;
    if (/[0-9]/.test(s)) n++;
    if (/[^A-Za-z0-9]/.test(s)) n++;
    return Math.min(4, n);
  }

  pw.addEventListener('input', () => {
    const n = pw.value ? score(pw.value) : 0;
    meter.dataset.score = String(n);
    helper.textContent = LABELS[n];
  });
})();
