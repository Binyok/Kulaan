{{--
    Blok tujuan pengiriman untuk kartu kurir: alamat lengkap penerima
    (jalan + desa + kecamatan + kota + provinsi), nama & nomor penerima,
    dan tombol aksi — rute Google Maps, WhatsApp, telepon — supaya kurir
    tidak perlu menebak lokasi dari alamat jalan + kecamatan saja.
--}}
@php
    $recipient = $item->umkm?->user;
    $waUrl = \App\Support\ContactLinks::whatsApp($recipient?->phone);
    $telUrl = \App\Support\ContactLinks::tel($recipient?->phone);
@endphp
<div>
    <p><strong class="text-ink">{{ $item->umkm->business_name }}</strong></p>
    <p class="text-muted leading-relaxed">{{ $item->umkm->fullAddress($item->delivery_address) }}</p>
    @if ($recipient)
        <p class="text-muted">Penerima: {{ $recipient->name }}@if ($recipient->phone) &bull; {{ $recipient->phone }} @endif</p>
    @endif
    <div class="flex flex-wrap gap-1.5 mt-1.5">
        <a href="{{ $item->umkm->directionsUrl($item->delivery_address) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-forest text-white">🧭 Buka Rute</a>
        @if ($waUrl)
            <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-success/15 text-success">WhatsApp</a>
        @endif
        @if ($telUrl)
            <a href="{{ $telUrl }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-forest/10 text-forest">Telepon</a>
        @endif
    </div>
</div>
