# 🎬 PoncolVerse - Platform Streaming Film Online

![PoncolVerse](assets/images/branding.png)

**PoncolVerse** adalah platform streaming film online modern yang menyediakan pengalaman menonton terbaik dengan koleksi film berkualitas tinggi. Dibangun dengan teknologi web modern dan terintegrasi dengan sistem pembayaran Midtrans.

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Struktur Database](#-struktur-database)
- [Penggunaan](#-penggunaan)
- [Fitur Admin](#-fitur-admin)
- [API Endpoints](#-api-endpoints)
- [Troubleshooting](#-troubleshooting)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

### Untuk Pengguna
- 🎥 **Katalog Film Lengkap** - Browse film dari berbagai genre (Action, Drama, Sci-Fi, Romance, dll)
- 🔍 **Pencarian & Filter** - Cari film favorit dengan mudah
- 📱 **Responsive Design** - Akses dari smartphone, tablet, atau desktop
- ⭐ **Rating & Review** - Baca dan tulis review untuk setiap film
- 🔖 **Watchlist** - Simpan film untuk ditonton nanti
- 🎬 **Trailer Preview** - Tonton trailer sebelum menonton film
- 💳 **Sistem Berlangganan** - 3 paket langganan (Basic, Standard, Premium)
- 💬 **Testimonial** - Bagikan pengalaman Anda dengan platform
- 👤 **Profile Management** - Kelola profil dan foto profil
- 📤 **Share Film** - Bagikan film ke WhatsApp, Facebook, Twitter

### Untuk Admin
- 🎬 **Kelola Film** - CRUD (Create, Read, Update, Delete) film
- 👥 **Manajemen User** - Kelola data pengguna dan subscription
- 💬 **Moderasi Testimonial** - Approve/reject testimonial dari user
- 📊 **Dashboard Admin** - Monitor aktivitas platform
- 🎭 **Manajemen Cast** - Kelola data aktor dan karakter

---

## 🛠 Teknologi yang Digunakan

### Frontend
- **HTML5** - Struktur website
- **CSS3** - Styling dengan animasi modern
- **Vanilla JavaScript** - Interaktivitas tanpa framework
- **AOS (Animate On Scroll)** - Animasi scroll yang smooth
- **Font Awesome** - Icon library
- **Google Fonts** - Typography (Orbitron, Poppins)

### Backend
- **PHP 8.2+** - Server-side scripting
- **MySQL/MariaDB** - Database management
- **Apache/XAMPP** - Web server

### Payment Gateway
- **Midtrans Snap** - Payment gateway integration

### External APIs
- **TMDB (The Movie Database)** - Movie data dan poster

---

## 💻 Persyaratan Sistem

- **Web Server**: Apache 2.4+ (XAMPP, WAMP, atau LAMP)
- **PHP**: Versi 8.0 atau lebih tinggi
- **Database**: MySQL 5.7+ atau MariaDB 10.4+
- **Browser**: Chrome, Firefox, Safari, Edge (versi terbaru)
- **Koneksi Internet**: Diperlukan untuk load poster film dan integrasi payment

---

## 📦 Instalasi

### 1. Clone atau Download Project

```bash
# Jika menggunakan Git
git clone https://github.com/yourusername/poncolverse.git

# Atau download ZIP dan extract ke folder htdocs
```

### 2. Setup Database

1. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`)
2. Buat database baru dengan nama `databasefilm`
3. Import file SQL:
   - Klik tab **Import**
   - Pilih file `databasefilm (3).sql`
   - Klik **Go**

### 3. Konfigurasi File Config

#### A. Database Configuration (`config/config.php`)

```php
<?php
// Sesuaikan dengan konfigurasi database Anda
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Kosongkan jika tidak ada password
define('DB_NAME', 'databasefilm');
```

#### B. Midtrans Configuration (`config/midtrans_config.php`)

```php
<?php
// Daftarkan akun di https://midtrans.com untuk mendapatkan API Key
define('MIDTRANS_SERVER_KEY', 'YOUR_SERVER_KEY');
define('MIDTRANS_CLIENT_KEY', 'YOUR_CLIENT_KEY');
define('MIDTRANS_IS_PRODUCTION', false); // Set true untuk production
```

### 4. Jalankan Server

```bash
# Jika menggunakan XAMPP
# 1. Start Apache dan MySQL di XAMPP Control Panel
# 2. Akses website di browser:
http://localhost/poncolverse
```

---

## ⚙ Konfigurasi

### Akun Admin Default

Login pertama kali menggunakan akun admin:

| Field    | Value                   |
|----------|-------------------------|
| Email    | admin@poncolverse.com   |
| Password | admin123                |

> ⚠️ **PENTING**: Segera ubah password setelah login pertama!

### Mendaftar Akun Midtrans

1. Kunjungi [https://midtrans.com](https://midtrans.com)
2. Daftar akun Sandbox (untuk testing)
3. Setelah login, buka **Settings** → **Access Keys**
4. Copy **Server Key** dan **Client Key**
5. Masukkan ke file `config/midtrans_config.php`

### Upload Limit

Untuk upload foto profil, pastikan PHP configuration:

```ini
# php.ini
upload_max_filesize = 2M
post_max_size = 8M
```

---

## 🗄 Struktur Database

Database `databasefilm` memiliki 7 tabel utama:

### 1. `users` - Data Pengguna
- id, firstName, lastName, email, password
- role (admin/user)
- subscription_plan_id, subscription_start, subscription_end
- subscription_status (active/expired/none)

### 2. `movies` - Data Film
- id, title, rating, poster, trailer, watchLink
- year, duration, genre, director, plot

### 3. `subscription_plans` - Paket Berlangganan
- id, name, price, duration_days
- features, max_profiles, video_quality

### 4. `transactions` - Riwayat Transaksi
- order_id, user_id, subscription_plan_id
- transaction_status, payment_type, gross_amount

### 5. `movie_comments` - Komentar Film
- movie_id, user_id, comment, rating

### 6. `watchlist` - Film yang Disimpan
- user_id, movie_id, added_at

### 7. `website_testimonials` - Testimonial Website
- user_id, message, rating, is_approved

---

## 🚀 Penggunaan

### Untuk User Biasa

#### 1. Registrasi Akun
1. Klik tombol **Login** di navbar
2. Klik **Daftar sekarang**
3. Isi form registrasi:
   - Nama Depan & Belakang
   - Email
   - Password (min. 6 karakter)
   - Email Pemulihan
4. Klik **Daftar Sekarang**

#### 2. Login
1. Klik tombol **Login**
2. Masukkan email dan password
3. Klik **Masuk**

#### 3. Browse Film
- **Film Populer**: Scroll ke section "Film Populer Hari Ini"
- **Semua Film**: Klik menu "Semua Film" atau scroll ke bawah
- **Filter Genre**: Klik dropdown "Genre" di navbar
- **Pencarian**: Gunakan search box di navbar

#### 4. Detail Film
1. Klik card film yang ingin dilihat
2. Halaman detail menampilkan:
   - Poster & informasi film
   - Rating & genre
   - Plot/sinopsis
   - Trailer (klik untuk play)
   - Tombol Watch (jika sudah subscribe)
   - Section komentar

#### 5. Watchlist
- **Tambah ke Watchlist**: Klik icon bookmark di card film
- **Lihat Watchlist**: Klik icon bookmark di navbar
- **Hapus dari Watchlist**: Klik lagi icon bookmark

#### 6. Berlangganan
1. Scroll ke section **Paket Berlangganan**
2. Pilih paket yang sesuai:
   - **Basic**: Rp 54.000/bulan
   - **Standard**: Rp 120.000/bulan (Terpopuler)
   - **Premium**: Rp 186.000/bulan
3. Klik **Berlangganan Sekarang**
4. Anda akan diarahkan ke halaman payment Midtrans
5. Pilih metode pembayaran (GoPay, Bank Transfer, dll)
6. Selesaikan pembayaran
7. Subscription otomatis aktif setelah payment sukses

#### 7. Memberikan Testimonial
1. Scroll ke section **Testimonial**
2. Login terlebih dahulu (jika belum)
3. Pilih rating (1-5 bintang)
4. Tulis pengalaman Anda
5. Klik **Kirim Testimonial**
6. Testimonial akan muncul setelah di-approve oleh admin

---

## 🔐 Fitur Admin

Login sebagai admin untuk akses fitur manajemen:

### Dashboard Admin
```
URL: http://localhost/poncolverse/admin/index-admin.php
```

### 1. Kelola Film

**Menambah Film Baru**
1. Buka **Admin Panel** → **Kelola Film**
2. Klik **Tambah Film Baru**
3. Isi form:
   - Title, Year, Rating
   - Genre (pilih multiple)
   - Duration, Director
   - Plot/Sinopsis
   - Poster URL (dari TMDB)
   - Trailer URL (YouTube embed)
   - Watch Link
4. Klik **Simpan**

**Edit/Hapus Film**
- Klik icon edit (✏️) untuk mengubah data
- Klik icon delete (🗑️) untuk menghapus

### 2. Manajemen Testimonial

```
URL: http://localhost/poncolverse/admin/testimonials/testimonials-admin.php
```

- **Tabel Pending**: Testimonial yang menunggu approval
  - Approve ✅: Testimonial muncul di homepage
  - Reject ❌: Testimonial dihapus
  
- **Tabel Approved**: Testimonial yang sudah di-approve
  - Unapprove 🔄: Kembalikan ke status pending
  - Delete 🗑️: Hapus permanent

### 3. Monitoring User
- Lihat daftar user terdaftar
- Cek status subscription
- Lihat riwayat transaksi

---

## 🔌 API Endpoints

### Authentication
```javascript
// Login
POST /API/login.php
Body: { email, password }

// Register
POST /API/register.php
Body: { firstName, lastName, email, password, recoveryEmail }

// Logout
POST /auth/logout.php
```

### Movies
```javascript
// Get all movies
GET /API/getMovies.php

// Get movie detail
GET /movie-detail.php?id={movie_id}

// Search movies
GET /API/getMovies.php?search={query}
```

### Watchlist
```javascript
// Add to watchlist
POST /API/add_to_watchlist.php
Body: { movie_id }

// Get user watchlist
GET /API/get_watchlist.php

// Remove from watchlist
POST /API/remove_from_watchlist.php
Body: { movie_id }
```

### Testimonials
```javascript
// Submit testimonial
POST /API/submit_testimonial.php
Body: { rating, message }

// Get approved testimonials (public)
GET /API/get_testimonials.php
```

### Subscription
```javascript
// Create transaction
POST /payment.php
Body: { plan_id }

// Payment callback (Midtrans)
POST /payment_callback.php
```

---

## 🐛 Troubleshooting

### Error: "Database connection failed"
**Solusi:**
1. Pastikan MySQL/MariaDB sudah running
2. Cek kredensial di `config/config.php`
3. Pastikan database `databasefilm` sudah dibuat

### Error: "Call to undefined function curl_init()"
**Solusi:**
```ini
# Aktifkan extension di php.ini
extension=curl
```
Restart Apache setelah edit php.ini

### Film poster tidak muncul
**Solusi:**
- Pastikan ada koneksi internet
- URL poster menggunakan HTTPS (TMDB API)

### Payment Midtrans tidak muncul
**Solusi:**
1. Pastikan `MIDTRANS_CLIENT_KEY` sudah benar
2. Cek console browser untuk error JavaScript
3. Gunakan Sandbox mode untuk testing

### Session timeout terus
**Solusi:**
```ini
# Tingkatkan session lifetime di php.ini
session.gc_maxlifetime = 3600
```

### Upload foto profile gagal
**Solusi:**
1. Pastikan folder `uploads/` ada dan writable
```bash
chmod 777 uploads/
```
2. Ukuran file max 2MB
3. Format: JPG, PNG, GIF, WEBP

---

## 📁 Struktur Folder

```
poncolverse/
├── admin/                      # Admin panel
│   ├── index-admin.php         # Dashboard admin
│   ├── movies/                 # Kelola film
│   └── testimonials/           # Kelola testimonial
├── API/                        # REST API endpoints
│   ├── login.php
│   ├── register.php
│   ├── getMovies.php
│   ├── add_to_watchlist.php
│   └── submit_testimonial.php
├── assets/                     # Asset static
│   ├── css/                    # Stylesheet
│   ├── js/                     # JavaScript
│   └── images/                 # Images/branding
├── auth/                       # Authentication
│   ├── login.php
│   └── logout.php
├── cache/                      # Cache files
├── config/                     # Configuration files
│   ├── config.php              # Database config
│   └── midtrans_config.php     # Payment config
├── uploads/                    # User uploaded files
├── index.php                   # Homepage
├── movie-detail.php            # Detail film page
├── payment.php                 # Payment page
├── payment_callback.php        # Midtrans webhook
└── databasefilm (3).sql        # Database schema
```

---

## 🎨 Customization

### Mengubah Warna Theme

Edit file `assets/css/main.css`:

```css
:root {
  --primary-color: #ff003c;      /* Warna utama */
  --secondary-color: #00ff88;    /* Warna aksen */
  --bg-dark: #0a0a0a;           /* Background gelap */
  --card-bg: rgba(20, 20, 20, 0.8); /* Background card */
}
```

### Mengubah Paket Berlangganan

Edit langsung di database (tabel `subscription_plans`) atau via phpMyAdmin.

### Menambah Genre Baru

Edit file `index.php` di bagian dropdown genre:

```html
<div class="genre-item" onclick="filterAllMoviesByGenre('NewGenre')">New Genre</div>
```

---

## 🤝 Kontribusi

Kami terbuka untuk kontribusi! Jika Anda ingin berkontribusi:

1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📝 Catatan Penting

### Security Best Practices

1. **Ganti Password Admin**: Segera ubah password default
2. **HTTPS**: Gunakan SSL/TLS untuk production
3. **Environment Variables**: Simpan API keys di environment variables, bukan hardcode
4. **SQL Injection**: Sudah menggunakan prepared statements
5. **XSS Protection**: Input di-sanitize dengan `htmlspecialchars()`

### Production Deployment

Sebelum deploy ke server production:

```php
// config/midtrans_config.php
define('MIDTRANS_IS_PRODUCTION', true); // Aktifkan production mode

// Ganti dengan Production API Keys
define('MIDTRANS_SERVER_KEY', 'YOUR_PRODUCTION_SERVER_KEY');
define('MIDTRANS_CLIENT_KEY', 'YOUR_PRODUCTION_CLIENT_KEY');
```

---

## 📞 Support & Kontak

Jika ada pertanyaan atau butuh bantuan:

- **Email**: admin@poncolverse.com
- **Website**: [www.poncolverse.com](#)
- **GitHub Issues**: [Report Bug/Request Feature](#)

---

## 📜 Lisensi

Copyright © 2025 PoncolVerse. All rights reserved.

Project ini dibuat untuk tujuan edukasi dan portfolio.

---

## 🙏 Credits

- **TMDB API** - Movie data dan poster
- **Midtrans** - Payment gateway
- **Font Awesome** - Icons
- **AOS Library** - Scroll animations
- **Google Fonts** - Typography

---

## 🎯 Roadmap

Fitur yang akan datang:

- [ ] Download film untuk offline viewing
- [ ] Multi-language support (EN/ID)
- [ ] Dark/Light mode toggle
- [ ] Push notifications
- [ ] Recommendation system
- [ ] Social login (Google, Facebook)
- [ ] Mobile app (React Native/Flutter)

---

<div align="center">

**Dibuat dengan ❤️ untuk pecinta film**

⭐ Star project ini jika berguna!

</div>
