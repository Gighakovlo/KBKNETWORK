# 🕸️ Network Mapping & IT Inventory System

Sistem informasi berbasis web untuk manajemen aset IT dan pemetaan topologi jaringan (Switch, PC, Backbone) secara visual. Dikembangkan menggunakan **Laravel** dan terintegrasi dengan **Microsoft SQL Server**.

Sistem ini memungkinkan pengguna untuk menggambar area gedung/lantai menggunakan kanvas interaktif dan menarik garis koneksi antar perangkat jaringan secara akurat.

---

## 🌟 Fitur Utama
* **Visual Network Mapping:** Pemetaan denah lantai dan perangkat menggunakan kanvas interaktif.
* **Manajemen Aset IT:** Pendataan detail Switch, PC, Gedung, dan Lantai.
* **Topologi Kabel:** Penarikan garis koneksi (*Backbone* dan distribusi) antar *node* perangkat.
* **SQL Server Integration:** Kompatibel penuh dengan MS SQL Server (termasuk versi *legacy* 2008 R2).

---

## 💻 Persyaratan Sistem Minimum
Pastikan lingkungan *server* / komputer Anda memenuhi spesifikasi berikut sebelum menjalankan aplikasi:
* **PHP** >= 8.x
* **Composer** (Package Manager)
* **Microsoft SQL Server** (Mendukung versi 2008 R2 hingga 2022)
* **Ekstensi PHP Aktif:** `sqlsrv`, `pdo_sqlsrv`, `gd`, `mbstring`

---

## 🚀 Instalasi Cepat (Quick Start)
Gunakan metode ini jika Anda ingin menjalankan aplikasi secara otomatis (One-Click Setup).

1. Buka folder proyek ini di komputer Anda.
2. Klik dua kali pada file **`setup.bat`**. (Script akan otomatis mengecek PHP, Composer, meng-copy `.env`, dan menginstal seluruh dependensi Laravel).
3. Buka file `.env` yang baru saja terbuat, lalu isikan kredensial SQL Server Anda:

       DB_CONNECTION=sqlsrv
       DB_HOST=127.0.0.1
       DB_PORT=1433
       DB_DATABASE=kbk_network
       DB_USERNAME=sa
       DB_PASSWORD=password_sa_anda

4. Buka terminal, jalankan perintah migrasi database: `php artisan migrate`
5. Jalankan *server* lokal: `php artisan serve`

---

## 📖 Instalasi Manual & Rinci (Detailed Guide)
Jika Anda menggunakan OS selain Windows atau ingin melakukan instalasi tahap demi tahap, ikuti panduan berikut:

### Tahap 1: Persiapan Lingkungan
1. **Clone Repository:**

       git clone [URL_REPO_ANDA]
       cd mapping-switch

2. **Install Dependensi:**

       composer install

3. **Konfigurasi Environment:**
   Duplikasi file environment bawaan.

       cp .env.example .env

4. **Generate Application Key:**

       php artisan key:generate


### Tahap 2: Konfigurasi Database
1. Buat sebuah database kosong di SQL Server Anda (contoh: `kbk_network`).
2. Sesuaikan konfigurasi di file `.env` dengan akun SQL Server Anda.
3. Lakukan migrasi tabel:

       php artisan migrate


---

## ⚠️ Troubleshooting SQL Server (Wajib Baca)
Jika aplikasi mengalami *error* **"Connection Refused"** atau tidak bisa terhubung ke database (terutama pada SQL Server 2008 R2), hal ini biasanya disebabkan oleh fitur jaringan bawaan Windows yang terkunci. 

Lakukan 2 langkah perbaikan berikut di komputer Server:

### 1. Mengaktifkan Jalur TCP/IP
* Buka **SQL Server Configuration Manager** di Windows.
* Masuk ke menu **SQL Server Network Configuration** -> **Protocols for [Nama Instance Anda]**.
* Klik kanan pada **TCP/IP** lalu pilih **Enable**.

### 2. Mencari Dynamic Port (Port Rahasia)
SQL Server seringkali tidak menggunakan *port default* (1433), melainkan *Dynamic Port* yang diacak.
* Masih di menu yang sama, klik dua kali pada **TCP/IP**.
* Pindah ke tab **IP Addresses** di bagian atas.
* *Scroll* layar hingga paling bawah ke bagian **IPAll**.
* Catat angka yang tertera pada kolom **TCP Dynamic Ports**.
* Masukkan angka tersebut ke dalam `DB_PORT=...` di file `.env` Laravel Anda.
* Restart *service* SQL Server Anda.

---
*Developed for PT. Krakatau Baja Konstruksi*









<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
