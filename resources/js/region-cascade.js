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

        async loadChildren(level, parentId, isInitial = false) {
            const childLevel = CHILD[level];
            if (!childLevel || !this.levels.includes(childLevel)) {
                return;
            }
            this.loading[childLevel] = true;
            this.clearBelow(childLevel);

            try {
                const list = await this.fetchJson(ENDPOINT[childLevel](parentId));
                this.options[childLevel] = list;

                let autoPick = null;
                if (isInitial && config.stored?.[childLevel]) {
                    autoPick = this.matchByName(list, config.stored[childLevel]);
                } else if (childLevel === 'city' && !config.stored?.city) {
                    autoPick = this.matchByName(list, DEFAULT_CITY_NAME) || list[0];
                }

                if (autoPick) {
                    this.selected[childLevel] = autoPick.name;
                    await this.loadChildren(childLevel, autoPick.id, isInitial);
                }
            } catch (e) {
                this.switchToTextMode(childLevel);
            } finally {
                this.loading[childLevel] = false;
            }
        },

        async onSelect(level) {
            const chosenName = this.selected[level];
            const childLevel = CHILD[level];
            if (!childLevel) {
                return;
            }
            this.clearBelow(childLevel);

            if (!chosenName) {
                return;
            }
            const match = this.matchByName(this.options[level], chosenName);
            if (match) {
                await this.loadChildren(level, match.id, false);
            }
        },

        clearBelow(level) {
            let cursor = level;
            while (cursor) {
                this.options[cursor] = [];
                this.selected[cursor] = '';
                cursor = CHILD[cursor];
            }
        },

        switchToTextMode(level) {
            let cursor = level;
            while (cursor) {
                this.textMode[cursor] = true;
                this.textValue[cursor] = this.selected[cursor] || this.textValue[cursor] || '';
                cursor = CHILD[cursor];
            }
        },

        goOffline() {
            this.offline = true;
            for (const lvl of this.levels) {
                this.textMode[lvl] = true;
                this.textValue[lvl] = config.stored?.[lvl] || '';
            }
        },
    };
};
