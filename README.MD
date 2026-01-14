# Ecommerce SLIM PHP Project – Setup & Run Guide

## 1️⃣ Requirement Sistem

* **PHP**: 7.2.19
* **MySQL**: 5.6.x
* **Web Server**: Apache / Nginx / PHP built-in server
* **Composer**: latest version

**Dependencies** (lihat `composer.json`):

```json
"require": {
    "slim/slim": "3.*",
    "catfan/medoo": "^1.7",
    "vlucas/phpdotenv": "^3.6",
    "monolog/monolog": "^1.17",
    "slim/twig-view": "^2.5",
    "rakit/validation": "^1.4",
    "adbario/slim-csrf": "^1.1",
    "adbario/slim-secure-session-middleware": "^1.3",
    "slim/flash": "^0.4.0"
}
```

---

## 2️⃣ Clone / Copy Project

1. git clone git@github.com:ajimaulana123/mini-ecommerce.git
2. git clone ..https
3. atau download zip

---

## 3️⃣ Install Dependencies

```bash
composer install
```

* Install semua package di `composer.json`

---

## 4️⃣ Setup Database

1. Buat database `ecommerce`
2. Import file SQL `db.sql` yang sudah disediakan

* Cara import via MySQL CLI:

```bash
mysql -u root -p < path\to\db.sql
```

* Default password Laragon: kosong

3. Pastikan tabel: `users`, `products`, `orders`, `payments` sudah ada
4. Pastikan ada data awal produk dan user (sesuai db.sql)

---

## 5️⃣ Konfigurasi Database

File konfigurasi: misal `bootstrapt/app.php`

```php
return [
    'database_type' => 'mysql',
    'database_name' => 'ecommerce',
    'server' => '127.0.0.1',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8'
];
```

---

## 6️⃣ Jalankan Server

1. Buka terminal di root project
2. Jalankan built-in PHP server:

```bash
php -S localhost:8888 -t public
```

3. Buka browser:

```
http://localhost:8888/
```

* Ini akan menampilkan halaman utama daftar produk
* Route lain:

  * `/auth/signin` → login
  * `/product/{id}` → detail produk
  * `/order/{id}` → ringkasan order
  * `/dashboard` → dashboard (login dulu)
  ...

---

## 7️⃣ Authentication

* User default:

| Email                                   | Password                  |
| --------------------------------------- | --------------------------|
| [user@gmail.com](mailto:user@gmail.com) | asdASD7*&^* (hash bcrypt) |

* Login: `/auth/signin`
* Logout: `/auth/signout`

---

## 8️⃣ Alur Pemesanan & Pembayaran

1. **Daftar Produk** → `/`
2. **Detail Produk** → `/product/{id}` → pilih jumlah → klik **Beli Sekarang**
3. **Ringkasan Order** → `/order/{id}` → pilih metode pembayaran → klik **Checkout Sekarang**
4. **Payment Mock**:

   * Status otomatis: `pending` → bisa disimulasikan `success` / `failed`
   * Data payment tersimpan di tabel `payments`
5. **Order**:

   * Tersimpan di tabel `orders`
   * Status order: `pending` → update `success` / `failed` sesuai payment

---

## 9️⃣ Dashboard

* Route: `/dashboard` (login user)
* Menampilkan:

  * Total order
  * Paid vs unpaid (berdasarkan payment status)
  * Transaksi terbaru: Order ID, Produk, Jumlah, Total Harga, Status, Metode Pembayaran

---

## 🔧 Tips & Notes

* Hapus cache browser jika twig tidak update
* Debug: aktifkan `display_errors`
* fitur:

  * No CRUD full
  * Payment menggunakan **mock API**

---

## ✅ Ringkasan Setup Cepat

1. Clone / unzip project
2. Composer install
3. Import db.sql
4. Konfigurasi database
5. Jalankan server: `php -S localhost:8888 -t public`
6. Buka browser: `http://localhost:8888/`
7. Login → pilih produk → order → checkout → lihat dashboard
