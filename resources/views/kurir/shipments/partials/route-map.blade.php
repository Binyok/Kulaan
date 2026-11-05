{{--
    Mini-peta rute di dalam kartu tugas kurir: garis rute jalan dari gudang
    distributor melewati semua tujuan pengiriman (data jalan OpenStreetMap
    via OSRM, digambar oleh public/js/kulaan-route.js). Peta dimuat malas
    (lazy) saat tombolnya pertama kali ditekan.
--}}
@php
    $distributor = $shipment->order->distributor;
    $originPair = ($distributor && $distributor->latitude !== null && $distributor->longitude !== null)
        ? (float) $distributor->latitude.','.(float) $distributor->longitude
        : null;
    $stops = $shipment->order->items
        ->map(fn ($i) => $i->umkm)
        ->filter(fn ($u) => $u && $u->latitude !== null && $u->longitude !== null)
        ->unique('id')
        ->map(fn ($u) => ['label' => $u->business_name, 'lat' => (float) $u->latitude, 'lng' => (float) $u->longitude])
        ->values();
    $courierPair = $shipment->latestLocation
        ? (float) $shipment->latestLocation->latitude.','.(float) $shipment->latestLocation->longitude
        : null;
    $wrapId = 'route-map-'.$shipment->id.'-'.$uid;
@endphp
@if ($originPair || $stops->isNotEmpty())
    <div>
        <button type="button" data-route-toggle="{{ $wrapId }}" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-brass-soft text-brass cursor-pointer">🗺️ Lihat Peta Rute</button>
        <div id="{{ $wrapId }}" class="hidden mt-2">
            <div data-route-map
                 @if ($originPair) data-origin="{{ $originPair }}" @endif
                 @if ($courierPair) data-courier="{{ $courierPair }}" @endif
                 data-stops='@json($stops)'
                 class="w-full rounded-2xl border border-line overflow-hidden" style="height: 240px; background:#ECE8DA;"></div>
            <p data-route-caption class="text-[11px] text-muted mt-1.5">Menghitung rute jalan…</p>
        </div>
    </div>
@endif
