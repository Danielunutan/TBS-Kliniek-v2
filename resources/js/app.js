import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Initialize Alpine
if (!window.AlpineInitialized) {
    Alpine.start();
    window.AlpineInitialized = true;
}
