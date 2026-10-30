import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global confirmation dialog.
window.kulaanConfirm = (options = {}) => {
    window.dispatchEvent(new CustomEvent('kulaan-confirm', { detail: options }));
};

Alpine.data('kulaanConfirmModal', () => ({
    open: false,
    title: '',
    message: '',
    confirmText: 'Ya, Lanjutkan',
    cancelText: 'Batal',
    isDanger: true,
    actionFn: null,
    init() {
        this.handleConfirm = (event) => {
            const detail = event.detail || {};
            this.title = detail.title || 'Konfirmasi Tindakan';
            this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            this.confirmText = detail.confirmText || 'Ya, Lanjutkan';
            this.cancelText = detail.cancelText || 'Batal';
            this.isDanger = detail.isDanger !== false;
            this.actionFn = typeof detail.onConfirm === 'function' ? detail.onConfirm : null;
            this.open = true;
        };
        window.addEventListener('kulaan-confirm', this.handleConfirm);
    },
    destroy() {
        window.removeEventListener('kulaan-confirm', this.handleConfirm);
    },
    proceed() {
        this.open = false;
        const action = this.actionFn;
        this.actionFn = null;
        if (typeof action === 'function') {
            action();
        }
    },
}));

if (!window.__alpineStarted) {
    window.__alpineStarted = true;
    Alpine.start();
}

// Scroll reveal: elements marked [data-reveal]
const revealObserver = new IntersectionObserver((entries) => {
    for (const entry of entries) {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            revealObserver.unobserve(entry.target);
        }
    }
}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

const initReveals = () => {
    for (const el of document.querySelectorAll('[data-reveal]:not(.is-revealed)')) {
        revealObserver.observe(el);
    }
};

document.addEventListener('turbo:load', initReveals);
document.addEventListener('DOMContentLoaded', initReveals);

// Route progress bar
const routeProgressBar = document.createElement('div');
routeProgressBar.id = 'route-progress';
routeProgressBar.setAttribute('aria-hidden', 'true');
document.body.prepend(routeProgressBar);

export function showRouteProgress() {
    routeProgressBar.classList.add('is-active');
}

document.addEventListener('click', (event) => {
    const anchor = event.target.closest('a[href]');
    if (!anchor || anchor.target === '_blank' || event.defaultPrevented) {
        return;
    }
    const href = anchor.getAttribute('href') || '';
    if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) {
        return;
    }
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }
    showRouteProgress();
});

// Submit buttons inside <form data-loading>
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-loading')) {
        return;
    }
    setTimeout(() => {
        if (event.defaultPrevented) {
            return;
        }
        const button = form.querySelector('[type="submit"]:not([disabled])');
        if (!button) {
            return;
        }
        button.disabled = true;
        button.classList.add('opacity-80', 'cursor-wait');
        const label = button.querySelector('[data-loading-label]');
        if (label) {
            label.textContent = 'Memproses…';
        } else {
            button.textContent = 'Memproses…';
        }
        showRouteProgress();
    }, 0);
});
