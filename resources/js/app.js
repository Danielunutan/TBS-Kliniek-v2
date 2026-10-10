import * as Turbo from '@hotwired/turbo';
import Alpine from 'alpinejs';

window.Turbo = Turbo;
window.Alpine = Alpine;

// Initialize Alpine
if (!window.AlpineInitialized) {
    Alpine.start();
    window.AlpineInitialized = true;
}
