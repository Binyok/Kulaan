import './app';
import * as Turbo from '@hotwired/turbo';
import './live-polling';

window.Turbo = Turbo;

// Rules #4: Non-GET forms must NOT be intercepted by Turbo
// Tag non-GET forms with data-turbo="false" during capture phase
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form instanceof HTMLFormElement) {
        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (method !== 'GET') {
            form.setAttribute('data-turbo', 'false');
        }
    }
}, true);

// Fallback stop if Turbo attempt to submit non-GET form
document.addEventListener('turbo:submit-start', (event) => {
    const form = event.detail.formSubmission?.formElement;
    const method = (form?.getAttribute('method') || 'GET').toUpperCase();
    if (method !== 'GET') {
        event.detail.formSubmission.stop();
    }
});
