/**
 * Main theme JavaScript
 * @package tripeak-test-seven
 */

(function($) {
    'use strict';

    // Wait for DOM to be ready
    $(document).ready(function() {
        
        // Initialize all functions
        initSmoothScrolling();
        initHeaderScroll();
        initSearchToggle();
        initLoadMore();
        initImageLazyLoading();
        initAccessibility();
        initPerformanceOptimizations();
        initDropdownMenus(); // Add dropdown menu functionality
        initCategoryFilter(); // Add category filter functionality
        
    });

    /**
     * Category Filter Functionality
     * Category buttons are now standard links that navigate to category archive pages.
     * The active state is set server-side based on the current category.
     */
    function initCategoryFilter() {
        // Category buttons work as normal links to category archive URLs
        // No JavaScript interception needed - let the href attributes work naturally
        // The active class is already set by PHP based on current category
    }

    /**
     * Dropdown Menu Functionality
     * Handles mobile dropdown menu toggling
     */
    function initDropdownMenus() {
        const menuItems = $('.main-navigation .menu-item-has-children');
        
        if (!menuItems.length) return;
        
        // Mobile click handler - toggle dropdowns
        menuItems.each(function() {
            const menuItem = $(this);
            const menuLink = menuItem.children('a').first();
            const submenu = menuItem.children('ul, .sub-menu').first();
            
            if (!menuLink.length || !submenu.length) return;
            
            menuLink.on('click', function(e) {
                // Only handle on mobile viewports (≤768px)
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    
                    // Toggle current dropdown
                    const isOpen = menuItem.hasClass('submenu-open');
                    
                    // Close all other dropdowns in the same menu
                    menuItems.not(menuItem).removeClass('submenu-open');
                    
                    // Toggle current item
                    menuItem.toggleClass('submenu-open', !isOpen);
                }
            });
        });
        
        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (window.innerWidth <= 768 && !$(e.target).closest('.main-navigation .menu-item-has-children').length) {
                menuItems.removeClass('submenu-open');
            }
        });
        
        // Clean up mobile classes when switching to desktop
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 768) {
                    menuItems.removeClass('submenu-open');
                }
            }, 250);
        });
    }

    /**
     * Smooth Scrolling for Anchor Links
     */
    function initSmoothScrolling() {
        $('a[href*="#"]:not([href="#"])').on('click', function(e) {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && 
                location.hostname == this.hostname) {
                
                let target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100 // Account for fixed header
                    }, 800);
                }
            }
        });
    }

    /**
     * Header Scroll Effects
     */
    function initHeaderScroll() {
        const header = $('.site-header');
        let lastScrollTop = 0;
        
        $(window).on('scroll', function() {
            const scrollTop = $(this).scrollTop();
            
            // Add scrolled class for styling
            if (scrollTop > 100) {
                header.addClass('scrolled');
            } else {
                header.removeClass('scrolled');
            }
            
            // Hide/show header on scroll (optional)
            if (scrollTop > lastScrollTop && scrollTop > 200) {
                // Scrolling down
                header.addClass('header-hidden');
            } else {
                // Scrolling up
                header.removeClass('header-hidden');
            }
            
            lastScrollTop = scrollTop;
        });
    }

    /**
     * Search Toggle Functionality
     */
    function initSearchToggle() {
        const searchToggle = $('.search-toggle');
        const searchForm = $('.search-form-container');
        
        searchToggle.on('click', function(e) {
            e.preventDefault();
            searchForm.slideToggle(300);
            searchForm.find('input[type="search"]').focus();
        });
        
        // Close search when clicking outside
        $(document).on('click', function(event) {
            if (!searchForm.is(event.target) && 
                !searchToggle.is(event.target) && 
                searchForm.has(event.target).length === 0 && 
                searchToggle.has(event.target).length === 0) {
                
                if (searchForm.is(':visible')) {
                    searchForm.slideUp(300);
                }
            }
        });
    }

    /**
     * Load More Functionality (if needed for infinite scroll)
     */
    function initLoadMore() {
        const loadMoreBtn = $('.load-more-btn');
        
        loadMoreBtn.on('click', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const page = button.data('page');
            const maxPages = button.data('max-pages');
            
            if (page >= maxPages) {
                button.hide();
                return;
            }
            
            button.text('Loading...').prop('disabled', true);
            
            // Make AJAX request (implement as needed)
            // This is a placeholder for potential future functionality
        });
    }

    /**
     * Enhanced Lazy Loading for Images (manual data-src handling)
     * Note: Most images now use native loading="lazy" attribute
     * This function handles any legacy images with data-src attributes
     */
    function initImageLazyLoading() {
        // Check if there are any manual lazy-load images (data-src)
        const manualLazyImages = document.querySelectorAll('img[data-src], img[data-srcset]');
        
        if (manualLazyImages.length === 0) {
            // No manual lazy images, skip initialization
            return;
        }
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        
                        // Load the appropriate image source
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                        }
                        
                        // Handle srcset for responsive images
                        if (img.dataset.srcset) {
                            img.srcset = img.dataset.srcset;
                        }
                        
                        img.classList.remove('lazy');
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            }, {
                root: null,
                rootMargin: '50px',
                threshold: 0.1
            });

            // Observe all manual lazy images
            manualLazyImages.forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            manualLazyImages.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                }
                img.classList.remove('lazy');
            });
        }
    }

    /**
     * Mobile navigation is now always visible, no JavaScript needed
     */

    /**
     * Accessibility Enhancements
     */
    function initAccessibility() {
        // Skip to content link
        $('.skip-link').on('click', function(e) {
            const target = $($(this).attr('href'));
            if (target.length) {
                target.focus();
            }
        });
        
        // Improve focus management for dropdowns
        $('.menu-item-has-children > a').on('keydown', function(e) {
            if (e.which === 13 || e.which === 32) { // Enter or Space
                e.preventDefault();
                $(this).next('.sub-menu').slideToggle();
            }
        });
        
        // Add ARIA labels where needed
        $('.social-links a').each(function() {
            const platform = $(this).find('i').attr('class').split(' ')[1].replace('fa-', '');
            if (!$(this).attr('aria-label')) {
                $(this).attr('aria-label', 'Visit our ' + platform + ' page');
            }
        });
    }

    /**
     * Card Hover Effects
     */
    function initCardEffects() {
        $('.card').hover(
            function() {
                $(this).addClass('card-hover');
            },
            function() {
                $(this).removeClass('card-hover');
            }
        );
    }

    /**
     * Form Enhancements
     */
    function initFormEnhancements() {
        // Add focus classes to form fields
        $('input, textarea, select').on('focus', function() {
            $(this).parent().addClass('field-focused');
        }).on('blur', function() {
            $(this).parent().removeClass('field-focused');
        });
        
        // Validate email fields
        $('input[type="email"]').on('blur', function() {
            const email = $(this).val();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                $(this).addClass('field-error');
            } else {
                $(this).removeClass('field-error');
            }
        });
    }

    /**
     * Performance Optimizations
     */
    function initPerformanceOptimizations() {
        // Defer loading of non-critical resources
        setTimeout(() => {
            // Prefetch next page resources if available
            prefetchNextPageResources();
        }, 1000);
        
        // Add performance monitoring
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                entries.forEach((entry) => {
                    if (entry.entryType === 'largest-contentful-paint') {
                        console.log('LCP:', entry.startTime);
                    }
                });
            });
            
            observer.observe({entryTypes: ['largest-contentful-paint']});
        }
        
        // Add critical resource hints
        addResourceHints();
    }
    
    /**
     * Prefetch next page resources
     */
    function prefetchNextPageResources() {
        // Prefetch common navigation links
        $('nav a[href], .btn[href]').each(function() {
            const href = $(this).attr('href');
            if (href && href.startsWith('/') && !href.includes('#')) {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = href;
                document.head.appendChild(link);
            }
        });
    }
    
    /**
     * Add resource hints for better performance
     */
    function addResourceHints() {
        const hints = [
            { rel: 'dns-prefetch', href: '//fonts.googleapis.com' },
            { rel: 'dns-prefetch', href: '//fonts.gstatic.com' }
        ];
        
        hints.forEach(hint => {
            if (!document.querySelector(`link[href="${hint.href}"]`)) {
                const link = document.createElement('link');
                link.rel = hint.rel;
                link.href = hint.href;
                document.head.appendChild(link);
            }
        });
    }
    
    /**
     * Initialize when window loads
     */
    $(window).on('load', function() {
        initCardEffects();
        initFormEnhancements();
        
        // Hide loading animations
        $('.loading').fadeOut();
        
        // Trigger scroll event to set initial header state
        $(window).trigger('scroll');
        
        // Mark page as fully loaded for performance tracking
        document.body.classList.add('page-loaded');
    });

})(jQuery); 