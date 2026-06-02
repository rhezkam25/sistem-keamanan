# Sistem Keamanan KJRI Penang

[![Version](https://img.shields.io/badge/versi-1.0.1-blue)](https://github.com/rhezkam25/sistem-keamanan/releases/tag/v1.0.1)
[![Laravel](https://img.shields.io/badge/Laravel-13-red)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-purple)](https://php.net)
[![License](https://img.shields.io/badge/lisensi-MIT-green)](LICENSE)

Aplikasi web manajemen keamanan kantor KJRI Penang — mencakup pengelolaan kunjungan tamu berbasis QR Code dan sistem absensi satpam berbasis GPS dengan geofencing interaktif.

---

## Latar Belakang

Pengelolaan tamu dan pencatatan kehadiran satpam di kantor KJRI Penang sebelumnya dilakukan secara manual menggunakan buku fisik — rawan kehilangan data, tidak efisien, dan sulit dilacak. Sistem ini hadir sebagai solusi digital yang mengintegrasikan:

- Alur persetujuan kunjungan tamu dengan QR Code otomatis
- Pencatatan check-in/check-out tamu secara real-time
- Absensi satpam berbasis GPS dengan validasi geofencing
- Laporan dan ekspor data untuk keperluan administrasi

---

## Fitur Utama

### Manajemen Peran (Role-Based Access Control)

| Fitur | Admin | Pejabat | Staff | Satpam |
|---|:---:|:---:|:---:|:---:|
| Input data tamu | ✓ | ✓ | ✓ | — |
| Approve/tolak tamu | ✓ | ✓ | — | — |
| Scan QR tamu | ✓ | — | — | ✓ |
| Absensi satpam | — | — | — | ✓ |
| Lihat data absensi | ✓ | ✓* | — | — |
| Kelola pengguna | ✓ | — | — | — |
| Pengaturan sistem | ✓ | — | — | — |

*Pejabat hanya bisa melihat data absensi jika diizinkan oleh Admin (toggle per akun).

---

### Manajemen Tamu

- Registrasi tamu: Nama, No. KTP, No. HP, kendaraan, tujuan kunjungan
- Workflow approval: tamu yang diregistrasi Staff → menunggu persetujuan Pejabat
- Generate QR Code 8 karakter otomatis setelah disetujui
- Scan masuk & keluar menggunakan kamera HTML5 atau kode manual
- QR hangus permanen setelah check-out — tidak dapat digunakan ulang
- Batas kunjungan maksimal 48 jam sejak check-in
- Data tamu: pencarian, sorting kolom, filter rentang tanggal, pagination

---

### Absensi Satpam

- **GPS Real-time** — posisi diambil dengan `enableHighAccuracy` dan diperbarui tiap 20 detik
- **Geofencing Leaflet.js** — peta interaktif menampilkan posisi satpam, titik kantor, dan radius
- **Validasi server-side** — perhitungan Haversine di PHP; klien tidak dapat memanipulasi jarak
- **Foto selfie** — kamera depan diaktifkan saat absen masuk dan keluar sebagai bukti visual
- **Minimum jam kerja** — absen keluar hanya bisa dilakukan setelah memenuhi durasi kerja minimum (default 12 jam, dapat diubah admin)
- **Countdown timer** — sisa waktu menuju jam kerja minimum ditampilkan real-time
- **Deteksi fake GPS** — pemeriksaan kecepatan teleportasi (>300 m/s antar sample)
- **Riwayat absensi** — filter per bulan/tahun, data milik satpam sendiri
- **Laporan admin** — semua data absensi satpam dengan filter nama dan rentang tanggal
- **Export XLS & CSV** — unduh laporan menggunakan `maatwebsite/excel`

---

### Dashboard & Laporan

- Statistik real-time: tamu masuk hari ini, menunggu persetujuan, ditolak
- Card absensi hari ini untuk Satpam (Belum Absen / Sedang Bertugas / Selesai)
- Laporan kunjungan tamu dengan filter status dan tanggal (Admin)
- Rekap absensi bulanan per satpam: total hadir, rata-rata durasi kerja

---

### Pengaturan Sistem (Admin)

- Titik kantor: klik atau drag marker di peta Leaflet → koordinat tersimpan otomatis
- Radius geofencing: slider 50–2000 meter, lingkaran peta update real-time
- Minimum jam kerja: 1–24 jam (default 12 jam)

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 13 / PHP 8.3+ |
| Database | MySQL 8.0+ / MariaDB 10.6+ |
| Frontend | Blade + Tailwind CSS 3 + Alpine.js 3 |
| Peta & Geofencing | Leaflet.js (CDN) |
| QR Generator | simplesoftwareio/simple-qrcode 4 |
| QR Scanner | html5-qrcode (CDN) |
| Export Excel | maatwebsite/excel 3.1 |
| Role Management | Spatie Laravel Permission 7 |
| Auth | Laravel Breeze |
| Build Tool | Vite 8 |

---

## Persyaratan Sistem

- **PHP** >= 8.3 dengan extension: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD (untuk QR)
- **MySQL** 8.0+ atau MariaDB 10.6+
- **Composer** 2.x
- **Node.js** 18+ dan NPM

---

## Instalasi

```bash
# 1. Clone repository
git clone https://github.com/rhezkam25/sistem-keamanan.git
cd sistem-keamanan

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Node dan build aset
npm install && npm run build

# 4. Salin dan konfigurasi environment
cp .env.example .env
php artisan key:generate
```

Edit `.env` sesuaikan koneksi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_keamanan
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 5. Jalankan migrasi dan seeder
php artisan migrate --seed

# 6. Buat symbolic link storage
php artisan storage:link

# 7. Jalankan server
php artisan serve
```

Akses aplikasi di `http://localhost:8000`

---

## Akun Default

| Peran | Email | Password |
|---|---|---|
| Admin | admin@kantor.com | password |
| Pejabat | pejabat@kantor.com | password |
| Staff | staff@kantor.com | password |
| Satpam | satpam@kantor.com | password |

> **Catatan:** Segera ubah password setelah instalasi di lingkungan produksi.

---

## Alur Kerja

### Kunjungan Tamu

```
Staff / Pejabat          Pejabat                    Satpam / Admin
       │                    │                              │
       │  Input data tamu   │                              │
       │──────────────────► │                              │
       │                    │  Setujui → QR digenerate     │
       │                    │  Tolak   → notifikasi        │
       │                    │                              │
       │         [QR Code diberikan kepada tamu]           │
       │                                                   │
       │                              Tamu tiba di kantor  │
       │                              Scan QR (Check-in) ──►
       │                                                   │
       │                              Tamu hendak pulang   │
       │                              Scan QR (Check-out) ─►
       │                              [QR hangus permanen] │
```

### Absensi Satpam

```
Satpam
  │
  ├─ Login → Buka /absensi
  ├─ GPS aktif, peta Leaflet menampilkan posisi & radius kantor
  ├─ Verifikasi dalam radius → foto selfie → Absen Masuk
  │
  ├─ [12 jam tugas — countdown real-time]
  │
  ├─ GPS verifikasi ulang → foto selfie → Absen Keluar
  └─ Durasi kerja tercatat otomatis
```

---

## Konfigurasi Pengaturan (Admin)

1. Login sebagai Admin → buka menu **Pengaturan**
2. Klik titik kantor di peta Leaflet (atau drag marker yang ada)
3. Atur radius geofencing menggunakan slider — lingkaran di peta berubah real-time
4. Atur minimum jam kerja sesuai jadwal shift
5. Klik **Simpan Pengaturan**

> Sebelum Satpam dapat melakukan absensi, Admin **wajib** mengatur titik kantor di Pengaturan.

---

## Lisensi

Proyek ini dikembangkan untuk keperluan sistem keamanan kantor KJRI Penang.  
Dilisensikan di bawah [MIT License](LICENSE).

© 2026 KJRI Penang
