/**
 * PWA Install Prompt Handler
 * Handles both Android (beforeinstallprompt) and iOS (manual instructions) PWA installation
 * 
 * Features:
 * - Detects Android and shows native install prompt
 * - Detects iOS and shows manual installation instructions
 * - Dismissible banner with "Don't show again" option
 * - Respects user preferences via localStorage
 */

(function() {
    'use strict';

    let deferredPrompt = null;
    const STORAGE_KEY = 'pwa-install-dismissed';
    const STORAGE_EXPIRY_KEY = 'pwa-install-dismissed-expiry';
    const DISMISS_DAYS = 7; // Show prompt again after 7 days

    /**
     * Check if the prompt was recently dismissed
     */
    function isDismissed() {
        const dismissedTime = localStorage.getItem(STORAGE_EXPIRY_KEY);
        if (!dismissedTime) return false;
        
        const now = new Date().getTime();
        if (now > parseInt(dismissedTime)) {
            // Expiry time has passed, clear the dismissal
            localStorage.removeItem(STORAGE_KEY);
            localStorage.removeItem(STORAGE_EXPIRY_KEY);
            return false;
        }
        
        return localStorage.getItem(STORAGE_KEY) === 'true';
    }

    /**
     * Mark the prompt as dismissed for DISMISS_DAYS
     */
    function markDismissed() {
        const expiryTime = new Date().getTime() + (DISMISS_DAYS * 24 * 60 * 60 * 1000);
        localStorage.setItem(STORAGE_KEY, 'true');
        localStorage.setItem(STORAGE_EXPIRY_KEY, expiryTime.toString());
    }

    /**
     * Detect if running on iOS
     */
    function isIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    }

    /**
     * Detect if running on Android
     */
    function isAndroid() {
        return /Android/.test(navigator.userAgent);
    }

    /**
     * Check if app is already installed
     */
    function isAppInstalled() {
        // Check if running in standalone mode (already installed)
        if (window.navigator.standalone === true) {
            return true;
        }
        
        // Check for display-mode: standalone
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        
        return false;
    }

    /**
     * Create and show the install prompt banner
     */
    function showInstallPrompt() {
        if (isDismissed() || isAppInstalled()) {
            return;
        }

        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.setAttribute('role', 'alert');
        
        const isIos = isIOS();
        const title = isIos ? 'Add to Home Screen' : 'Install App';
        const message = isIos 
            ? 'Tap the Share button, then select "Add to Home Screen"'
            : 'Install this app on your device for quick access';

        banner.innerHTML = `
            <div style="
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 16px;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
                z-index: 9998;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                animation: slideUp 0.3s ease-out;
            ">
                <div style="max-width: 600px; margin: 0 auto; display: flex; align-items: center; gap: 12px;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px;">
                            ${title}
                        </div>
                        <div style="font-size: 13px; opacity: 0.95;">
                            ${message}
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; flex-shrink: 0;">
                        ${!isIos ? `
                            <button id="pwa-install-btn" style="
                                background: white;
                                color: #667eea;
                                border: none;
                                padding: 8px 16px;
                                border-radius: 6px;
                                font-weight: 600;
                                font-size: 13px;
                                cursor: pointer;
                                transition: transform 0.2s;
                            " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                Install
                            </button>
                        ` : ''}
                        <button id="pwa-dismiss-btn" style="
                            background: rgba(255,255,255,0.2);
                            color: white;
                            border: none;
                            padding: 8px 16px;
                            border-radius: 6px;
                            font-weight: 600;
                            font-size: 13px;
                            cursor: pointer;
                            transition: background 0.2s;
                        " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
            <style>
                @keyframes slideUp {
                    from {
                        transform: translateY(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
            </style>
        `;

        document.body.appendChild(banner);

        // Handle dismiss button
        document.getElementById('pwa-dismiss-btn').addEventListener('click', function() {
            banner.remove();
            markDismissed();
        });

        // Handle install button (Android only)
        if (!isIos && deferredPrompt) {
            document.getElementById('pwa-install-btn').addEventListener('click', function() {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('PWA installation accepted');
                        banner.remove();
                        markDismissed();
                    } else {
                        console.log('PWA installation dismissed');
                    }
                    deferredPrompt = null;
                });
            });
        }
    }

    /**
     * Initialize PWA install prompt detection
     */
    function init() {
        // Don't show if already installed
        if (isAppInstalled()) {
            return;
        }

        // Android: Listen for beforeinstallprompt event
        if (isAndroid()) {
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                showInstallPrompt();
            });
        }
        // iOS: Show instructions banner
        else if (isIOS()) {
            // Show after a short delay to let page load
            setTimeout(showInstallPrompt, 1000);
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
