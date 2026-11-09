// Searchable cascading region picker (province > city > district > village)
// backed by the region dataset bundled with the app itself (served from
// config.baseUrl, e.g. /regions/provinces). Selections submit dataset NAMES
// (matching the DB columns); Kemendagri ids are only used to fetch children.
//
// There is deliberately NO free-text fallback: a level only ever submits a
// name picked from the dataset, and the server re-validates the whole chain,
// so junk addresses cannot get through.
window.regionCascade = function (config) {
    const LIMIT = 10;
    const CHILD = { province: 'city', city: 'district', district: 'village' };
    const PARENT = { city: 'province', district: 'city', village: 'district' };
    const ENDPOINT_SEGMENT = { province: 'provinces', city: 'regencies', district: 'districts', village: 'villages' };

    const norm = (s) => (s ?? '').toString().trim().toLowerCase().replace(/\s+/g, ' ');
    const regencyKeys = (name) => {
        const normalized = norm(name);
        const keys = [normalized];
        for (const prefix of ['kabupaten ', 'kota ', 'kota adm. ', 'kab. adm. ', 'kab. ']) {
            if (normalized.startsWith(prefix)) {
                keys.push(normalized.slice(prefix.length));
                break;
            }
        }
        return keys;
    };

    const emptyLevels = () => ({ province: '', city: '', district: '', village: '' });
    const falseLevels = () => ({ province: false, city: false, district: false, village: false });
    const listLevels = () => ({ province: [], city: [], district: [], village: [] });

    return {
        levels: config.levels,
        names: config.names || {},
        required: config.required || [],
        stored: config.stored || {},
        errors: config.errors || {},
        baseUrl: config.baseUrl,
        labels: { province: 'Provinsi', city: 'Kota / Kabupaten', district: 'Kecamatan', village: 'Desa / Kelurahan' },
        options: listLevels(),
        selected: emptyLevels(),
        selectedId: emptyLevels(),
        query: emptyLevels(),
        open: falseLevels(),
        loading: falseLevels(),
        failed: falseLevels(),
        unresolved: emptyLevels(),

        endpoint(level) {
            if (level === 'province') {
                return `${this.baseUrl}/${ENDPOINT_SEGMENT[level]}`;
            }
            return `${this.baseUrl}/${ENDPOINT_SEGMENT[level]}/${this.selectedId[PARENT[level]]}`;
        },

        async init() {
            await this.loadLevel('province', true);
        },

        async loadLevel(level, initial = false) {
            this.loading[level] = true;
            this.failed[level] = false;
            try {
                const res = await fetch(this.endpoint(level), { headers: { Accept: 'application/json' } });
                if (!res.ok) {
                    throw new Error(`Wilayah ${res.status}`);
                }
                const list = await res.json();
                if (!Array.isArray(list)) {
                    throw new Error('Wilayah format');
                }
                this.options[level] = list;

                if (initial) {
                    await this.restoreLevel(level);
                }
            } catch (e) {
                this.failed[level] = true;
            } finally {
                this.loading[level] = false;
            }
        },

        matchOption(level, name) {
            const options = this.options[level];
            if (level === 'city') {
                const wanted = regencyKeys(name);
                return options.find((o) => regencyKeys(o.name).some((k) => wanted.includes(k)))
                    || options.find((o) => wanted.some((k) => k && norm(o.name).includes(k)))
                    || null;
            }
            return options.find((o) => norm(o.name) === norm(name))
                || options.find((o) => norm(name) && norm(o.name).includes(norm(name)))
                || null;
        },

        async restoreLevel(level) {
            const storedName = this.stored[level];
            if (!storedName) {
                return;
            }
            const match = this.matchOption(level, storedName);
            if (!match) {
                this.unresolved[level] = storedName;
                return;
            }
            await this.applyPick(level, match, true);
        },

        async applyPick(level, option, initial = false) {
            this.selected[level] = option.name;
            this.selectedId[level] = String(option.id);
            this.query[level] = option.name;
            this.unresolved[level] = '';
            this.errors[level] = '';
            this.open[level] = false;
            this.clearBelow(level);

            const child = CHILD[level];
            if (child && this.levels.includes(child)) {
                await this.loadLevel(child, initial);
            }
        },

        async pick(level, option) {
            await this.applyPick(level, option, false);
        },

        pickFirst(level) {
            const first = this.filteredOptions(level)[0];
            if (first) {
                this.pick(level, first);
            }
        },

        clearBelow(level) {
            let cursor = CHILD[level];
            while (cursor) {
                this.options[cursor] = [];
                this.selected[cursor] = '';
                this.selectedId[cursor] = '';
                this.query[cursor] = '';
                this.open[cursor] = false;
                this.unresolved[cursor] = '';
                this.errors[cursor] = '';
                cursor = CHILD[cursor];
            }
        },

        isEnabled(level) {
            if (level === 'province') {
                return true;
            }
            return this.selectedId[PARENT[level]] !== '';
        },

        placeholderFor(level) {
            if (this.loading[level]) {
                return 'Memuat…';
            }
            if (!this.isEnabled(level)) {
                return `Pilih ${this.labels[PARENT[level]].toLowerCase()} dulu`;
            }
            return `Ketik / pilih ${this.labels[level].toLowerCase()}`;
        },

        filteredOptions(level) {
            const q = norm(this.query[level]);
            if (!q || q === norm(this.selected[level])) {
                return this.options[level].slice(0, LIMIT);
            }
            return this.options[level]
                .filter((o) => norm(o.name).includes(q))
                .slice(0, LIMIT);
        },

        filteredCount(level) {
            const q = norm(this.query[level]);
            if (!q || q === norm(this.selected[level])) {
                return this.options[level].length;
            }
            return this.options[level].filter((o) => norm(o.name).includes(q)).length;
        },

        hasMore(level) {
            return this.filteredCount(level) > LIMIT;
        },

        openList(level) {
            if (this.isEnabled(level)) {
                this.open[level] = true;
            }
        },

        closeList(level) {
            this.open[level] = false;
        },

        onQuery(level) {
            this.openList(level);
            // Typing away from the picked value un-picks it: only a dataset
            // option (or an exact retype of one) may ever be submitted.
            if (norm(this.query[level]) !== norm(this.selected[level])) {
                if (this.selected[level] !== '') {
                    this.selected[level] = '';
                    this.selectedId[level] = '';
                    this.clearBelow(level);
                }
                const exact = this.options[level].find((o) => norm(o.name) === norm(this.query[level]));
                if (exact) {
                    this.selected[level] = exact.name;
                    this.selectedId[level] = String(exact.id);
                    const child = CHILD[level];
                    if (child && this.levels.includes(child)) {
                        this.loadLevel(child, false);
                    }
                }
            }
        },

        onBlur(level) {
            const exact = this.options[level].find((o) => norm(o.name) === norm(this.query[level]));
            if (exact && this.selected[level] !== exact.name) {
                this.pick(level, exact);
            }
        },

        async retry(level) {
            if (level === 'province' || this.isEnabled(level)) {
                await this.loadLevel(level, false);
            }
        },
    };
};
