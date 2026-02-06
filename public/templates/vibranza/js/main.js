document.addEventListener('DOMContentLoaded', () => {
  const countdownEl = document.querySelector('.countdown');
  if (!countdownEl) return;

  const targetDate = countdownEl.dataset.date;
  if (!targetDate) return;

  const target = new Date(targetDate);
  if (Number.isNaN(target.getTime())) return;

  const formatCountdown = () => {
    const now = new Date();
    const diff = target.getTime() - now.getTime();

    if (diff <= 0) {
      countdownEl.textContent = '¡Hoy celebramos!';
      return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((diff / (1000 * 60)) % 60);

    countdownEl.textContent = `Faltan ${days} días · ${hours}h ${minutes}m`;
  };

  formatCountdown();
  setInterval(formatCountdown, 60000);
});
