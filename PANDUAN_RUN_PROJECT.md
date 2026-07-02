# Panduan Lengkap & Mudah Menjalankan Project (CapStone 2.0)

Panduan ini ditujukan untuk seluruh anggota kelompok agar bisa menjalankan project (baik *Backend/Web* maupun *Mobile*) di laptop masing-masing dengan mudah.

---

## 💻 TAHAP 1: Menyiapkan Database (Hanya Sekali)
1. Buka aplikasi **Laragon** atau **XAMPP** di laptop kalian.
2. Nyalakan layanan **MySQL** (dan **Apache/Nginx**).
3. Buka pengelola database (seperti phpMyAdmin atau HeidiSQL).
4. Buat database kosong baru, misalnya dengan nama: `capstone2.0` *(atau sesuaikan dengan kesepakatan kelompok)*.

---

## ⚙️ TAHAP 2: Setup Awal Backend (Laravel API)
Backend ini berfungsi sebagai pusat data dan panel admin web.
Buka **Terminal** (sangat disarankan memakai fitur *Terminal* bawaan dari aplikasi Laragon) dan pastikan kalian berada di dalam folder project (contoh: `C:\laragon\www\Capstone2.0`).

**Langkah ini hanya dilakukan saat pertama kali mendownload project:**
1. Instal semua keperluan backend:
   ```bash
   composer install
   npm install
   ```
2. Cari file bernama `.env.example`, copy dan ubah nama file copy-annya menjadi `.env`.
3. Buka file `.env`, lalu edit bagian database agar sesuai dengan yang kalian buat di Tahap 1:
   ```env
   DB_DATABASE=capstone2.0
   ```
4. Jalankan perintah berikut secara berurutan:
   ```bash
   php artisan key:generate
   php artisan migrate
   ```
   *(Jika ditanya "Database does not exist. Would you like to create it?", ketik: `yes`)*

---

## 🚀 TAHAP 3: Menjalankan Server & Fitur Web (Rutinitas Harian)
Setiap kali kalian ingin mulai *ngoding* atau testing, kalian wajib membuka **4 tab terminal terpisah** di dalam folder backend Laravel. Biarkan keempatnya menyala terus:

- **Terminal 1 (Server PHP / API):**
  ```bash
  php artisan serve
  ```
  *(Jika kalian memakai Virtual Host Laragon seperti `http://capstone2.0.test`, langkah ini bisa dilewati).*

- **Terminal 2 (Server Vite untuk Tampilan Web):**
  ```bash
  npm run dev
  ```

- **Terminal 3 (Server WebSocket untuk Fitur Real-Time):**
  ```bash
  php artisan reverb:start
  ```
  *(Wajib menyala agar pesanan masuk langsung muncul di layar dapur tanpa refresh).*

- **Terminal 4 (Queue Worker untuk Tugas Latar Belakang):**
  ```bash
  php artisan queue:work
  ```
  *(Wajib menyala agar notifikasi dan proses antrean berjalan lancar).*

---

## 📱 TAHAP 4: Menjalankan Aplikasi Mobile (Flutter)
Ini adalah aplikasi yang nantinya akan dipakai oleh kasir atau pelanggan.
1. Buka **Terminal Baru**.
2. Masuk ke folder aplikasi mobile:
   ```bash
   cd capstone_mobile
   ```
3. Instal keperluan Flutter (hanya sekali di awal):
   ```bash
   flutter pub get
   ```
4. Siapkan Emulator Android (dari Android Studio) atau sambungkan HP kalian pakai kabel USB.
5. Jalankan aplikasinya:
   ```bash
   flutter run
   ```

---

## 🔗 Link & Akun untuk Testing (Web Admin)

Jika kalian menjalankan `php artisan serve`, maka URL webnya adalah `http://127.0.0.1:8000`. Jika memakai Laragon, sesuaikan dengan nama hostnya (misal `http://capstone2.0.test`).

### 1. Akses Customer (Tanpa perlu Scan QR Code)
Gunakan link rahasia ini untuk langsung mencoba memesan sebagai tamu:
- **URL**: `http://127.0.0.1:8000/test-guest`

### 2. Login Admin Panel (Web)
- **URL Login**: `http://127.0.0.1:8000/admin/login`

Gunakan salah satu akun berikut sesuai fitur yang ingin kalian test:

**Role Owner (Akses Penuh):**
- **Email**: `owner@hotel.com`
- **Password**: `password`

**Role Kitchen (Akses Layar Dapur Saja):**
- **Email**: `kitchen@hotel.com`
- **Password**: `password`

**Role Finance (Akses Laporan Keuangan Saja):**
- **Email**: `finance@hotel.com`
- **Password**: `password`
