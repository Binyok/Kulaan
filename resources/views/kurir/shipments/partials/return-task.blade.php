{{-- Tombol + modal "Kembalikan Tugas": hanya untuk tugas yang belum dimulai.
     Kurir wajib memilih alasan; tugas kembali ke distributor untuk
     ditugaskan ulang ke kurir lain. --}}
<div x-data="{ returnOpen: false }">
    <button type="button" @click="returnOpen = true"
            class="w-full mt-2.5 bg-white border border-danger/40 text-danger text-sm font-bold py-3 rounded-2xl cursor-pointer hover:bg-danger/5 transition">
        Kembalikan Tugas
    </button>

    <div x-show="returnOpen" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
        <div class="absolute inset-0 bg-ink/50" @click="returnOpen = false"></div>
        <form method="POST" action="{{ route('kurir.shipments.return', $shipment) }}"
              class="relative bg-white rounded-3xl p-5 w-full max-w-md shadow-xl text-left">
            @csrf
            <p class="font-extrabold text-ink">Kembalikan Tugas Ini?</p>
            <p class="text-xs text-muted mt-1 leading-relaxed">Tugas <span class="font-mono font-bold">{{ $shipment->shipment_number }}</span> akan dilepas dan distributor menugaskan kurir lain. Kamu kembali tersedia untuk tugas berikutnya.</p>

            <div class="mt-4">
                <label for="return-reason-{{ $uid }}-{{ $shipment->id }}" class="field-label">Alasan pengembalian <span class="text-danger">*</span></label>
                <select id="return-reason-{{ $uid }}-{{ $shipment->id }}" name="return_reason" required
                        class="block w-full text-sm border border-line rounded-xl bg-white px-3 py-2.5">
                    <option value="">— Pilih alasan —</option>
                    <option value="Kendaraan bermasalah">Kendaraan bermasalah</option>
                    <option value="Sakit / berhalangan">Sakit / berhalangan</option>
                    <option value="Muatan melebihi kapasitas kendaraan">Muatan melebihi kapasitas kendaraan</option>
                    <option value="Ada keperluan mendesak">Ada keperluan mendesak</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="mt-3">
                <label for="return-detail-{{ $uid }}-{{ $shipment->id }}" class="field-label">Keterangan tambahan (opsional)</label>
                <input id="return-detail-{{ $uid }}-{{ $shipment->id }}" type="text" name="return_reason_detail" maxlength="255"
                       placeholder="Contoh: motor mogok di bengkel"
                       class="block w-full text-sm border border-line rounded-xl bg-white px-3 py-2.5">
            </div>

            <div class="mt-5 flex gap-2">
                <button type="button" @click="returnOpen = false"
                        class="flex-1 bg-white border border-line text-ink text-sm font-bold py-3 rounded-2xl cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 bg-danger text-white text-sm font-bold py-3 rounded-2xl cursor-pointer">
                    Ya, Kembalikan
                </button>
            </div>
        </form>
    </div>
</div>
