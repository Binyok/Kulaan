# Kula'an — UMKM Supply Hub
Platform **Smart Collective Procurement** yang menghubungkan UMKM dengan distributor bahan baku melalui pembelian kolektif otomatis berbasis **Sistem Pendukung Keputusan (AHP + TOPSIS + SAW)**.

---

## 📌 Fitur Utama

1. **Auto-Matching Engine:**
   - Mengelompokkan kebutuhan bahan baku UMKM secara otomatis berdasarkan produk, kuantitas, area, dan deadline.
   - Mengakumulasi kuantitas pesanan hingga mencapai Minimum Order Quantity (MOQ) distributor.
   - Penanganan deadline otomatis dengan jalur pembelian cepat (fallback) jika kuota tidak terpenuhi sebelum deadline.

2. **Sistem Pendukung Keputusan (SPK):**
   - **AHP (Analytical Hierarchy Process):** Menghitung bobot kriteria (Harga 40%, Kualitas 25%, Waktu Pengiriman 20%, Jarak 10%, MOQ 5%) dengan pengujian rasio konsistensi ($CR \le 0.1$).
   - **TOPSIS:** Menentukan rekomendasi distributor terbaik berdasarkan kedekatan dengan solusi ideal positif dan terjauh dari solusi ideal negatif.
   - **SAW:** Metode pendukung scoring dan pembanding hasil ranking.

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
Semua akun default menggunakan kata sandi: `password`

| Role | Email | Nama / Usaha |
| :--- | :--- | :--- |
| **Administrator** | `admin@kulaan.id` | Administrator Kula'an |
| **UMKM 1** | `umkm.berkah@gmail.com` | Warung Berkah Kuliner (Banjarmasin Utara) |
| **UMKM 2** | `umkm.barokah@gmail.com` | Katering Barokah (Banjarmasin Barat) |
| **UMKM 3** | `umkm.sejahtera@gmail.com` | Dapur Sejahtera (Banjarmasin Timur) |
| **Distributor 1** | `distributor.borneo@gmail.com` | PT Borneo Bahan Sembako |
| **Distributor 2** | `distributor.banua@gmail.com` | CV Banua Makmur Jaya |
| **Kurir Armada** | `kurir.budi@gmail.com` | Rizky Kurir Express |

---

## 🧪 Menjalankan Pengujian (Testing)

```bash
# Menjalankan seluruh unit & feature test suite
php artisan test
```

---

## 📜 Lisensi & Hak Cipta
Hak Cipta &copy; {{ date('Y') }} Kula'an. Platform Smart Collective Procurement untuk UMKM.
