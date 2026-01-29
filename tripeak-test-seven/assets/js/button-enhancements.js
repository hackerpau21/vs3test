/**
 * WordPress Block Button Enhancements
 * Adds interactive features to WordPress block buttons
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeButtonEnhancements();
    });

    function initializeButtonEnhancements() {
        // Add ripple effect to buttons
        addRippleEffect();
        
        // Add loading state functionality
        addLoadingStates();
        
        // Add button group enhancements
        enhanceButtonGroups();
        
        // Add keyboard navigation improvements
        enhanceKeyboardNavigation();
    }

    /**
     * Add ripple effect to buttons
     */
    function addRippleEffect() {
        const buttons = document.querySelectorAll('.wp-block-button__link');
        
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Create ripple element
                const ripple = document.createElement('span');
                const rect = button.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple-animation 0.6s linear;
                    pointer-events: none;
                `;
                
                button.appendChild(ripple);
                
                // Remove ripple after animation
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    }

    /**
     * Add loading state functionality
     */
    function addLoadingStates() {
        // Add loading state to buttons with data-loading attribute
        const loadingButtons = document.querySelectorAll('.wp-block-button__link[data-loading]');
        
        loadingButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (button.hasAttribute('data-loading')) {
                    e.preventDefault();
                    
                    const originalText = button.textContent;
                    const loadingText = button.getAttribute('data-loading-text') || 'Loading...';
                    
                    // Add loading class
                    button.closest('.wp-block-button').classList.add('is-loading');
                    button.textContent = loadingText;
                    button.disabled = true;
                    
                    // Simulate loading (remove in production and use actual async operations)
                    setTimeout(() => {
                        button.closest('.wp-block-button').classList.remove('is-loading');
                        button.textContent = originalText;
                        button.disabled = false;
                    }, 2000);
                }
            });
        });
    }

    /**
     * Enhance button groups
     */
    function enhanceButtonGroups() {
        const buttonGroups = document.querySelectorAll('.wp-block-buttons');
        
        buttonGroups.forEach(group => {
            // Add spacing between buttons
            const buttons = group.querySelectorAll('.wp-block-button');
            
            buttons.forEach((button, index) => {
                if (index > 0) {
                    button.style.marginLeft = '1rem';
                }
            });
            
            // Add responsive behavior for mobile
            if (window.innerWidth <= 480) {
                group.classList.add('is-vertical');
            }
        });
    }

    /**
     * Enhance keyboard navigation
     */
    function enhanceKeyboardNavigation() {
        const buttons = document.querySelectorAll('.wp-block-button__link');
        
        buttons.forEach(button => {
            button.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    button.click();
                }
            });
            
            // Improve focus visibility
            button.addEventListener('focus', function() {
                this.style.outline = '3px solid rgba(231, 76, 60, 0.4)';
                this.style.outlineOffset = '3px';
            });
            
            button.addEventListener('blur', function() {
                this.style.outline = '';
                this.style.outlineOffset = '';
            });
        });
    }

    /**
     * Add CSS for ripple animation
     */
    function addRippleStyles() {
        if (!document.getElementById('button-ripple-styles')) {
            const style = document.createElement('style');
            style.id = 'button-ripple-styles';
            style.textContent = `
                @keyframes ripple-animation {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                
                .wp-block-button__link {
                    position: relative;
                    overflow: hidden;
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Add ripple styles
    addRippleStyles();

    // Handle window resize for responsive button groups
    window.addEventListener('resize', function() {
        const buttonGroups = document.querySelectorAll('.wp-block-buttons');
        
        buttonGroups.forEach(group => {
            if (window.innerWidth <= 480) {
                group.classList.add('is-vertical');
            } else {
                group.classList.remove('is-vertical');
            }
        });
    });

})(); 