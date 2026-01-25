(function () {
  'use strict';

  const cfg = window.SUKUN || {};
  const $ = (sel, ctx = document) => ctx.querySelector(sel);

  // Smooth scroll
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const id = a.getAttribute('href');
    const el = $(id);
    if (!el) return;
    e.preventDefault();
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  // Countdown
  const targetIso = cfg.eventStartIso;
  const target = targetIso ? new Date(targetIso) : null;

  const elDays = $('#cd-days');
  const elHours = $('#cd-hours');
  const elMins = $('#cd-mins');
  const elSecs = $('#cd-secs');

  function pad2(n) { return String(n).padStart(2, '0'); }

  function tick() {
    if (!target || isNaN(target.getTime())) return;

    const now = new Date();
    const diff = target.getTime() - now.getTime();

    if (diff <= 0) {
      if (elDays) elDays.textContent = '0';
      if (elHours) elHours.textContent = '00';
      if (elMins) elMins.textContent = '00';
      if (elSecs) elSecs.textContent = '00';
      return;
    }

    const s = Math.floor(diff / 1000);
    const days = Math.floor(s / 86400);
    const hours = Math.floor((s % 86400) / 3600);
    const mins = Math.floor((s % 3600) / 60);
    const secs = s % 60;

    if (elDays) elDays.textContent = String(days);
    if (elHours) elHours.textContent = pad2(hours);
    if (elMins) elMins.textContent = pad2(mins);
    if (elSecs) elSecs.textContent = pad2(secs);
  }

  tick();
  setInterval(tick, 1000);

  // RSVP submit (AJAX)
  const form = $('#rsvp-form');
  if (form && cfg.rsvpUrl) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const btn = $('#rsvp-submit');
      if (btn) btn.disabled = true;

      try {
        const fd = new FormData(form);
        const res = await fetch(cfg.rsvpUrl, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        });

        const data = await res.json().catch(() => null);
        if (!data || !data.success) {
          const msg = data?.message || 'No fue posible guardar tu confirmación.';
          alert(msg);
          if (btn) btn.disabled = false;
          return;
        }

        alert(data.message || 'Confirmación registrada. ¡Gracias!');
        form.reset();
        if (btn) btn.disabled = false;
      } catch (err) {
        console.error(err);
        alert('Ocurrió un error al enviar tu confirmación.');
        if (btn) btn.disabled = false;
      }
    });
  }
})();
