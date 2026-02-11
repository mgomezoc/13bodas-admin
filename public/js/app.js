/**
 * 13Bodas - Main JavaScript
 * Version: 3.0
 */

document.addEventListener('DOMContentLoaded', () => {
    const app = new App13Bodas();
    app.init();
});

class App13Bodas {
    constructor() {
        // Navigation
        this.header = document.getElementById('header');
        this.navToggle = document.getElementById('navToggle');
        this.navMenu = document.getElementById('navMenu');

        // Hero elements
        this.heroSection = document.querySelector('.hero');
        this.heroBadge = document.querySelector('.hero-badge');
        this.heroTitle = document.querySelector('.hero-title');
        this.heroDescription = document.querySelector('.hero-description');
        this.heroStats = document.querySelector('.hero-stats');
        this.heroCta = document.querySelector('.hero-cta');
        this.heroVisual = document.querySelector('.hero-visual');
        this.scrollIndicator = document.querySelector('.scroll-indicator');

        this.lastTrackedSection = '';

        // All elements with data-aos attribute
        this.aosElements = document.querySelectorAll('[data-aos]');
    }

    init() {
        // Mark body as JS loaded
        document.body.classList.add('js-loaded');

        // Initialize components
        this.initNavigation();
        this.initSmoothScroll();
        this.initFAQ();
        this.initAnalytics();

        // Initialize animations
        if (typeof gsap !== 'undefined') {
            document.body.classList.add('gsap-loaded');

            if (typeof ScrollTrigger !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);
            }

            this.initGSAPAnimations();
        } else {
            // Fallback without GSAP
            this.initFallbackAnimations();
        }
    }

    // ==================================
    // ANALYTICS
    // ==================================
    initAnalytics() {
        this.initCookieConsentBanner();
        this.initCtaTracking();
        this.initPackageTracking();
        this.initFormTracking();
        this.initSocialTracking();
        this.initOutboundAndDownloadTracking();
        this.initSectionTracking();
        this.initArTracking();
    }

    trackEvent(eventName, params = {}) {
        if (typeof window.gtag !== 'function') {
            return;
        }

        window.gtag('event', eventName, {
            event_category: 'engagement',
            debug_mode: Boolean(window.__gaDebugMode),
            ...params
        });
    }

    initCookieConsentBanner() {
        const banner = document.getElementById('cookieBanner');
        const acceptButton = document.getElementById('cookieAcceptBtn');
        const rejectButton = document.getElementById('cookieRejectBtn');
        const deleteButton = document.getElementById('cookieDeleteBtn');

        if (!banner || !acceptButton || !rejectButton || !deleteButton || typeof window.gtag !== 'function') {
            return;
        }

        const consent = this.getStoredConsent();
        if (!consent) {
            banner.hidden = false;
        }

        acceptButton.addEventListener('click', () => {
            this.updateConsent('granted');
            banner.hidden = true;
        });

        rejectButton.addEventListener('click', () => {
            this.updateConsent('denied');
            banner.hidden = true;
        });

        deleteButton.addEventListener('click', () => {
            this.deleteAnalyticsData();
            banner.hidden = false;
        });
    }

    getStoredConsent() {
        try {
            return localStorage.getItem('13bodas_cookie_consent');
        } catch (error) {
            return null;
        }
    }

    updateConsent(consentState) {
        const isGranted = consentState === 'granted';

        window.gtag('consent', 'update', {
            analytics_storage: isGranted ? 'granted' : 'denied',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied'
        });

        try {
            localStorage.setItem('13bodas_cookie_consent', consentState);
        } catch (error) {
            // ignore storage errors
        }

        this.trackEvent('cookie_consent_update', {
            consent_state: consentState,
            event_category: 'privacy'
        });
    }

    deleteAnalyticsData() {
        this.updateConsent('denied');

        try {
            localStorage.removeItem('13bodas_cookie_consent');
        } catch (error) {
            // ignore storage errors
        }

        const host = window.location.hostname;
        document.cookie = `_ga=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.${host};`;
        document.cookie = `_ga_${String(window.__gaMeasurementId || '').replace('G-', '')}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.${host};`;

        this.trackEvent('analytics_data_deleted', {
            event_category: 'privacy'
        });
    }

    initCtaTracking() {
        const ctaButtons = document.querySelectorAll('[data-track-cta]');

        ctaButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                const target = event.currentTarget;
                this.trackEvent('cta_click', {
                    cta_name: target.dataset.trackCta || 'unknown',
                    cta_position: target.dataset.position || 'unknown',
                    event_category: 'engagement'
                });
            });
        });
    }

    initPackageTracking() {
        const packageButtons = document.querySelectorAll('[data-package-type]');

        packageButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                const packageName = event.currentTarget.dataset.packageType;
                const packageValue = packageName === 'infinity' ? 1 : 0;

                this.trackEvent('select_package', {
                    package_type: packageName,
                    event_category: 'conversion',
                    value: packageValue
                });
            });
        });
    }

    initFormTracking() {
        const form = document.getElementById('contactForm');

        if (!form) {
            return;
        }

        form.addEventListener('submit', () => {
            const packageSelect = document.getElementById('paquete');

            this.trackEvent('form_submit', {
                form_type: 'contact',
                package_type: packageSelect && packageSelect.value ? packageSelect.value : 'unknown',
                event_category: 'lead'
            });
        });
    }

    initSocialTracking() {
        const socialLinks = document.querySelectorAll('[data-social]');

        socialLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const target = event.currentTarget;
                this.trackEvent('social_click', {
                    social_network: target.dataset.social || 'unknown',
                    event_category: 'engagement'
                });
            });
        });
    }

    initOutboundAndDownloadTracking() {
        document.querySelectorAll('a[href]').forEach((link) => {
            const href = link.getAttribute('href') || '';

            if (/\.(pdf|doc|docx|xlsx|zip)$/i.test(href)) {
                link.addEventListener('click', () => {
                    this.trackEvent('file_download', {
                        file_name: href.split('/').pop() || href,
                        file_extension: href.split('.').pop() || 'unknown',
                        event_category: 'engagement'
                    });
                });
            }

            if (href.startsWith('http')) {
                try {
                    const url = new URL(href);

                    if (url.hostname !== window.location.hostname) {
                        link.addEventListener('click', () => {
                            this.trackEvent('click', {
                                link_url: href,
                                outbound: true,
                                event_category: 'engagement'
                            });
                        });
                    }
                } catch (error) {
                    // ignore invalid url
                }
            }
        });
    }

    initSectionTracking() {
        const trackedSections = ['inicio', 'servicios', 'magiccam', 'paquetes', 'proceso', 'faq', 'contacto'];

        document.querySelectorAll('[data-track-nav]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const target = event.currentTarget.dataset.trackNav;
                this.trackEvent('section_navigation', {
                    section_id: target,
                    event_category: 'engagement'
                });
            });
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && this.lastTrackedSection !== entry.target.id) {
                    this.lastTrackedSection = entry.target.id;
                    this.trackEvent('section_view', {
                        section_id: entry.target.id,
                        event_category: 'engagement'
                    });

                    window.gtag('event', 'page_view', {
                        page_title: document.title,
                        page_location: `${window.location.origin}${window.location.pathname}#${entry.target.id}`,
                        page_path: `${window.location.pathname}#${entry.target.id}`,
                        debug_mode: Boolean(window.__gaDebugMode)
                    });
                }
            });
        }, {
            root: null,
            threshold: 0.55
        });

        trackedSections.forEach((sectionId) => {
            const section = document.getElementById(sectionId);
            if (section) {
                observer.observe(section);
            }
        });

        let scrollTracked = false;
        window.addEventListener('scroll', () => {
            if (scrollTracked) {
                return;
            }

            const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (totalHeight <= 0) {
                return;
            }

            const currentDepth = Math.round((window.scrollY / totalHeight) * 100);
            if (currentDepth >= 90) {
                scrollTracked = true;
                this.trackEvent('scroll', {
                    percent_scrolled: 90,
                    event_category: 'engagement'
                });
            }
        }, { passive: true });
    }

    initArTracking() {
        document.querySelectorAll('[data-ar-interaction="open"]').forEach((element) => {
            element.addEventListener('click', () => {
                this.trackEvent('ar_demo_interaction', {
                    ar_demo_interaction: 'open',
                    event_category: 'engagement'
                });
            });
        });

        window.addEventListener('message', (event) => {
            const message = event.data;

            if (!message || typeof message !== 'object') {
                return;
            }

            if (message.source !== '8thwall-analytics') {
                return;
            }

            const interaction = message.action || 'interact';
            if (!['open', 'close', 'interact'].includes(interaction)) {
                return;
            }

            this.trackEvent('ar_demo_interaction', {
                ar_demo_interaction: interaction,
                event_category: 'engagement'
            });
        });
    }

    // ==================================
    // NAVIGATION
    // ==================================
    initNavigation() {
        // Mobile menu toggle
        if (this.navToggle && this.navMenu) {
            this.navToggle.addEventListener('click', () => {
                const isExpanded = this.navToggle.getAttribute('aria-expanded') === 'true';
                this.navToggle.setAttribute('aria-expanded', !isExpanded);
                this.navMenu.classList.toggle('is-open');
                this.navToggle.classList.toggle('is-open');
            });
        }

        // Header scroll effect
        window.addEventListener('scroll', () => {
            if (this.header) {
                if (window.scrollY > 50) {
                    this.header.classList.add('scrolled');
                } else {
                    this.header.classList.remove('scrolled');
                }
            }
        });

        // Close menu on link click (mobile)
        const navLinks = document.querySelectorAll('.nav-link, .nav-cta');
        navLinks.forEach((link) => {
            link.addEventListener('click', () => {
                if (this.navMenu && this.navMenu.classList.contains('is-open')) {
                    this.navMenu.classList.remove('is-open');
                    this.navToggle.classList.remove('is-open');
                    this.navToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    // ==================================
    // SMOOTH SCROLL
    // ==================================
    initSmoothScroll() {
        const scrollLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');

        scrollLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const targetId = link.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    event.preventDefault();

                    const headerHeight = this.header ? this.header.offsetHeight : 0;
                    const targetPosition = targetElement.getBoundingClientRect().top + window.scrollY - headerHeight - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // ==================================
    // FAQ ACCORDION
    // ==================================
    initFAQ() {
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach((item) => {
            const summary = item.querySelector('summary');

            if (summary) {
                summary.addEventListener('click', () => {
                    // Allow default details behavior
                });
            }
        });
    }

    // ==================================
    // GSAP ANIMATIONS
    // ==================================
    initGSAPAnimations() {
        // Hero animation timeline
        this.animateHero();

        // Scroll-triggered animations
        this.animateSections();
    }

    animateHero() {
        if (!this.heroSection) return;

        const heroElements = [
            this.heroBadge,
            this.heroTitle,
            this.heroDescription,
            this.heroStats,
            this.heroCta,
            this.heroVisual,
            this.scrollIndicator
        ].filter(Boolean);

        // Set initial state
        gsap.set(heroElements, {
            opacity: 0,
            y: 30
        });

        // Create timeline
        const tl = gsap.timeline({
            defaults: {
                duration: 0.8,
                ease: 'power3.out'
            }
        });

        // Animate each element
        if (this.heroBadge) {
            tl.to(this.heroBadge, { opacity: 1, y: 0 }, 0.2);
        }

        if (this.heroTitle) {
            tl.to(this.heroTitle, { opacity: 1, y: 0 }, '-=0.5');
        }

        if (this.heroDescription) {
            tl.to(this.heroDescription, { opacity: 1, y: 0 }, '-=0.5');
        }

        if (this.heroStats) {
            tl.to(this.heroStats, { opacity: 1, y: 0 }, '-=0.4');
        }

        if (this.heroCta) {
            tl.to(this.heroCta, { opacity: 1, y: 0 }, '-=0.4');
        }

        if (this.heroVisual) {
            tl.to(this.heroVisual, {
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 1
            }, '-=0.6');
        }

        if (this.scrollIndicator) {
            tl.to(this.scrollIndicator, { opacity: 1, y: 0 }, '-=0.3');
        }
    }

    animateSections() {
        if (typeof ScrollTrigger === 'undefined') {
            // If no ScrollTrigger, just show all elements
            this.initFallbackAnimations();
            return;
        }

        // Animate all data-aos elements on scroll
        this.aosElements.forEach((element) => {
            // Skip hero elements (already animated)
            if (this.heroSection && this.heroSection.contains(element)) {
                return;
            }

            gsap.set(element, {
                opacity: 0,
                y: 30
            });

            gsap.to(element, {
                opacity: 1,
                y: 0,
                duration: 0.7,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: element,
                    start: 'top 85%',
                    once: true
                }
            });
        });

        // Staggered animations for grids
        this.animateGrid('.services-grid', '.service-card');
        this.animateGrid('.packages-grid', '.package-card');
        this.animateGrid('.process-timeline', '.process-step');
        this.animateGrid('.faq-list', '.faq-item');
    }

    animateGrid(containerSelector, itemSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        const items = container.querySelectorAll(itemSelector);
        if (!items.length) return;

        gsap.set(items, {
            opacity: 0,
            y: 40
        });

        gsap.to(items, {
            opacity: 1,
            y: 0,
            duration: 0.6,
            stagger: 0.15,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: container,
                start: 'top 80%',
                once: true
            }
        });
    }

    // ==================================
    // FALLBACK (No GSAP)
    // ==================================
    initFallbackAnimations() {
        // Simply show all elements with data-aos
        this.aosElements.forEach((element, index) => {
            setTimeout(() => {
                element.classList.add('aos-animate');
            }, index * 100);
        });

        // Show hero elements immediately
        const heroElements = [
            this.heroBadge,
            this.heroTitle,
            this.heroDescription,
            this.heroStats,
            this.heroCta,
            this.heroVisual,
            this.scrollIndicator
        ].filter(Boolean);

        heroElements.forEach((element) => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        });
    }
}

// Year in footer
document.addEventListener('DOMContentLoaded', () => {
    const yearEl = document.getElementById('year');
    if (yearEl) {
        yearEl.textContent = new Date().getFullYear();
    }
});
