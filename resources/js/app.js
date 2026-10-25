import * as Turbo from '@hotwired/turbo';
import 'alpine-turbo-drive-adapter';
import Alpine from 'alpinejs';

window.Turbo = Turbo;
window.Alpine = Alpine;

// Forward CSRF token and XMLHttpRequest header to all Turbo fetch requests
document.addEventListener('turbo:before-fetch-request', (event) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        event.detail.fetchOptions.headers['X-CSRF-TOKEN'] = token;
        event.detail.fetchOptions.headers['X-Requested-With'] = 'XMLHttpRequest';
    }
});

// Update stats and trigger page lifecycle hooks on Turbo navigation
document.addEventListener('turbo:load', () => {
    if (typeof window.tbsRefreshStats === 'function') {
        window.tbsRefreshStats();
    }
});

// Initialize Alpine
if (!window.AlpineInitialized) {
    Alpine.start();
    window.AlpineInitialized = true;
}
