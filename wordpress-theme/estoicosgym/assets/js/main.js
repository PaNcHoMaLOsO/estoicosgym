/**
 * EstoicosGym Theme - Main JavaScript
 * 
 * @package EstoicosGym
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // ========================================
    // Navigation
    // ========================================
    const nav = {
        init: function() {
            this.header = document.getElementById('header');
            this.navMenu = document.getElementById('nav-menu');
            this.navToggle = document.getElementById('nav-toggle');
            this.navClose = document.getElementById('nav-close');
            this.navLinks = document.querySelectorAll('.nav-link');
            
            this.bindEvents();
            this.handleScroll();
        },
        
        bindEvents: function() {
            // Toggle mobile menu
            if (this.navToggle) {
                this.navToggle.addEventListener('click', () => this.showMenu());
            }
            
            if (this.navClose) {
                this.navClose.addEventListener('click', () => this.hideMenu());
            }
            
            // Close menu on link click
            this.navLinks.forEach(link => {
                link.addEventListener('click', () => this.hideMenu());
            });
            
            // Scroll event
            window.addEventListener('scroll', () => this.handleScroll());
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', (e) => this.smoothScroll(e));
            });
        },
        
        showMenu: function() {
            if (this.navMenu) {
                this.navMenu.classList.add('show');
                document.body.classList.add('menu-open');
            }
        },
        
        hideMenu: function() {
            if (this.navMenu) {
                this.navMenu.classList.remove('show');
                document.body.classList.remove('menu-open');
            }
        },
        
        handleScroll: function() {
            if (this.header) {
                if (window.scrollY > 100) {
                    this.header.classList.add('scrolled');
                } else {
                    this.header.classList.remove('scrolled');
                }
            }
            
            // Update active nav link
            this.updateActiveLink();
        },
        
        updateActiveLink: function() {
            const sections = document.querySelectorAll('section[id]');
            const scrollY = window.pageYOffset;
            
            sections.forEach(section => {
                const sectionHeight = section.offsetHeight;
                const sectionTop = section.offsetTop - 100;
                const sectionId = section.getAttribute('id');
                const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);
                
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    this.navLinks.forEach(link => link.classList.remove('active'));
                    if (navLink) navLink.classList.add('active');
                }
            });
        },
        
        smoothScroll: function(e) {
            const href = e.currentTarget.getAttribute('href');
            
            if (href.startsWith('#') && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                
                if (target) {
                    const headerHeight = this.header ? this.header.offsetHeight : 0;
                    const targetPosition = target.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        }
    };

    // ========================================
    // Stats Counter Animation
    // ========================================
    const statsCounter = {
        init: function() {
            this.counters = document.querySelectorAll('.stat-number[data-count]');
            
            if (this.counters.length > 0) {
                this.observeCounters();
            }
        },
        
        observeCounters: function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            this.counters.forEach(counter => observer.observe(counter));
        },
        
        animateCounter: function(counter) {
            const target = parseInt(counter.getAttribute('data-count'));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const updateCounter = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target;
                }
            };
            
            updateCounter();
        }
    };

    // ========================================
    // Contact Form
    // ========================================
    const contactForm = {
        init: function() {
            this.form = document.getElementById('contact-form');
            this.message = document.getElementById('form-message');
            
            if (this.form) {
                this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            }
        },
        
        handleSubmit: function(e) {
            e.preventDefault();
            
            const formData = new FormData(this.form);
            formData.append('action', 'estoicosgym_contact');
            formData.append('nonce', estoicosgym_ajax.nonce);
            
            const submitBtn = this.form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            fetch(estoicosgym_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showMessage(data.data.message, 'success');
                    this.form.reset();
                } else {
                    this.showMessage(data.data.message || 'Error al enviar el mensaje.', 'error');
                }
            })
            .catch(error => {
                this.showMessage('Error de conexión. Por favor intenta de nuevo.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        },
        
        showMessage: function(text, type) {
            if (this.message) {
                this.message.textContent = text;
                this.message.className = `form-message ${type}`;
                this.message.style.display = 'block';
                
                setTimeout(() => {
                    this.message.style.display = 'none';
                }, 5000);
            }
        }
    };

    // ========================================
    // Back to Top Button
    // ========================================
    const backToTop = {
        init: function() {
            this.button = document.getElementById('back-to-top');
            
            if (this.button) {
                this.bindEvents();
            }
        },
        
        bindEvents: function() {
            window.addEventListener('scroll', () => this.toggleVisibility());
            this.button.addEventListener('click', (e) => this.scrollToTop(e));
        },
        
        toggleVisibility: function() {
            if (window.scrollY > 500) {
                this.button.classList.add('visible');
            } else {
                this.button.classList.remove('visible');
            }
        },
        
        scrollToTop: function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    };

    // ========================================
    // Testimonials Slider
    // ========================================
    const testimonialSlider = {
        init: function() {
            this.slider = document.querySelector('.testimonials-slider');
            
            if (this.slider && this.slider.children.length > 1) {
                this.setupSlider();
            }
        },
        
        setupSlider: function() {
            // Simple auto-scroll for testimonials
            const cards = this.slider.querySelectorAll('.testimonial-card');
            let currentIndex = 0;
            
            if (cards.length > 3) {
                setInterval(() => {
                    currentIndex = (currentIndex + 1) % (cards.length - 2);
                    this.slider.scrollTo({
                        left: cards[currentIndex].offsetLeft - 20,
                        behavior: 'smooth'
                    });
                }, 5000);
            }
        }
    };

    // ========================================
    // Gallery Lightbox
    // ========================================
    const gallery = {
        init: function() {
            this.links = document.querySelectorAll('.gallery-link[data-lightbox]');
            
            if (this.links.length > 0) {
                this.createLightbox();
                this.bindEvents();
            }
        },
        
        createLightbox: function() {
            const lightbox = document.createElement('div');
            lightbox.className = 'lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-overlay"></div>
                <div class="lightbox-content">
                    <img src="" alt="Gallery Image" class="lightbox-image">
                    <button class="lightbox-close"><i class="fas fa-times"></i></button>
                    <button class="lightbox-prev"><i class="fas fa-chevron-left"></i></button>
                    <button class="lightbox-next"><i class="fas fa-chevron-right"></i></button>
                </div>
            `;
            document.body.appendChild(lightbox);
            
            this.lightbox = lightbox;
            this.image = lightbox.querySelector('.lightbox-image');
        },
        
        bindEvents: function() {
            this.links.forEach((link, index) => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.currentIndex = index;
                    this.open(link.href);
                });
            });
            
            this.lightbox.querySelector('.lightbox-overlay').addEventListener('click', () => this.close());
            this.lightbox.querySelector('.lightbox-close').addEventListener('click', () => this.close());
            this.lightbox.querySelector('.lightbox-prev').addEventListener('click', () => this.prev());
            this.lightbox.querySelector('.lightbox-next').addEventListener('click', () => this.next());
            
            document.addEventListener('keydown', (e) => {
                if (this.lightbox.classList.contains('active')) {
                    if (e.key === 'Escape') this.close();
                    if (e.key === 'ArrowLeft') this.prev();
                    if (e.key === 'ArrowRight') this.next();
                }
            });
        },
        
        open: function(src) {
            this.image.src = src;
            this.lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        },
        
        close: function() {
            this.lightbox.classList.remove('active');
            document.body.style.overflow = '';
        },
        
        prev: function() {
            this.currentIndex = (this.currentIndex - 1 + this.links.length) % this.links.length;
            this.image.src = this.links[this.currentIndex].href;
        },
        
        next: function() {
            this.currentIndex = (this.currentIndex + 1) % this.links.length;
            this.image.src = this.links[this.currentIndex].href;
        }
    };

    // ========================================
    // Scroll Reveal Animations
    // ========================================
    const scrollReveal = {
        init: function() {
            this.elements = document.querySelectorAll('.service-card, .membership-card, .testimonial-card, .contact-form-wrapper, .contact-info-wrapper');
            
            if (this.elements.length > 0) {
                this.observe();
            }
        },
        
        observe: function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('revealed');
                        }, index * 100);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            this.elements.forEach(el => {
                el.classList.add('reveal-element');
                observer.observe(el);
            });
        }
    };

    // ========================================
    // Initialize
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        nav.init();
        statsCounter.init();
        contactForm.init();
        backToTop.init();
        testimonialSlider.init();
        gallery.init();
        scrollReveal.init();
    });

})(jQuery);
