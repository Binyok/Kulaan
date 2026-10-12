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
