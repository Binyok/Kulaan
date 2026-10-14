# Kula'an — UMKM Supply Hub
Platform **Smart Collective Procurement** yang menghubungkan UMKM dengan distributor bahan baku melalui pembelian kolektif otomatis berbasis **Sistem Pendukung Keputusan (AHP + TOPSIS + SAW)**.

---

## 📌 Fitur Utama

1. **Auto-Matching Engine:**
   - Mengelompokkan kebutuhan bahan baku UMKM secara otomatis berdasarkan produk, kuantitas, area, dan deadline.
   - Mengakumulasi kuantitas pesanan hingga mencapai Minimum Order Quantity (MOQ) distributor.
   - Penanganan deadline otomatis dengan jalur pembelian cepat (fallback) jika kuota tidak terpenuhi sebelum deadline.

2. **Sistem Pendukung Keputusan (SPK):**
   - **AHP (Analytical Hierarchy Process):** Menghitung pembobotan kriteria secara terstruktur melalui matriks perbandingan berpasangan (*pairwise comparison* Skala Saaty 1–9) dengan verifikasi konsistensi ($CR \le 0.1$).
     - **Matriks Perbandingan Berpasangan Default (5 Kriteria):**
       - $C_1$ (Harga / Cost): **42.45%** (0.4245)
       - $C_2$ (Kualitas / Benefit): **27.28%** (0.2728)
       - $C_3$ (Waktu Pengiriman / Benefit): **17.72%** (0.1772)
       - $C_4$ (Jarak ke UMKM / Cost): **8.36%** (0.0836)
       - $C_5$ (Minimum Order Quantity / Cost): **4.19%** (0.0419)
     - **Hasil Uji Konsistensi:** $\lambda_{\max} = 5.1376$, $CI = 0.0344$, $RI = 1.12$, $CR = 0.0307 \le 0.10$ (**Konsisten**).
   - **Kalkulasi Jarak Nyata (Haversine Formula):** Kriteria jarak ($C_4$) dihitung secara dinamis dari koordinat GPS (Latitude/Longitude) distributor ke titik koordinat UMKM dalam grup pengadaan.
   - **TOPSIS (Technique for Order Preference by Similarity to Ideal Solution):** Menentukan rekomendasi distributor terbaik berdasarkan kedekatan relatif terhadap Solusi Ideal Positif ($A^+$) dan terjauh dari Solusi Ideal Negatif ($A^-$).
   - **SAW (Simple Additive Weighting):** Metode pendukung pembobotan terpadu (*additive utility*) sebagai pembanding hasil ranking.

3. **Grosir Kolektif & Transparansi Penghematan:**
   - Menghitung harga tier grosir berdasarkan volume kolektif.
   - Menerbitkan invoice individual dengan tampilan transparan nominal penghematan dibanding patokan eceran pasar.

4. **Integrasi Payment Gateway (Midtrans):**
   - Mendukung pembayaran via QRIS dan Virtual Account.
   - Validasi status transaksi via Webhook dengan enkripsi HMAC SHA512 signature.

5. **Realtime GPS Tracking & Maps:**
   - Peta interaktif Leaflet.js (OpenStreetMap).
   - Pengiriman lokasi GPS realtime oleh kurir armada saat pengiriman berjalan.
   - Riwayat pengiriman dengan filter status (berjalan/selesai) dan pencarian nomor, distributor, tanggal.

6. **Usulan Produk Custom:**
   - Distributor dapat mengusulkan produk di luar katalog master.
   - Admin mereview (setujui/tolak beralasan); produk yang disetujui otomatis masuk katalog master beserta listing pengusul.

7. **Wilayah Dinamis (Sidoarjo & Nasional):**
   - Pilih provinsi → kota/kabupaten → kecamatan → desa bertingkat via API wilayah Indonesia, default Jawa Timur – Sidoarjo.
   - Tersimpan di profil UMKM/distributor dan dipakai sebagai area pengiriman pengajuan.

---

## 🔄 Alur Sistem End-to-End

1. **UMKM mengajukan kebutuhan** (`umkm/requests`): pilih produk, kuantitas, deadline, area pengiriman.
2. **Auto-matching** (`MatchingEngineService::processMatchingForProduct`): permintaan seproduk dengan total kuantitas ≥ MOQ terkecil digabung jadi satu grup (`matching_groups` + `matching_members`).
3. **SPK memilih distributor** (`DistributorRecommendationService::recommendDistributor`): AHP + TOPSIS (+ SAW sebagai validasi silang) → distributor terbaik dicatat di grup + riwayat `spk_calculation_histories`.
4. **Order kolektif dibuat** (`CollectiveOrderService::createOrderFromMatchingGroup`): satu order + invoice individual per UMKM.
5. **UMKM membayar** via QRIS/VA (Midtrans, webhook HMAC SHA512) → item Lunas; kalau semua lunas, order jadi PAID.
6. **Distributor memproses**: PAID → CONFIRMED → PREPARING → READY_TO_SHIP (transisi dikunci, tak bisa loncat/mundur), lalu assign kurir → shipment PENDING.
7. **Kurir mengantar**: start → IN_TRANSIT (GPS realtime tiap detik) → complete → DELIVERED + order COMPLETED.
8. **UMKM memberi ulasan** (hanya setelah DELIVERED/COMPLETED): rating merata-rata otomatis memperbaiki reputasi distributor.

Peran lain: **admin** mengelola kategori, katalog master, kriteria SPK (bobot AHP + uji sensitivitas + ekspor PDF), dan moderasi usulan produk. **Kurir** hanya melihat tugasnya sendiri.

---

## 🧮 SPK di Dalam Kode

