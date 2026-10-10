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

---

## 🛠️ Stack Teknologi

- **Backend:** Laravel 12 (PHP 8.3)
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
git clone <repository-url>
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
# Menjalankan seluruh unit & feature test suite
php artisan test
```

---

## 📜 Lisensi & Hak Cipta
Hak Cipta &copy; {{ date('Y') }} Kula'an. Platform Smart Collective Procurement untuk UMKM.
