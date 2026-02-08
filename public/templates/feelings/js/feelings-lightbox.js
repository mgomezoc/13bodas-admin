/**
 * FEELINGS TEMPLATE — Custom Lightbox
 * Reemplaza Fancybox v2 (sprites rotas) con un lightbox ligero CSS+JS
 * Versión: 1.0
 */
(function () {
    'use strict';

    // ── State ──────────────────────────────────────────────────────
    var images = [];
    var currentIndex = 0;
    var overlay = null;
    var imgEl = null;
    var counterEl = null;
    var touchStartX = 0;
    var touchEndX = 0;

    // ── Build Overlay DOM ──────────────────────────────────────────
    function createOverlay() {
        overlay = document.createElement('div');
        overlay.className = 'feelings-lightbox-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-label', 'Galería de imágenes');

        // Close button
        var closeBtn = document.createElement('button');
        closeBtn.className = 'feelings-lb-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Cerrar');
        closeBtn.addEventListener('click', close);

        // Prev button
        var prevBtn = document.createElement('button');
        prevBtn.className = 'feelings-lb-nav feelings-lb-prev';
        prevBtn.innerHTML = '&#8249;';
        prevBtn.setAttribute('aria-label', 'Anterior');
        prevBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            navigate(-1);
        });

        // Next button
        var nextBtn = document.createElement('button');
        nextBtn.className = 'feelings-lb-nav feelings-lb-next';
        nextBtn.innerHTML = '&#8250;';
        nextBtn.setAttribute('aria-label', 'Siguiente');
        nextBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            navigate(1);
        });

        // Image
        imgEl = document.createElement('img');
        imgEl.alt = '';
        imgEl.draggable = false;

        // Counter
        counterEl = document.createElement('div');
        counterEl.className = 'feelings-lb-counter';

        overlay.appendChild(closeBtn);
        overlay.appendChild(prevBtn);
        overlay.appendChild(nextBtn);
        overlay.appendChild(imgEl);
        overlay.appendChild(counterEl);

        // Click on overlay background to close
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) close();
        });

        // Touch gestures
        overlay.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        overlay.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                navigate(diff > 0 ? 1 : -1);
            }
        }, { passive: true });

        document.body.appendChild(overlay);
    }

    // ── Open ───────────────────────────────────────────────────────
    function open(index) {
        currentIndex = index;
        showImage();
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // ── Close ──────────────────────────────────────────────────────
    function close() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    // ── Navigate ───────────────────────────────────────────────────
    function navigate(direction) {
        currentIndex = (currentIndex + direction + images.length) % images.length;
        showImage();
    }

    // ── Show Image ─────────────────────────────────────────────────
    function showImage() {
        var src = images[currentIndex];
        // Quick fade out/in
        imgEl.style.opacity = '0';
        imgEl.style.transform = 'scale(0.95)';

        setTimeout(function () {
            imgEl.src = src;
            imgEl.alt = 'Foto ' + (currentIndex + 1) + ' de ' + images.length;
            imgEl.style.opacity = '1';
            imgEl.style.transform = 'scale(1)';
        }, 150);

        counterEl.textContent = (currentIndex + 1) + ' / ' + images.length;
    }

    // ── Keyboard ───────────────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (!overlay || !overlay.classList.contains('active')) return;

        switch (e.key) {
            case 'Escape':
                close();
                break;
            case 'ArrowLeft':
                navigate(-1);
                break;
            case 'ArrowRight':
                navigate(1);
                break;
        }
    });

    // ── Init ───────────────────────────────────────────────────────
    function init() {
        var links = document.querySelectorAll('.fancybox, a[data-fancybox]');
        if (!links.length) return;

        createOverlay();

        // Collect image URLs
        links.forEach(function (link, index) {
            var href = link.getAttribute('href');
            if (href) {
                images.push(href);

                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    open(index);
                });
            }
        });
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
