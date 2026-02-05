/**
 * Majestic Template - Main JavaScript
 * ================================================
 */

// Initialize AOS (Animate On Scroll)
document.addEventListener('DOMContentLoaded', function() {
    // Init AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }

    // Initialize Countdown
    initCountdown();

    // Initialize Map
    if (EVENT_DATA.venueLat && EVENT_DATA.venueLng) {
        initMap();
    }

    // Smooth scroll for anchor links
    initSmoothScroll();

    // RSVP Form handling
    initRSVPForm();
});

/**
 * Countdown Timer
 * ================================================
 */
function initCountdown() {
    const eventDate = new Date(EVENT_DATA.eventDate).getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = eventDate - now;

        if (distance < 0) {
            document.getElementById('countdownTimer').innerHTML = '<p class="countdown-over">¡El gran día ha llegado!</p>';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const daysEl = document.getElementById('days');
        const hoursEl = document.getElementById('hours');
        const minutesEl = document.getElementById('minutes');
        const secondsEl = document.getElementById('seconds');

        if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
        if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
        if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
        if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
}

/**
 * Interactive Map with Leaflet
 * ================================================
 */
function initMap() {
    const mapElement = document.getElementById('venueMap');
    if (!mapElement || typeof L === 'undefined') return;

    const lat = EVENT_DATA.venueLat;
    const lng = EVENT_DATA.venueLng;
    const venueName = EVENT_DATA.venueName;

    // Initialize map
    const map = L.map('venueMap').setView([lat, lng], 15);

    // Add tile layer (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Custom marker icon
    const customIcon = L.divIcon({
        className: 'custom-map-marker',
        html: '<div style="background: var(--primary-color); color: white; padding: 10px 15px; border-radius: 50px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.3); white-space: nowrap;"><i class="bi bi-geo-alt-fill"></i> ' + venueName + '</div>',
        iconSize: [150, 40],
        iconAnchor: [75, 40]
    });

    // Add marker
    L.marker([lat, lng], { icon: customIcon }).addTo(map);

    // Add circle
    L.circle([lat, lng], {
        color: 'var(--primary-color)',
        fillColor: 'var(--accent-color)',
        fillOpacity: 0.2,
        radius: 200
    }).addTo(map);

    // Disable scroll zoom (for better mobile UX)
    map.scrollWheelZoom.disable();

    // Enable zoom on click
    mapElement.addEventListener('click', function() {
        map.scrollWheelZoom.enable();
    });

    mapElement.addEventListener('mouseleave', function() {
        map.scrollWheelZoom.disable();
    });
}

/**
 * Smooth Scrolling
 * ================================================
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;

            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * RSVP Form Handling
 * ================================================
 */
function initRSVPForm() {
    const form = document.getElementById('rsvpForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;

        // Show loading
        submitButton.innerHTML = '<i class="bi bi-hourglass"></i> Enviando...';
        submitButton.disabled = true;

        // Get form data
        const formData = new FormData(form);

        // Submit via fetch
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                form.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px;">
                        <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: var(--secondary-color);"></i>
                        <h3 style="margin: 20px 0; color: var(--primary-color);">¡Confirmación Recibida!</h3>
                        <p style="font-size: 1.1rem; color: var(--text-dark); opacity: 0.8;">
                            Gracias por confirmar tu asistencia. Te esperamos con mucha alegría.
                        </p>
                    </div>
                `;
            } else {
                throw new Error(data.message || 'Error al enviar la confirmación');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        });
    });
}

/**
 * Parallax Effect for Hero
 * ================================================
 */
window.addEventListener('scroll', function() {
    const hero = document.querySelector('.majestic-hero');
    if (!hero) return;

    const scrolled = window.pageYOffset;
    const parallax = scrolled * 0.5;

    hero.style.transform = `translateY(${parallax}px)`;
});

/**
 * Lazy Loading Images
 * ================================================
 */
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}
