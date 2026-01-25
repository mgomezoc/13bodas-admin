(function () {
  'use strict';

  function pad2(n) { return String(n).padStart(2, '0'); }

  function tick(el) {
    const target = el.getAttribute('data-target');
    if (!target) return;

    const targetDate = new Date(target);
    if (isNaN(targetDate.getTime())) return;

    const now = new Date();
    let diff = targetDate.getTime() - now.getTime();
    if (diff < 0) diff = 0;

    const s = Math.floor(diff / 1000);
    const days = Math.floor(s / 86400);
    const hours = Math.floor((s % 86400) / 3600);
    const minutes = Math.floor((s % 3600) / 60);
    const seconds = Math.floor(s % 60);

    const daysEl = el.querySelector('[data-part="days"]');
    const hoursEl = el.querySelector('[data-part="hours"]');
    const minsEl = el.querySelector('[data-part="minutes"]');
    const secsEl = el.querySelector('[data-part="seconds"]');

    if (daysEl) daysEl.textContent = String(days);
    if (hoursEl) hoursEl.textContent = pad2(hours);
    if (minsEl) minsEl.textContent = pad2(minutes);
    if (secsEl) secsEl.textContent = pad2(seconds);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const blocks = document.querySelectorAll('[data-solene-countdown]');
    if (!blocks.length) return;

    blocks.forEach(tick);
    setInterval(() => blocks.forEach(tick), 1000);
  });
})();
