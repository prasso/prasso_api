/**
 * Alpine.js Singleton
 * This script ensures Alpine.js is loaded only once across the application
 * and prevents the "Detected multiple instances of Alpine running" error.
 */

// Create a global namespace for Alpine singleton management
window.AlpineSingleton = window.AlpineSingleton || {};

// Prevent multiple Alpine instances by setting up a global flag
if (!window.alpineHasBeenSetup) {
    window.alpineHasBeenSetup = true;
    
    // Tell Livewire not to load Alpine.js
    window.livewireScriptConfig = window.livewireScriptConfig || {};
    window.livewireScriptConfig.skipAlpine = true;
    
    // Function to load Alpine.js properly
    function loadAlpine() {
        // If Alpine is already defined, don't do anything
        if (window.Alpine) {
            console.log('Alpine is already loaded, not loading again');
            return;
        }
        
        // Create Alpine script element
        const alpineScript = document.createElement('script');
        alpineScript.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
        alpineScript.defer = true;
        
        // Add to document body (not head) to ensure body is available
        document.body.appendChild(alpineScript);
    }
    
    // Wait for DOM to be fully loaded before attempting to load Alpine
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Small delay to ensure body is fully available
            setTimeout(loadAlpine, 10);
        });
    } else {
        // DOM is already loaded, add a small delay to ensure body is available
        setTimeout(loadAlpine, 10);
    }
}
