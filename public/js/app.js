/**
 * 13Bodas - Main JavaScript
 * Version: 2.0
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
        navLinks.forEach(link => {
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
        
        scrollLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const targetId = link.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    
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
        
        faqItems.forEach(item => {
            const summary = item.querySelector('summary');
            
            if (summary) {
                summary.addEventListener('click', (e) => {
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
        this.aosElements.forEach(element => {
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
        this.aosElements.forEach((el, index) => {
            setTimeout(() => {
                el.classList.add('aos-animate');
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

        heroElements.forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
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
