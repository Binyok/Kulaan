@if ($order->status->value === 'PAID')
    <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}" class="sm:text-right">
        @csrf
        <input type="hidden" name="status" value="CONFIRMED">
        <button type="submit" class="btn-primary text-xs py-1.5 px-4 font-semibold w-full sm:w-auto">
            Konfirmasi
        </button>
    </form>
@elseif ($order->status->value === 'CONFIRMED')
    <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}" class="sm:text-right">
        @csrf
        <input type="hidden" name="status" value="PREPARING">
        <button type="submit" class="btn-primary text-xs py-1.5 px-4 font-semibold w-full sm:w-auto">
            Siapkan Barang
        </button>
    </form>
@elseif ($order->status->value === 'PREPARING')
    <form method="POST" action="{{ route('distributor.orders.update-status', $order) }}" class="sm:text-right">
        @csrf
        <input type="hidden" name="status" value="READY_TO_SHIP">
        <button type="submit" class="btn-primary text-xs py-1.5 px-4 font-semibold w-full sm:w-auto">
            Siap Kirim
        </button>
    </form>
@elseif ($order->status->value === 'READY_TO_SHIP')
    <button type="button" @click="selectedOrder = {{ json_encode($order) }}; openCourierModal = true" class="btn-accent text-xs py-1.5 px-4 font-semibold w-full sm:w-auto">
        Tugaskan Kurir
    </button>
@else
    <span class="text-xs text-brand-slate">Tidak ada aksi</span>
@endif
