(function() {
  'use strict';

  const header = document.querySelector('[data-header]');
  const nav = document.querySelector('[data-nav]');
  const toggle = document.querySelector('[data-nav-toggle]');

  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  const onScroll = () => {
    if (!header) return;
    if (window.scrollY > 60) header.classList.add('is-scrolled');
    else header.classList.remove('is-scrolled');
  };
  window.addEventListener('scroll', onScroll);
  onScroll();

  document.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (e) => {
      const targetId = link.getAttribute('href');
      const target = document.querySelector(targetId);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
      if (nav) nav.classList.remove('is-open');
    });
  });

  document.querySelectorAll('[data-parallax]').forEach((section) => {
    const speed = 0.3;
    const onMove = () => {
      const offset = window.scrollY * speed;
      section.style.backgroundPosition = `center calc(50% + ${offset}px)`;
    };
    window.addEventListener('scroll', onMove);
    onMove();
  });

  document.querySelectorAll('[data-countdown]').forEach((node) => {
    const target = node.getAttribute('data-countdown');
    if (!target) return;
    const targetDate = new Date(target);

    const update = () => {
      const now = new Date();
      if (targetDate <= now) return;

      let months = (targetDate.getFullYear() - now.getFullYear()) * 12 + (targetDate.getMonth() - now.getMonth());
      let anchor = new Date(now.getFullYear(), now.getMonth() + months, now.getDate(), now.getHours(), now.getMinutes(), now.getSeconds());
      if (anchor > targetDate) {
        months -= 1;
        anchor = new Date(now.getFullYear(), now.getMonth() + months, now.getDate(), now.getHours(), now.getMinutes(), now.getSeconds());
      }

      const diffMs = targetDate - anchor;
      const diff = Math.max(0, Math.floor(diffMs / 1000));
      const days = Math.floor(diff / 86400);
      const hours = Math.floor((diff % 86400) / 3600);
      const minutes = Math.floor((diff % 3600) / 60);
      const seconds = diff % 60;

      const set = (key, val) => {
        const el = node.querySelector(`[data-count="${key}"]`);
        if (el) el.textContent = String(val).padStart(2, '0');
      };

      set('months', months);
      set('days', days);
      set('hours', hours);
      set('minutes', minutes);
      set('seconds', seconds);
    };

    update();
    setInterval(update, 1000);
  });

  const lazyImages = document.querySelectorAll('[data-src]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const img = entry.target;
        if (img.dataset.src) img.src = img.dataset.src;
        obs.unobserve(img);
      });
    }, { rootMargin: '150px' });

    lazyImages.forEach((img) => observer.observe(img));
  }

  const form = document.querySelector('[data-rsvp-form]');
  const status = document.querySelector('[data-rsvp-status]');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (status) status.textContent = 'Enviando...';

      const formData = new FormData(form);

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data && data.success) {
          if (status) status.textContent = data.message || '¡Gracias por confirmar!';
          form.reset();
        } else {
          if (status) status.textContent = data.message || 'No fue posible enviar la confirmación.';
        }
      } catch (err) {
        if (status) status.textContent = 'Ocurrió un error. Intenta nuevamente.';
      }
    });
  }
})();
