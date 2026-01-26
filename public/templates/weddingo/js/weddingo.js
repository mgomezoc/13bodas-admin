(function () {
  'use strict';

  const state = window.__WEDDINGO__ || {};
  const qs = (s, el=document) => el.querySelector(s);
  const qsa = (s, el=document) => Array.from(el.querySelectorAll(s));

  // Mobile nav
  const burger = qs('[data-w-burger]');
  const nav = qs('[data-w-nav]');
  if (burger && nav) {
    burger.addEventListener('click', () => nav.classList.toggle('is-open'));
    qsa('a', nav).forEach(a => a.addEventListener('click', () => nav.classList.remove('is-open')));
  }

  // Reveal animations
  const revealEls = qsa('[data-reveal]');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('is-revealed');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(el => io.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('is-revealed'));
  }

  // Countdown
  const elDays = qs('[data-cd-days]');
  const elHours = qs('[data-cd-hours]');
  const elMin = qs('[data-cd-min]');
  const elSec = qs('[data-cd-sec]');

  function pad(n) { return String(n).padStart(2, '0'); }

  function startCountdown(iso) {
    if (!iso) return;
    const target = new Date(iso).getTime();
    if (!Number.isFinite(target)) return;

    function tick() {
      const now = Date.now();
      let diff = Math.max(0, target - now);

      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      diff -= days * (1000 * 60 * 60 * 24);
      const hours = Math.floor(diff / (1000 * 60 * 60));
      diff -= hours * (1000 * 60 * 60);
      const mins = Math.floor(diff / (1000 * 60));
      diff -= mins * (1000 * 60);
      const secs = Math.floor(diff / 1000);

      if (elDays) elDays.textContent = days;
      if (elHours) elHours.textContent = pad(hours);
      if (elMin) elMin.textContent = pad(mins);
      if (elSec) elSec.textContent = pad(secs);

      if (target - now <= 0) clearInterval(timer);
    }

    tick();
    const timer = setInterval(tick, 1000);
  }
  startCountdown(state.eventDateISO);

  // RSVP (AJAX)
  const form = qs('#rsvp-form');
  if (form) {
    const loader = qs('[data-rsvp-loader]', form);
    const ok = qs('[data-rsvp-ok]', form);
    const err = qs('[data-rsvp-err]', form);

    function show(el, msg) {
      if (!el) return;
      el.textContent = msg || '';
      el.hidden = false;
    }
    function hide(el) { if (el) el.hidden = true; }
    function setLoading(on) { if (loader) loader.hidden = !on; }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      hide(ok); hide(err);

      const url = form.getAttribute('action') || state.rsvpUrl;
      if (!url) { show(err, 'No hay endpoint de RSVP configurado.'); return; }

      const fd = new FormData(form);

      setLoading(true);
      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        });
        const data = await res.json().catch(() => null);

        if (data && data.success) {
          show(ok, data.message || 'Confirmación registrada. ¡Gracias!');
          form.reset();
        } else {
          show(err, (data && data.message) ? data.message : 'No fue posible registrar tu confirmación.');
        }
      } catch (_) {
        show(err, 'No fue posible registrar tu confirmación.');
      } finally {
        setLoading(false);
      }
    });
  }
})();
