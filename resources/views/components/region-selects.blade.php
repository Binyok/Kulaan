@props([
    'values' => [],
    'fields' => ['province', 'city', 'district', 'village'],
    'names' => [],
    'required' => ['province', 'city', 'district'],
])

@php
    $fieldNames = [];
    $fieldErrors = [];
    foreach ($fields as $field) {
        $fieldNames[$field] = $names[$field] ?? $field;
        $fieldErrors[$field] = $errors->first($fieldNames[$field]);
    }
@endphp

{{-- Classic script on purpose: the regionCascade factory must exist before
     Alpine evaluates x-data. A deferred Vite module races Alpine's start
     and intermittently leaves the fields unrendered. --}}
@once
    <script src="{{ asset('js/region-cascade.js') }}"></script>
@endonce

<div
    x-data="regionCascade({
        levels: {{ json_encode(array_values($fields)) }},
        names: {{ json_encode($fieldNames) }},
        required: {{ json_encode(array_values($required)) }},
        stored: {{ json_encode([
            'province' => $values['province'] ?? null,
            'city' => $values['city'] ?? null,
            'district' => $values['district'] ?? null,
            'village' => $values['village'] ?? null,
        ]) }},
        errors: {{ json_encode($fieldErrors) }},
        baseUrl: {{ json_encode(url('/regions')) }},
    })"
    class="grid grid-cols-1 sm:grid-cols-2 gap-3"
>
    <template x-for="level in levels" :key="level">
        <div class="relative" @click.outside="closeList(level)">
            <label class="field-label">
                <span x-text="labels[level]"></span>
                <span x-show="required.includes(level)" x-cloak class="text-danger">*</span>
            </label>

            <input
                type="text"
                autocomplete="off"
                x-model="query[level]"
                @focus="openList(level)"
                @input="onQuery(level)"
                @blur="onBlur(level)"
                @keydown.escape="closeList(level)"
                @keydown.enter.prevent="pickFirst(level)"
                :disabled="!isEnabled(level)"
                :placeholder="placeholderFor(level)"
                class="input-field bg-white disabled:bg-cream/60 disabled:text-muted"
            >
            <input type="hidden" :name="names[level] || level" :value="selected[level]">

            <div
                x-show="open[level]"
                x-cloak
                class="absolute z-30 left-0 right-0 mt-1.5 bg-white border border-line rounded-2xl shadow-xl max-h-60 overflow-auto py-1.5"
            >
                <p x-show="loading[level]" class="px-4 py-2.5 text-xs text-muted">Memuat daftar wilayah…</p>
                <template x-for="opt in filteredOptions(level)" :key="opt.id">
                    <button
                        type="button"
                        @click="pick(level, opt)"
                        class="w-full text-left px-4 py-2.5 text-sm text-ink hover:bg-cream"
                        :class="{ 'bg-mint font-bold': selected[level] === opt.name }"
                        x-text="opt.name"
                    ></button>
                </template>
                <p x-show="!loading[level] && filteredOptions(level).length === 0" x-cloak class="px-4 py-2.5 text-xs text-muted">Tidak ada wilayah yang cocok dengan ketikanmu.</p>
            </div>

            <p x-show="failed[level]" x-cloak class="text-xs text-danger font-semibold mt-1.5">
                Daftar wilayah gagal dimuat.
                <button type="button" @click="retry(level)" class="underline font-bold">Coba lagi</button>
            </p>
            <p x-show="errors[level]" x-cloak class="text-xs text-danger font-semibold mt-1.5" x-text="errors[level]"></p>
            <p x-show="unresolved[level]" x-cloak class="text-[0.6875rem] text-brass mt-1.5">
                Alamat tersimpan sebelumnya (<span x-text="unresolved[level]"></span>) tidak ditemukan di data wilayah — silakan pilih ulang dari daftar.
            </p>
        </div>
    </template>
</div>
