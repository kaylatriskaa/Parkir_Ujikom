# 🅿️ PARKIEST — Smart Parking Management System

> Sistem Manajemen Parkir berbasis web untuk UKK (Ujian Kompetensi Keahlian)
> Dibangun dengan **Laravel 12**, **MySQL**, **Tailwind CSS**, **Alpine.js**, dan **Chart.js**

---

## 📋 Fitur Utama

| Role | Fitur |
|------|-------|
| **Admin** | Kelola akun user (CRUD), atur tarif parkir, monitoring area parkir |
| **Petugas** | Input kendaraan masuk/keluar, cetak karcis, riwayat transaksi, denah slot |
| **Owner** | Laporan pendapatan, grafik Chart.js, filter periode, monitoring traffic |

### Fitur Tambahan
- 🔐 Login multi-role dengan keamanan RBAC (Role-Based Access Control)
- 🖨️ Cetak karcis/struk otomatis (thermal printer 80mm)
- ⏱️ Perhitungan biaya otomatis berdasarkan durasi parkir
- 📊 Grafik pendapatan 7 hari terakhir
- 🅿️ Visualisasi slot parkir real-time (grid hijau/merah)
- 📅 Filter data berdasarkan tanggal
- 🔄 Status akun aktif/nonaktif tanpa hapus data

---

## 🗄️ Struktur Database

**Nama Database**: `ukk_parkir`

| No | Tabel | Keterangan |
|----|-------|------------|
| 1 | `tb_user` | Data pengguna (admin, petugas, owner) |
| 2 | `tb_tarif` | Tarif parkir per jenis kendaraan |
| 3 | `tb_area_parkir` | Area/zona parkir + kapasitas |
| 4 | `tb_kendaraan` | Identitas kendaraan (plat, jenis, warna) |
| 5 | `tb_transaksi` | Transaksi parkir (masuk/keluar/biaya) |
| 6 | `tb_log_aktivitas` | Log aktivitas user |
| 7 | `tb_log` | Log tambahan sistem |

---

## 🚀 Cara Setup & Menjalankan Projek

### Prasyarat
Pastikan sudah terinstall:
- **PHP** >= 8.2
- **Composer** (package manager PHP)
- **Node.js** + **NPM** (untuk build frontend)
- **MySQL** (bisa pakai DBngin, XAMPP, MAMP, atau install langsung)
- **Git** (untuk clone)

### Langkah 1 — Clone Repository
```bash
git clone <URL_REPOSITORY>
cd Parkir_Ujikom
```

### Langkah 2 — Install Dependencies
```bash
composer install
npm install
```

### Langkah 3 — Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### Langkah 4 — Konfigurasi Database
Buka file `.env` lalu sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ukk_parkir
DB_USERNAME=root
DB_PASSWORD=
```
> ⚠️ Sesuaikan `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD` dengan konfigurasi MySQL kamu.

### Langkah 5 — Buat Database & Import Schema
1. Buat database baru bernama **`ukk_parkir`** (bisa lewat Sequel Ace, phpMyAdmin, atau terminal)
2. Import file `schema.sql` yang ada di root projek:

**Via Terminal:**
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ukk_parkir"
mysql -u root -p ukk_parkir < schema.sql
```

**Via Sequel Ace / phpMyAdmin:**
- Buat database `ukk_parkir`
- Buka file `schema.sql`, copy semua isinya
- Paste di query editor, lalu Run/Execute

### Langkah 6 — Build Frontend Assets
```bash
npm run build
```

### Langkah 7 — Jalankan Server
```bash
php artisan serve
```
Buka browser: **http://127.0.0.1:8000**

---

## 🔑 Akun Default

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Admin |
| `petugas` | `admin123` | Petugas |
| `owner` | `admin123` | Owner |

---

## 📁 Struktur Projek (File Penting)

```
Parkir_Ujikom/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/AdminController.php      # Logika admin (CRUD user, tarif)
│   │   │   ├── Auth/AuthenticatedSession...   # Logika login/logout
│   │   │   ├── Owner/OwnerController.php      # Logika owner (laporan)
│   │   │   └── Petugas/PetugasController.php  # Logika petugas (parkir)
│   │   ├── Middleware/
│   │   │   └── CheckRole.php                  # Middleware RBAC
│   │   └── Requests/Auth/
│   │       └── LoginRequest.php               # Validasi & proses login MD5
│   └── Models/
│       ├── User.php                           # Model tb_user
│       ├── Tarif.php                          # Model tb_tarif
│       ├── Area.php                           # Model tb_area_parkir
│       ├── Kendaraan.php                      # Model tb_kendaraan
│       ├── Transaksi.php                      # Model tb_transaksi
│       └── LogAktivitas.php                   # Model tb_log_aktivitas
├── resources/views/
│   ├── auth/login.blade.php                   # Halaman login
│   ├── dashboards/
│   │   ├── admin.blade.php                    # Dashboard admin
│   │   ├── petugas.blade.php                  # Dashboard petugas
│   │   └── owner.blade.php                    # Dashboard owner
│   └── petugas/cetak.blade.php                # Cetak karcis
├── routes/web.php                             # Peta URL aplikasi
├── schema.sql                                 # Query lengkap database
├── materi.txt                                 # Materi penjelasan projek
├── materi_presentasi.txt                      # Panduan presentasi + kode
├── .env                                       # Konfigurasi environment
└── README.md                                  # File ini
```

---

## 🛠️ Teknologi

| Kategori | Teknologi |
|----------|-----------|
| Backend | PHP 8.4, Laravel 12 |
| Database | MySQL / MariaDB |
| Frontend | Blade Template, Tailwind CSS |
| Interaktivitas | Alpine.js |
| Grafik | Chart.js |
| Build Tool | Vite |
| Icon | Font Awesome 6 |

---

## 📝 Rekap Perubahan yang Dilakukan

### Perbaikan Bug & Restructuring
- ✅ Seluruh projek di-restructure untuk schema UKK `ukk_parkir`
- ✅ Tabel `users` → `tb_user` dengan kolom baru (nama_lengkap, username, status_aktif)
- ✅ Tabel `tarifs` → `tb_tarif` dengan kolom `tarif_per_jam`
- ✅ Tabel `area_parkirs` → `tb_area_parkir` dengan logika `terisi` (bukan slot_tersedia)
- ✅ Tabel baru `tb_kendaraan` untuk memisahkan data kendaraan dari transaksi
- ✅ Tabel `transaksis` → `tb_transaksi` dengan kolom baru (id_parkir, durasi_jam, biaya_total)
- ✅ Sistem login diubah dari email+bcrypt ke username+MD5

### Keamanan
- ✅ Middleware RBAC (`CheckRole`) untuk kontrol akses per role
- ✅ Rate limiting pada login (blokir setelah 5x gagal)
- ✅ CSRF protection di semua form
- ✅ Mass assignment protection di semua model ($fillable)

### Pembersihan
- ✅ Hapus file tidak terpakai (TarifController, UserController, iniproject.zip)
- ✅ Bersihkan komentar berlebihan dan rapikan indentasi
- ✅ Session driver diubah ke `file` (tidak perlu tabel sessions)

---

## 📄 Lisensi

Projek ini dibuat untuk keperluan **UKK (Ujian Kompetensi Keahlian)**.
