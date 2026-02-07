(function () {
  'use strict';

  const cfg = window.SUKUN || {};
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  const header = $('[data-header]');
  const nav = $('[data-nav]');
  const navToggle = $('[data-nav-toggle]');

  if (navToggle && nav) {
    navToggle.addEventListener('click', () => {
      nav.classList.toggle('is-open');
      navToggle.classList.toggle('is-open');
      document.body.classList.toggle('is-locked', nav.classList.contains('is-open'));
    });
  }

  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href^="#"]');
    if (!link) return;
    const id = link.getAttribute('href');
    const target = $(id);
    if (!target) return;
    event.preventDefault();
    const offset = header ? header.offsetHeight : 0;
    const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top, behavior: 'smooth' });
    if (nav && nav.classList.contains('is-open')) {
      nav.classList.remove('is-open');
      navToggle?.classList.remove('is-open');
      document.body.classList.remove('is-locked');
    }
  });

  const countdownEl = $('[data-countdown]');
  if (countdownEl) {
    const targetIso = countdownEl.dataset.countdown || cfg.eventStartIso;
    const target = targetIso ? new Date(targetIso) : null;
    const parts = {
      days: $('[data-count="days"]', countdownEl),
      hours: $('[data-count="hours"]', countdownEl),
      minutes: $('[data-count="minutes"]', countdownEl),
      seconds: $('[data-count="seconds"]', countdownEl)
    };

    const pad = (num) => String(num).padStart(2, '0');

    const tick = () => {
      if (!target || isNaN(target.getTime())) return;
      const diff = target.getTime() - Date.now();
      if (diff <= 0) {
        Object.values(parts).forEach((el) => {
          if (el) el.textContent = '00';
        });
        if (parts.days) parts.days.textContent = '0';
        return;
      }
      const s = Math.floor(diff / 1000);
      const days = Math.floor(s / 86400);
      const hours = Math.floor((s % 86400) / 3600);
      const mins = Math.floor((s % 3600) / 60);
      const secs = s % 60;
      if (parts.days) parts.days.textContent = String(days);
      if (parts.hours) parts.hours.textContent = pad(hours);
      if (parts.minutes) parts.minutes.textContent = pad(mins);
      if (parts.seconds) parts.seconds.textContent = pad(secs);
    };

    tick();
    setInterval(tick, 1000);
  }

  const revealEls = $$('.sk-reveal');
  if (revealEls.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    revealEls.forEach((el) => observer.observe(el));
  }

  const tabs = $('[data-tabs]');
  if (tabs) {
    const buttons = $$('[data-tab]', tabs);
    const contents = $$('[data-tab-content]', tabs);

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        const target = button.dataset.tab;
        buttons.forEach((btn) => btn.classList.toggle('sk-tab--active', btn === button));
        contents.forEach((content) => {
          content.classList.toggle('sk-tab-content--active', content.dataset.tabContent === target);
        });
      });
    });
  }

  const rsvpForm = $('#rsvp-form');
  if (rsvpForm && cfg.rsvpUrl) {
    rsvpForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      const status = $('[data-status]', rsvpForm);
      const submit = $('[data-submit]', rsvpForm);
      if (submit) submit.disabled = true;
      if (status) status.textContent = 'Enviando...';

      try {
        const response = await fetch(cfg.rsvpUrl, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(rsvpForm)
        });
        const data = await response.json().catch(() => null);
        if (!data || !data.success) {
          if (status) status.textContent = data?.message || 'No fue posible guardar tu confirmación.';
          if (submit) submit.disabled = false;
          return;
        }
        if (status) status.textContent = data.message || 'Confirmación registrada. ¡Gracias!';
        rsvpForm.reset();
        if (submit) submit.disabled = false;
      } catch (error) {
        console.error(error);
        if (status) status.textContent = 'Ocurrió un error al enviar tu confirmación.';
        if (submit) submit.disabled = false;
      }
    });
  }

  const lazyImages = $$('img[data-src]');
  if (lazyImages.length) {
    const lazyObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const img = entry.target;
        img.src = img.dataset.src;
        img.onload = () => img.classList.add('is-loaded');
        observer.unobserve(img);
      });
    }, { rootMargin: '200px' });

    lazyImages.forEach((img) => lazyObserver.observe(img));
  }

  const lightbox = $('[data-lightbox-overlay]');
  const lightboxImage = $('[data-lightbox-image]');
  const lightboxItems = $$('[data-lightbox-item]');
  let currentIndex = 0;

  function openLightbox(index) {
    if (!lightbox || !lightboxImage) return;
    const item = lightboxItems[index];
    if (!item) return;
    const src = item.dataset.full;
    lightboxImage.src = src;
    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    currentIndex = index;
  }

  function closeLightbox() {
    if (!lightbox || !lightboxImage) return;
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    lightboxImage.src = '';
  }

  function navigateLightbox(step) {
    if (!lightboxItems.length) return;
    currentIndex = (currentIndex + step + lightboxItems.length) % lightboxItems.length;
    openLightbox(currentIndex);
  }

  lightboxItems.forEach((item, index) => {
    item.addEventListener('click', () => openLightbox(index));
  });

  $('[data-lightbox-close]')?.addEventListener('click', closeLightbox);
  $('[data-lightbox-prev]')?.addEventListener('click', () => navigateLightbox(-1));
  $('[data-lightbox-next]')?.addEventListener('click', () => navigateLightbox(1));

  if (lightbox) {
    lightbox.addEventListener('click', (event) => {
      if (event.target === lightbox) closeLightbox();
    });
  }

  document.addEventListener('keydown', (event) => {
    if (!lightbox || !lightbox.classList.contains('is-open')) return;
    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowLeft') navigateLightbox(-1);
    if (event.key === 'ArrowRight') navigateLightbox(1);
  });
})();
