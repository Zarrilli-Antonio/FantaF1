import './bootstrap';

// Close any open language-switcher <details> when the user clicks outside it.
// Plain JS on purpose: this must work even on pages with no Livewire/Alpine.
document.addEventListener('click', (event) => {
    document.querySelectorAll('details.language-switcher[open]').forEach((details) => {
        if (!details.contains(event.target)) {
            details.removeAttribute('open');
        }
    });
});

if ('serviceWorker' in navigator) {
    import('virtual:pwa-register').then(({ registerSW }) => {
        registerSW({ immediate: true });
    });
}
