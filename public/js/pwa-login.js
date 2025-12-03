/**
 * PWA Hidden Login Access
 * Provides hidden login button access via keyboard shortcuts and multi-tap gestures
 * 
 * Usage: Include this script in your page and the login functionality will be automatically available
 * 
 * Access Methods:
 * - Keyboard: Press 'L' or '?' to show login button
 * - Keyboard: Press 'Escape' to hide login button
 * - Touch/Click: Triple-tap anywhere on the page to show login button
 */

(function() {
    'use strict';

    let tapCount = 0;
    let tapTimer;

    /**
     * Show or hide the login button
     * @param {boolean} show - Whether to show (true) or hide (false) the button
     */
    function toggleLoginButton(show = true) {
        const loginBtn = document.getElementById('pwa-hidden-login-btn');
        if (loginBtn) {
            loginBtn.style.display = show ? 'block' : 'none';
            if (show) {
                // Auto-hide after 10 seconds
                setTimeout(() => {
                    loginBtn.style.display = 'none';
                }, 10000);
            }
        }
    }

    /**
     * Initialize the hidden login functionality
     * Called when DOM is ready
     */
    function init() {
        // Create the hidden login button element if it doesn't exist
        if (!document.getElementById('pwa-hidden-login-btn')) {
            createLoginButton();
        }

        // Triple-tap detection (mobile/trackpad)
        document.addEventListener('click', handleTap);

        // Keyboard shortcut detection
        document.addEventListener('keydown', handleKeydown);
    }

    /**
     * Handle tap/click events for triple-tap detection
     */
    function handleTap() {
        tapCount++;
        clearTimeout(tapTimer);

        if (tapCount === 3) {
            toggleLoginButton(true);
            tapCount = 0;
        }

        tapTimer = setTimeout(() => {
            tapCount = 0;
        }, 500);
    }

    /**
     * Handle keyboard events for login access
     * @param {KeyboardEvent} e - The keyboard event
     */
    function handleKeydown(e) {
        // 'L' key or '?' key (Shift + /)
        if ((e.shiftKey && e.key === '?') || e.key.toLowerCase() === 'l') {
            e.preventDefault();
            toggleLoginButton(true);
        }
        // Escape key to hide
        if (e.key === 'Escape') {
            toggleLoginButton(false);
        }
    }

    /**
     * Get the site's primary color from the theme-color meta tag
     * Falls back to blue if not found
     */
    function getSiteThemeColor() {
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        return metaThemeColor ? metaThemeColor.getAttribute('content') : '#2563eb'; // Default blue
    }

    /**
     * Create the hidden login button HTML element
     */
    function createLoginButton() {
        const themeColor = getSiteThemeColor();
        const darkerColor = adjustColorBrightness(themeColor, -20); // Darker shade for hover
        
        const loginBtn = document.createElement('div');
        loginBtn.id = 'pwa-hidden-login-btn';
        loginBtn.style.cssText = `
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

        loginBtn.innerHTML = `
            <div style="font-size: 12px; color: #666; margin-bottom: 8px; text-align: center;">
                Press <kbd style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 11px;">L</kbd> or <kbd style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 11px;">?</kbd> to toggle
            </div>
            <a href="/login" class="inline-block text-white font-bold py-2 px-4 rounded-lg shadow-lg w-full text-center" style="display: block; text-decoration: none; background-color: ${themeColor}; transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='${darkerColor}'" onmouseout="this.style.backgroundColor='${themeColor}'">
                Login
            </a>
        `;

        document.body.appendChild(loginBtn);
    }

    /**
     * Adjust color brightness (for hover effects)
     * @param {string} color - Hex color code
     * @param {number} percent - Percentage to adjust (-100 to 100)
     */
    function adjustColorBrightness(color, percent) {
        const num = parseInt(color.replace('#', ''), 16);
        const amt = Math.round(2.55 * percent);
        const R = Math.min(255, (num >> 16) + amt);
        const G = Math.min(255, (num >> 8 & 0x00FF) + amt);
        const B = Math.min(255, (num & 0x0000FF) + amt);
        return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255))
            .toString(16).slice(1);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

/**
 * PWA Service Worker Registration
 * Registers the service worker for offline support and caching
 */
(function() {
    'use strict';

    /**
     * Register the service worker when the page loads
     */
    function registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function(registration) {
                        console.log('Service Worker registered successfully:', registration);
                    })
                    .catch(function(error) {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
    }

    // Register service worker when script loads
    registerServiceWorker();
})();