| Metode | File | Peran |
| :--- | :--- | :--- |
| AHP (bobot + uji konsistensi CR) | `app/Services/Spk/AhpService.php` | Hitung bobot dari matriks pairwise; dipakai `SpkCriteriaController` (halaman `/admin/spk`) dan sebagai bobot aktif rekomendasi |
| Jarak Haversine | `DistributorRecommendationService::calculateHaversineDistance()` | Kriteria C4: jarak distributor → rata-rata titik UMKM grup |
| TOPSIS (rekomendasi utama) | `app/Services/Spk/TopsisService.php` | Ranking alternatif vs solusi ideal positif/negatif |
| SAW (validasi silang) | `app/Services/Spk/SawService.php` | Ranking pembanding tiap kalkulasi |
| Orkestrasi + riwayat | `app/Services/Spk/DistributorRecommendationService.php` | Susun matriks keputusan, jalankan TOPSIS+SAW, simpan `spk_calculation_histories`, tetapkan pemenang ke grup |
| Uji sensitivitas | `SpkCriteriaController@sensitivity` (halaman `/admin/spk/sensitivity`) | Hitung ulang ranking dengan skenario bobot (fokus biaya/kualitas/jarak/bobot rata) untuk menguji stabilitas pemenang |

Lima kriteria default: C1 Harga (cost), C2 Kualitas rating (benefit), C3 Waktu kirim (benefit, diinvers), C4 Jarak (cost), C5 MOQ (cost).

---

## 🔒 Jaminan ACID (Transaksi Database)

Setiap alur yang menulis ke ≥2 tabel dibungkus `DB::transaction()` sehingga gagal di tengah = rollback total, tidak ada data setengah jalan:

| Alur | Lokasi |
| :--- | :--- |
| Webhook Midtrans (payment + item + order + event) | `MidtransService::handleWebhookNotification()` |
| Pembuatan Snap payment | `MidtransService::createSnapPayment()` |
| Pembuatan order kolektif | `CollectiveOrderService::createOrderFromMatchingGroup()` |
| Pembentukan grup matching | `MatchingEngineService::processMatchingForProduct()` |
| Registrasi + Google OAuth | `RegisteredUserController@store`, `GoogleController` |
| Kurir mulai antar (shipment + order) | `CourierShipmentController@startDelivery()` |
| Kurir posting GPS (titik + posisi kurir) | `CourierShipmentController@postGpsLocation()` |
| Kurir selesai antar (shipment + order) | `CourierShipmentController@completeDelivery()` |
| Assign kurir (shipment + order) | `OrderManagementController@assignCourier()` |
| Kadaluarsa order terpusat | `ExpireOrdersCommand` |
| Simpan tier harga (hapus + tulis ulang) | `SupplierProductController@updateTiers()` |
| Approve usulan produk (master + listing) | `ProductProposalController@approve()` |

---

## 🛠️ Stack Teknologi

- **Backend:** Laravel 13 (PHP 8.3)
- **Database:** MySQL (`kulaan_db`)
- **Frontend:** Blade, Tailwind CSS v4, Alpine.js, Leaflet.js
- **Payment Gateway:** Midtrans (Sandbox & Webhook Validation)
- **Code Quality & Testing:** PHPUnit / Pest, Laravel Pint

---

## 🚀 Panduan Instalasi & Pengoperasian

### 1. Prasyarat
- PHP 8.2 atau PHP 8.3
- Composer 2.x
- MySQL Server (Laragon / XAMPP / Native)
- Node.js & NPM

### 2. Langkah Instalasi
```bash
# Clone repository & masuk ke direktori
git clone https://github.com/Binyok/Kulaan.git
cd Kulaan

# Install dependensi PHP & JavaScript
composer install
npm install

# Salin environment file & generate key
cp .env.example .env
php artisan key:generate

# Konfigurasi database MySQL pada .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=kulaan_db
# DB_USERNAME=root
# DB_PASSWORD=

# Jalankan migrasi dan seeder awal
php artisan migrate --seed

# Build asset CSS & JS
npm run build
```

### 3. Akun Penguji (Default Credentials)
Total 10 akun dari `RoleAndUserSeeder`. Semua akun menggunakan kata sandi: `password`

| Role | Email | Nama / Usaha |
| :--- | :--- | :--- |
| **Administrator** | `admin@kulaan.id` | Administrator Kula'an |
| **UMKM 1** | `umkm.maju@gmail.com` | Budi Santoso — Kedai Maju Jaya (Banjarmasin Utara) |
| **UMKM 2** | `umkm.nusantara@gmail.com` | Siti Rahma — Dapur Nusantara (Banjarmasin Barat) |
| **UMKM 3** | `umkm.berkah@gmail.com` | Andi Pratama — Warung Berkah (Banjarmasin Timur) |
| **Distributor 1** | `distributor.borneo@gmail.com` | PT Borneo Bahan Sembako (rating 4.85) |
| **Distributor 2** | `distributor.banua@gmail.com` | CV Banua Makmur Jaya (rating 4.70) |
| **Distributor 3** | `distributor.kalimantan@gmail.com` | UD Kalimantan Agro Pangan (rating 4.60) |
| **Kurir 1** | `kurir.rizky@gmail.com` | Rizky Kurir Express (Mobil Box) |
| **Kurir 2** | `kurir.arif@gmail.com` | Arif Delivery Services (Pickup) |
| **Kurir 3** | `kurir.fajar@gmail.com` | Fajar Motor Cargo (Motor) |

---

## 🧪 Menjalankan Pengujian (Testing)

```bash
# Menjalankan seluruh unit & feature test suite (85 test:
# fungsional, SPK, keamanan webhook, otorisasi, dan regresi UI)
php artisan test
```

---

## 📜 Lisensi & Hak Cipta
Hak Cipta &copy; 2026 Kula'an. Platform Smart Collective Procurement untuk UMKM.
