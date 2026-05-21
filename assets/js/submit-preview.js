/* ─────────────────────────────────────────────────────────────────────────
 * submit-preview.js — Live catalog-card preview for submit_species.php
 *
 * Wire-up: loaded at the bottom of submit_species.php. No dependencies.
 * Path: assets/js/submit-preview.js
 *
 * What it does:
 *   - Updates the right-side preview card as the user types
 *   - Renders the photo thumbnail and the catalog card image when the
 *     image URL field has a value
 *   - Mirrors segmented-radio checked state to data-on for CSS styling
 *   - Tracks summary character count (0 / 600)
 * ───────────────────────────────────────────────────────────────────────── */

(function () {
  const $ = (s) => document.querySelector(s);
  const form = $('#contribute-form');
  if (!form) return;

  const pcCommon  = $('#pc-common');
  const pcLatin   = $('#pc-latin');
  const pcStatus  = $('#pc-status');
  const pcDot     = $('#pc-dot');
  const pcHabitat = $('#pc-habitat');
  const pcImg     = $('#pc-img');
  const thumb     = $('#thumb');
  const countEl   = $('#count');

  const STATUS_LABEL = {
    stable:     'Least concern',
    vulnerable: 'Vulnerable',
    endangered: 'Endangered',
    critical:   'Critically endangered',
  };
  const HABITAT_LABEL = {
    forest:  'Forest',
    ocean:   'Ocean & reef',
    alpine:  'Alpine',
    wetland: 'Wetland',
    savanna: 'Savanna',
    desert:  'Desert',
    urban:   'Urban edge',
  };

  function setText(el, val, fallback) {
    if (val && val.trim()) {
      el.textContent = val.trim();
      el.classList.remove('empty');
    } else {
      el.textContent = fallback;
      el.classList.add('empty');
    }
  }

  function render() {
    const fd = new FormData(form);

    setText(pcCommon, fd.get('common'),     'Untitled specimen');
    setText(pcLatin,  fd.get('scientific'), 'Scientific name pending');

    const status = fd.get('status') || 'stable';
    pcStatus.dataset.s = status;
    pcDot.dataset.s    = status;
    pcStatus.textContent = fd.get('status') ? STATUS_LABEL[status] : 'Status pending';

    const habitat = fd.get('habitat');
    pcHabitat.textContent = HABITAT_LABEL[habitat] || 'Habitat pending';

    const img = (fd.get('image') || '').trim();
    const existingImg = pcImg.querySelector('.img');
    const ph = pcImg.querySelector('.ph');
    if (img) {
      if (existingImg) {
        existingImg.style.backgroundImage = `url('${img}')`;
      } else {
        const d = document.createElement('div');
        d.className = 'img';
        d.style.backgroundImage = `url('${img}')`;
        pcImg.prepend(d);
      }
      if (ph) ph.style.display = 'none';
      thumb.innerHTML = `<div class="img" style="background-image:url('${img}')"></div>`;
    } else {
      if (existingImg) existingImg.remove();
      if (ph) ph.style.display = '';
      thumb.innerHTML = `<div class="ph">Preview<br>appears here</div>`;
    }

    if (countEl) countEl.textContent = (fd.get('summary') || '').length;
  }

  // Mirror segmented-control checked state to data-on for CSS
  document.querySelectorAll('.seg').forEach((group) => {
    const sync = () => {
      group.querySelectorAll('label').forEach((l) => {
        const r = l.querySelector('input');
        l.dataset.on = r && r.checked ? '1' : '0';
      });
    };
    group.addEventListener('change', () => { sync(); render(); });
    sync();
  });

  form.addEventListener('input', render);
  form.addEventListener('change', render);
  render();
})();
