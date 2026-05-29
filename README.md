# Sistem Keamanan Kantor

Aplikasi web untuk manajemen dan pengawasan kunjungan tamu di lingkungan kantor KJRI Penang berbasis QR Code.

---

## Latar Belakang

Pengelolaan tamu di lingkungan kantor KJRI Penang sering kali masih dilakukan secara manual — menggunakan buku tamu fisik yang rawan kehilangan data, sulit dilacak, dan tidak efisien. Tidak ada mekanisme yang memastikan bahwa setiap tamu yang masuk telah mendapat izin dari pejabat yang berwenang, sehingga potensi risiko keamanan sulit dikendalikan.

**Sistem Keamanan Kantor** hadir sebagai solusi digital yang mengintegrasikan alur persetujuan kunjungan, pembuatan QR Code otomatis, dan pencatatan check-in/check-out secara real-time. Dengan sistem ini, setiap kunjungan tamu dapat dipantau secara transparan mulai dari pendaftaran hingga tamu meninggalkan gedung.

---

## Fitur Utama

### Manajemen Peran (Role-Based Access Control)
Sistem memiliki 4 peran dengan hak akses yang berbeda:

| Peran | Input Tamu | Approve Tamu | Scan QR | Kelola Sistem |
|---|:---:|:---:|:---:|:---:|
| Admin | ✓ | ✓ | ✓ | ✓ |
| Pejabat | ✓ | ✓ | — | — |
| Staff | ✓ | — | — | — |
| Satpam | — | — | ✓ | — |

### Pendaftaran Tamu
- Input data tamu: Nama, Nomor KTP, No. HP, Jenis Kendaraan, Plat Kendaraan, dan Tujuan Kunjungan
- Staff dan Pejabat dapat mendaftarkan tamu
- Tamu yang didaftarkan oleh Staff otomatis diarahkan ke Pejabat atasan untuk mendapat persetujuan

### Alur Persetujuan (Approval Workflow)
- Setiap tamu yang didaftarkan berstatus **Menunggu** hingga disetujui
- Pejabat menyetujui atau menolak kunjungan secara manual
- Pejabat hanya dapat melihat dan menyetujui tamu dari dirinya sendiri dan seluruh Staff bawahannya
- Penolakan wajib disertai catatan alasan

### QR Code Otomatis
- QR Code 8 karakter di-generate otomatis saat kunjungan disetujui
- QR Code dicetak dan diberikan kepada tamu sebagai bukti izin masuk
- Tamu dapat menunjukkan QR Code atau menyebutkan kode 8 karakter secara manual

### Scan QR & Check-in / Check-out
- Satpam dan Admin dapat melakukan scan QR menggunakan:
  - **Kamera HP/laptop** — deteksi QR Code otomatis secara real-time
  - **Kode manual** — ketik 8 karakter kode, auto-submit saat lengkap
- Sistem mencatat waktu check-in dan check-out secara otomatis
- **QR hangus permanen** setelah tamu melakukan check-out — tidak dapat digunakan kembali
- Sistem memblokir check-out jika tamu belum melakukan check-in
- Batas waktu kunjungan maksimal **48 jam** sejak check-in

### Dashboard & Statistik
- Statistik disesuaikan per peran (total tamu, menunggu persetujuan, sudah masuk, dll.)
- Log kunjungan hari ini ditampilkan secara real-time di halaman scan

### Laporan Kunjungan (Admin)
- Riwayat seluruh kunjungan tamu dengan filter status dan rentang tanggal
- Menampilkan waktu check-in dan check-out setiap tamu

### Manajemen Pengguna (Admin)
- Tambah, edit, dan hapus akun pengguna
- Atur peran dan hubungan Pejabat–Staff (satu Pejabat dapat memiliki banyak Staff)

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 13 (PHP 8.4) |
| Database | MySQL |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| QR Generator | simplesoftwareio/simple-qrcode |
| QR Scanner | html5-qrcode |
| Auth | Laravel Breeze |

---

## Instalasi

### Prasyarat
- PHP >= 8.2
- Composer
- MySQL
- Node.js & NPM

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/rhezkam25/sistem-keamanan.git
cd sistem-keamanan

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Node
npm install && npm run build

# 4. Salin file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate
```

### Konfigurasi Database

Edit file `.env` dan sesuaikan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_keamanan
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 6. Jalankan migrasi dan seeder
php artisan migrate --seed

# 7. Jalankan server
php artisan serve
```

Akses aplikasi di `http://localhost:8000`

---

## Akun Default (Seeder)

| Email | Password | Peran |
|---|---|---|
| admin@kantor.com | password | Admin |
| pejabat@kantor.com | password | Pejabat |
| staff@kantor.com | password | Staff |
| satpam@kantor.com | password | Satpam |

---

## Alur Penggunaan

```
Staff/Pejabat          Pejabat                 Satpam/Admin
     │                    │                         │
     │  Input data tamu   │                         │
     │──────────────────► │                         │
     │                    │  Setujui / Tolak         │
     │                    │─────────────────────┐    │
     │                    │  QR Code digenerate  │    │
     │                    │◄────────────────────┘    │
     │   QR diberikan ke tamu                        │
     │                                               │
     │              Tamu datang ke kantor             │
     │                                               │
     │                              Scan QR (Check-in)│
     │                              ────────────────► │
     │                                               │
     │                              Scan QR (Check-out)
     │                              ────────────────► │
     │                              QR hangus permanen│
```

---

## Lisensi

Proyek ini dibuat untuk keperluan pengembangan sistem keamanan kantor.
