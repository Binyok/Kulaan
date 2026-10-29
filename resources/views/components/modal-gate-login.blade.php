<div id="gateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" x-data="{ open: false }" x-show="open" x-cloak @keydown.escape.window="open = false">
    <div class="fixed inset-0 bg-ink/60 transition-opacity" @click="open = false"></div>
    <div class="relative bg-white rounded-[1.75rem] max-w-md w-full p-7 shadow-2xl transform transition-all z-10">
        <button type="button" @click="open = false" aria-label="Tutup" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-cream border border-line flex items-center justify-center hover:bg-line transition cursor-pointer">
            <svg class="w-4 h-4 text-ink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
        <span class="w-12 h-12 rounded-2xl bg-forest/10 text-forest flex items-center justify-center">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
        </span>
        <h2 class="text-xl font-extrabold mt-4 text-ink">Login dulu untuk belanja</h2>
        <p class="text-sm text-muted leading-relaxed mt-2">Kamu bisa bebas melihat-lihat produk dan distributor. Untuk memasukkan ke keranjang dan memesan, masuk atau daftar sebagai UMKM — gratis.</p>
        <a href="{{ route('login') }}" class="block text-center bg-forest hover:bg-forest-dark text-white font-bold px-6 py-3 rounded-xl mt-5 transition">Masuk</a>
        <a href="{{ route('register') }}" class="block text-center border border-forest text-forest font-bold px-6 py-3 rounded-xl mt-2.5 hover:bg-forest/5 transition">Daftar Jadi UMKM</a>
        <button type="button" @click="open = false" class="block w-full text-center text-xs font-semibold text-muted hover:text-ink mt-4 cursor-pointer">Nanti saja, lanjut melihat-lihat</button>
    </div>
</div>
