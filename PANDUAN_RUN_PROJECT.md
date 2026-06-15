# Panduan Menjalankan Project (CapStone 2.0)

⚠️ **PENTING: Gunakan Terminal Laragon**  
Agar semua perintah (`php`, `npm`, dsb) bisa dikenali dengan baik, sangat disarankan untuk **membuka Terminal langsung dari aplikasi Laragon** (klik tombol **Terminal** di aplikasi Laragon), bukan menggunakan terminal bawaan Windows atau VSCode biasa.

Pastikan database (MySQL) di Laragon sudah dalam keadaan **Start/Running**!

Langkah pertama sebelum menjalankan server, pastikan database sudah terbuat:
```bash
cd c:\laragonn\www\CapStone2.0

# Jalankan perintah ini (hanya jika database/tabel belum ada):
php artisan migrate
# Jika ditanya "Database does not exist. Would you like to create it?", ketik: yes
```

---

Setelah database siap, Anda perlu membuka beberapa tab terminal **(dari dalam terminal Laragon tersebut)** secara bersamaan:

---

## 🖥️ Terminal 1: Menjalankan Server Vite (Wajib)
Terminal ini bertugas untuk meng-compile CSS (Tailwind) dan JavaScript secara *real-time*.
```bash
npm run dev
```
*(Biarkan terminal ini tetap terbuka dan berjalan)*

---

## 📡 Terminal 2: Menjalankan WebSocket Reverb (Wajib untuk Real-time)
Terminal ini menjalankan server WebSocket agar fitur *real-time* (seperti update status pesanan langsung di layar Kitchen tanpa di-refresh) bisa berfungsi.
```bash
php artisan reverb:start
```
*(Biarkan terminal ini tetap terbuka dan berjalan)*

---

## ⚙️ Terminal 3: Menjalankan Queue Worker (Wajib)
Karena aplikasi kita menggunakan `QUEUE_CONNECTION=database`, semua *broadcast event* atau tugas di latar belakang (seperti mengirim notifikasi) dikerjakan oleh Queue Worker.
```bash
php artisan queue:work
```
*(Biarkan terminal ini tetap terbuka dan berjalan)*

---

## 🌐 Terminal 4: Menjalankan PHP Server (Opsional jika pakai Laragon)
Jika Anda menggunakan fitur *virtual host* dari Laragon (misalnya mengakses web melalui `http://capstone2.0.test`), Anda **tidak perlu** menjalankan terminal ini. 

Namun, jika Laragon sedang bermasalah atau Anda ingin mengakses via localhost biasa, jalankan:
```bash
php artisan serve
```
*(Maka web bisa diakses di `http://127.0.0.1:8000`)*

---

### Ringkasan Singkat (Cheat Sheet):
1. `npm run dev`
2. `php artisan reverb:start`
3. `php artisan queue:work`
*(Buka 3 terminal baru di VS Code, paste masing-masing perintah di atas, dan biarkan menyala).*

---

## 🔗 Link untuk Testing

Gunakan URL berikut (sesuaikan domain jika Anda menggunakan Virtual Host Laragon, misal `http://capstone2.0.test`):

### 1. Customer / Guest Access (Tanpa QR Code)
Gunakan shortcut berikut untuk masuk sebagai Guest tanpa harus melakukan scan QR Code:
- **URL**: `http://127.0.0.1:8000/test-guest`

### 2. Admin Panel
- **URL Login**: `http://127.0.0.1:8000/admin/login`

Gunakan kredensial (akun) berikut untuk login dengan role yang berbeda:

**Role Owner (Akses Penuh):**
- **Email**: `owner@hotel.com`
- **Password**: `password`

**Role Kitchen (Akses Layar Dapur):**
- **Email**: `kitchen@hotel.com`
- **Password**: `password`

**Role Finance (Akses Laporan Keuangan):**
- **Email**: `finance@hotel.com`
- **Password**: `password`
