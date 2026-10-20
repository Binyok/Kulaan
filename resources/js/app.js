import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global confirmation dialog. Triggers call window.kulaanConfirm({...}); the
// <x-layouts.*> confirm modal listens for the event on window, so the dialog
// opens regardless of when or where the trigger button was rendered. Defining
// the trigger here (rather than inside an Alpine init()) removes any ordering
// dependency between page markup and the modal component.
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

// Submit buttons inside <form data-loading>: lock against double-submit and
// swap the label while the request flies. Skipped when another handler
// cancels the submit (e.g. a confirm() dialog returning false).
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

// Thin top progress bar for full-page navigations (this is an MPA: every
// link click and form submit unloads the page, so the bar only ever fills).
const routeProgressBar = document.createElement('div');
routeProgressBar.id = 'route-progress';
routeProgressBar.setAttribute('aria-hidden', 'true');
document.body.prepend(routeProgressBar);

function showRouteProgress() {
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

// Realtime auto-refresh: every authenticated page polls a cheap revision
// fingerprint and reloads itself the instant new data lands, so nobody has to
// hit refresh to see an incoming order, request, or shipment. Polling pauses
// while the tab is hidden, a modal is open, or the user is mid-input, so an
// in-progress interaction is never yanked out from under them.
const liveEndpoint = document.querySelector('meta[name="kulaan-live-endpoint"]')?.content;

if (liveEndpoint) {
    const intervalMs = Number(document.querySelector('meta[name="kulaan-live-interval"]')?.content || 10000);
    let baseline = null;

    const isBusy = () => {
        if (document.visibilityState !== 'visible') {
            return true;
        }
        if (document.body.style.overflow === 'hidden') {
            return true;
        }
        if (document.querySelector('dialog[open]')) {
            return true;
        }
        const active = document.activeElement;
        return !!active && (active.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName));
    };

    const fetchRevision = async () => {
        try {
            const res = await fetch(liveEndpoint, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin',
            });
            if (!res.ok) {
                return null;
            }
            const data = await res.json();
            return data?.revision ?? null;
        } catch (e) {
            return null;
        }
    };

    (async () => {
        baseline = await fetchRevision();
    })();

    setInterval(async () => {
        if (isBusy()) {
            return;
        }
        const revision = await fetchRevision();
        if (revision && baseline && revision !== baseline) {
            location.reload();
        }
    }, intervalMs);

    document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState === 'visible' && !isBusy()) {
            const revision = await fetchRevision();
            if (revision && baseline && revision !== baseline) {
                location.reload();
            }
        }
    });
}

// Cascading region selects (province > city > district > village) backed by
// the public emsifa wilayah API. Selections submit NAMES (matching the DB
// columns), while API ids are only used internally to fetch children.
// Anything unresolvable degrades to a text input so stored data is never lost.
window.regionCascade = function (config) {
    const API = 'https://emsifa.github.io/api-wilayah-indonesia/api';
    const norm = (s) => (s ?? '').toString().trim().toLowerCase();
    const CHILD = { province: 'city', city: 'district', district: 'village' };
    const ENDPOINT = {
        province: () => `${API}/provinces.json`,
        city: (parentId) => `${API}/regencies/${parentId}.json`,
        district: (parentId) => `${API}/districts/${parentId}.json`,
        village: (parentId) => `${API}/villages/${parentId}.json`,
    };
    const DEFAULT_PROVINCE_ID = '35';
    const DEFAULT_CITY_NAME = 'sidoarjo';

    return {
        levels: config.levels,
        names: config.names || {},
        required: config.required || [],
        labels: { province: 'Provinsi', city: 'Kota / Kabupaten', district: 'Kecamatan', village: 'Desa / Kelurahan' },
        options: { province: [], city: [], district: [], village: [] },
        selected: { province: '', city: '', district: '', village: '' },
        textMode: { province: false, city: false, district: false, village: false },
        textValue: { province: null, city: null, district: null, village: null, ...(config.stored || {}) },
        loading: { province: false, city: false, district: false, village: false },
        offline: false,

        async fetchJson(url) {
            const res = await fetch(url);
            if (!res.ok) {
                throw new Error(`Wilayah API ${res.status}`);
            }
            const data = await res.json();
            if (!Array.isArray(data)) {
                throw new Error('Wilayah API format');
            }
            return data;
        },

        matchByName(options, name) {
            const needle = norm(name);
            if (!needle) {
                return null;
            }
            return options.find((o) => norm(o.name) === needle)
                || options.find((o) => norm(o.name).includes(needle))
                || null;
        },

        async init() {
            this.loading.province = true;
            try {
                const provinces = await this.fetchJson(ENDPOINT.province());
                this.options.province = provinces;
                const picked = this.matchByName(provinces, config.stored?.province)
                    || provinces.find((p) => String(p.id) === DEFAULT_PROVINCE_ID)
                    || provinces[0];
                this.selected.province = picked ? picked.name : '';
                if (picked) {
                    await this.loadChildren('province', picked.id, true);
                }
            } catch (e) {
                this.goOffline();
            } finally {
                this.loading.province = false;
            }
        },

        async loadChildren(level, parentId, resolving) {
            const child = CHILD[level];
            if (!child || !this.levels.includes(child)) {
                return;
            }
            this.loading[child] = true;
            try {
                const opts = await this.fetchJson(ENDPOINT[child](parentId));
                this.options[child] = opts;
                const storedName = resolving ? config.stored?.[child] : null;
                let picked = storedName ? this.matchByName(opts, storedName) : null;
                if (!picked && child === 'city' && resolving) {
                    picked = this.matchByName(opts, DEFAULT_CITY_NAME) || opts[0] || null;
                }
                if (picked) {
                    this.textMode[child] = false;
                    this.selected[child] = picked.name;
                    await this.loadChildren(child, picked.id, resolving);
                } else if (storedName) {
                    this.textMode[child] = true;
                    this.textValue[child] = storedName;
                    this.selected[child] = '';
                } else {
                    this.selected[child] = '';
                }
            } catch (e) {
                if (resolving && config.stored?.[child]) {
                    this.textMode[child] = true;
                } else {
                    this.goOffline();
                }
            } finally {
                this.loading[child] = false;
            }
        },

        async onLevelChange(level) {
            const opt = this.options[level].find((o) => o.name === this.selected[level]);
            for (const l of this.levelsAfter(level)) {
                this.options[l] = [];
                this.selected[l] = '';
                this.textMode[l] = false;
            }
            if (opt) {
                await this.loadChildren(level, opt.id, false);
            }
        },

        levelsAfter(level) {
            const out = [];
            let next = CHILD[level];
            while (next) {
                if (this.levels.includes(next)) {
                    out.push(next);
                }
                next = CHILD[next];
            }
            return out;
        },

        goOffline() {
            this.offline = true;
            for (const level of this.levels) {
                this.textMode[level] = true;
            }
        },
    };
};
