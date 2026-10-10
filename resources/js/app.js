import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Scroll reveal: elements marked [data-reveal] rise + fade once they enter
// the viewport. Purely progressive enhancement; content is visible without JS
// only when the observer cannot run (see the reduced-motion CSS fallback).
const revealObserver = new IntersectionObserver((entries) => {
    for (const entry of entries) {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            revealObserver.unobserve(entry.target);
        }
    }
}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

for (const el of document.querySelectorAll('[data-reveal]')) {
    revealObserver.observe(el);
}
