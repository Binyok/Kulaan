@props([
    'values' => [],
    'fields' => ['province', 'city', 'district', 'village'],
    'names' => [],
    'required' => ['city', 'district'],
])

@php
    $labels = [
        'province' => 'Provinsi',
        'city' => 'Kota / Kabupaten',
        'district' => 'Kecamatan',
        'village' => 'Desa / Kelurahan',
    ];
@endphp

<div
    x-data="regionCascade({
        levels: {{ json_encode(array_values($fields)) }},
        names: {{ json_encode($names) }},
        required: {{ json_encode(array_values($required)) }},
        stored: {{ json_encode([
            'province' => $values['province'] ?? null,
            'city' => $values['city'] ?? null,
            'district' => $values['district'] ?? null,
            'village' => $values['village'] ?? null,
        ]) }},
    })"
    class="grid grid-cols-1 sm:grid-cols-2 gap-3"
>
    <template x-for="level in levels" :key="level">
        <div>
            <label :for="'region-' + level" class="field-label" x-text="labels[level]"></label>

            <template x-if="!textMode[level]">
                <select
                    :id="'region-' + level"
                    :name="names[level] || level"
                    x-model="selected[level]"
                    @change="onLevelChange(level)"
                    :required="required.includes(level)"
                    :disabled="loading[level]"
                    class="input-field bg-white"
                >
                    <option value="" x-text="loading[level] ? 'Memuat…' : ('Pilih ' + labels[level])"></option>
                    <template x-for="opt in options[level]" :key="opt.id">
                        <option :value="opt.name" x-text="opt.name"></option>
                    </template>
                </select>
            </template>

            <template x-if="textMode[level]">
                <input
                    type="text"
                    :id="'region-' + level"
                    :name="names[level] || level"
                    x-model="textValue[level]"
                    :required="required.includes(level)"
                    :placeholder="'Tulis ' + labels[level]"
                    class="input-field"
                >
            </template>
        </div>
    </template>

    <p x-show="offline" x-cloak class="sm:col-span-2 text-[0.6875rem] text-brand-slate">
        Daftar wilayah tidak dapat dimuat (perlu internet) — silakan tulis manual.
    </p>
</div>
