/**
 * Alpine.js Initializer
 * This script ensures Alpine.js is loaded only once across the application
 */

// Create a global namespace for our Alpine initialization
window.PrassoAlpine = window.PrassoAlpine || {};

// Only initialize if not already initialized
if (!window.PrassoAlpine.initialized) {
    window.PrassoAlpine.initialized = true;
    
    // Check if Alpine is already loaded
    if (typeof window.Alpine === 'undefined') {
        // Create script element
        const alpineScript = document.createElement('script');
        alpineScript.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
        alpineScript.defer = true;
        
        // Add to document
        document.addEventListener('DOMContentLoaded', function() {
            document.body.appendChild(alpineScript);
        });
        
        // If DOM is already loaded, append immediately
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            document.body.appendChild(alpineScript);
        }
    }
}
