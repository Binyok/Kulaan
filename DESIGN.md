# DESIGN.md — Kula'an Redesign Direction

> Sumber: jawaban owner (redesign total). Filter: antislop + redesign-existing-projects.
> Dials: **ENERGY 2 / RHYTHM 2 / MOTION 2**.

## Identitas

Kula'an = pasar kolektif UMKM yang hangat dan tepercaya, dieksekusi setara produk modern.
Hangat datang dari: serif display, kertas krem hangat, sudut membulat, bahasa Indonesia santai.
Premium datang dari: skala tipografi tegas, angka tabular, shadow kehijauan, satu aksen emas,
satu motif berulang (eyebrow + kartu double-bezel yang disempurnakan).

## Palet (hijau dipertahankan, netral dihangatkan)

- Primer: `#2D6A4F` (tetap), hover `#1B4332`, terang `#40916C`
- Hutan (panel gelap): `#12352A` — untuk panel SPK, footer, momen penekanan. Satu-satunya permukaan gelap.
- Kertas: `#FAF7F1` (krem hangat, ganti `#FAFAF8` netral)
- Tinta: `#22302A` (arang kehijauan, ganti slate kebiruan `#1E293B`)
- Abu hangat: `#6E7368` (ganti `#64748B`) — teks sekunder, tetap ≥ 4.5:1 di atas kertas
- Aksen: emas `#D4A017` dipakai hemat (hemat, peringkat, momen kemenangan); bata `#C0392B` hanya error
- Larangan: gradien biru-ungu, glow, kaca di mana-mana, hitam pekat `#000`

## Tipografi

- Display: **Fraunces** (serif hangat, opsz otomatis) — H1 landing, H1 dashboard, angka hero. Tight tracking, `text-wrap: balance`.
- Body/UI: **Plus Jakarta Sans** (tetap, karya desainer Jakarta — cocok untuk UMKM Indonesia).
- Data: font mono/tabular untuk kode, harga, skor (`tabular-nums`).
- Kalimat: sentence case; eyebrow huruf kecil kapitalisasi kecil secukupnya, bukan teriakan.

## Motif & Permukaan

- Kartu double-bezel dipertahankan, shadow di-tint hijau (cahaya dari atas, satu sumber).
- Tekstur grain halus hanya di hero landing + panel hutan (bukan di seluruh app).
- Radius: pil untuk tombol, 1.25rem kartu, 0.75rem input — variasi disengaja.
- Satu titik fokal per layar: headline + satu aksi utama.

## Motion (MOTION 2, beralasan)

- Reveal saat scroll: naik 14px + fade, stagger 60ms antar kartu — memandu mata, bukan dekorasi.
- Hover: angkat 1px + shadow; tekan: scale 0.98. Transisi 200-300ms.
- Hormati `prefers-reduced-motion` (sudah ada).

## Audiens (dipertahankan dari arah awal)

Pemilik UMKM pangan, kuliner, dan kerajinan non-teknis yang mengutamakan kecepatan
dan kepastian harga. Bahasa Indonesia santai, tanpa jargon, tanpa teks template generik.

## Aturan yang dipertahankan

- Mono/tabular HANYA untuk ID transaksi, harga, skor, koordinat, kode teknis.
- Motif kolektif: progress agregat kuantitas menuju MOQ selalu hidup dan jelas.
- Informasi penghematan selalu ditonjolkan (kini memakai brass agar lolos kontras).
- Setiap tampilan data punya empty state; form punya error state inline.
