# 10 Juta Poin — situs program poin (responsif, mobile-first)

Fondasi teknis untuk situs bertema **"10 jt poin untuk kebutuhan"**: halaman depan,
dashboard pengguna, halaman informasi, dan backend sederhana untuk pendaftaran/login,
penambahan–pengurangan poin, serta pencatatan transaksi poin.

**Demo bisa diklik:** lihat folder `docs/` (versi statis dengan backend tiruan di browser).
**Aplikasi sebenarnya:** folder `app/` — PHP 8 + MySQL (atau SQLite untuk uji coba lokal).

---

## Isi repo

```
app/
  config.example.php     konfigurasi (salin jadi config.php)
  lib/
    bootstrap.php        satu-satunya file yang di-require tiap halaman
    db.php               koneksi PDO + pembuatan tabel otomatis (MySQL & SQLite)
    auth.php             daftar, login, sesi, proteksi halaman
    points.php           INTI SISTEM POIN — semua penambahan/pengurangan lewat sini
    helpers.php          escaping, format angka/tanggal, CSRF, flash message
    layout.php           header/footer bersama + tag SEO
  public/                docroot — arahkan domain ke folder ini
    index.php            halaman depan
    daftar.php           pendaftaran
    masuk.php            login
    keluar.php           logout
    dashboard.php        dashboard anggota (saldo, absen harian, ringkasan)
    riwayat.php          riwayat poin lengkap + paginasi
    katalog.php          katalog penukaran + proses tukar
    profil.php           ubah nama/HP/kata sandi
    cara-kerja.php       halaman informasi
    bantuan.php          FAQ (dengan data terstruktur FAQPage)
    kontak.php           form kontak
    syarat.php           syarat & ketentuan
    privasi.php          kebijakan privasi
    sitemap.php          sitemap XML
    robots.txt
    .htaccess            kompresi, cache, header keamanan
    assets/style.css     satu-satunya stylesheet
    admin/
      index.php          daftar anggota + tambah/kurangi poin
      hadiah.php         kelola katalog hadiah
      penukaran.php      setujui / tolak penukaran (tolak = poin dikembalikan)
  db/
    seed.php             isi data contoh (khusus uji coba)
    schema.mysql.sql     skema MySQL untuk impor manual
docs/                    demo statis untuk GitHub Pages
PANDUAN.md               panduan mengelola konten dan data poin
```

## Menjalankan di komputer sendiri (5 menit)

Butuh PHP 8.1+ saja — tanpa Composer, tanpa Node, tanpa framework.

```bash
cp app/config.example.php app/config.php   # driver bawaan: sqlite, langsung jalan
php app/db/seed.php                        # opsional: data contoh
php -S localhost:8000 -t app/public
```

Buka `http://localhost:8000`.
Akun contoh dari seed: `siti@contoh.id` / `rahasia123`, admin `admin@example.com` / `rahasia123`.

## Memasang di hosting (cPanel / VPS)

1. Upload seluruh isi folder `app/` ke luar `public_html`, lalu arahkan document root ke `app/public`.
   Kalau tidak bisa mengubah document root, upload isi `app/public` ke `public_html` dan folder
   `lib/`, `db/`, `config.php` satu tingkat di atasnya.
2. Buat database MySQL, lalu salin `config.example.php` menjadi `config.php` dan isi:

```php
'db' => [
    'driver' => 'mysql',
    'host'   => 'localhost',
    'name'   => 'nama_database',
    'user'   => 'user_database',
    'pass'   => 'kata_sandi',
],
'site_url'     => 'https://domainanda.com',
'admin_emails' => ['email-admin-anda@domain.com'],
```

3. Buka situs sekali — tabel dibuat otomatis. (Alternatif: impor `app/db/schema.mysql.sql`.)
4. Daftar memakai email yang ada di `admin_emails` — akun itu langsung berperan admin.
5. Pastikan folder `app/db/` **tidak** bisa diakses publik bila memakai SQLite.

## Aturan yang dipegang kode ini

- Saldo poin **tidak pernah** diubah dengan `UPDATE` langsung dari halaman mana pun.
  Semua lewat `add_points()` / `deduct_points()` di `app/lib/points.php`, sehingga setiap
  perubahan selalu meninggalkan satu baris di tabel `point_transactions`.
- Setiap baris riwayat menyimpan `balance_after`, jadi saldo bisa diaudit tanpa hitung ulang.
- Penukaran hadiah dijalankan dalam satu transaksi database: potong poin → catat penukaran →
  kurangi stok. Bila satu langkah gagal, semuanya dibatalkan.
- Kata sandi disimpan sebagai hash (`password_hash`), form dilindungi token CSRF, dan
  seluruh query memakai prepared statement.

## Yang masih placeholder

Teks pada halaman **Syarat & Ketentuan**, **Kebijakan Privasi**, **Bantuan**, dan isi
**katalog hadiah** adalah contoh — harus diganti dengan ketentuan dan hadiah asli sebelum
situs dipublikasikan. Nilai poin (bonus daftar, absen harian, minimal penukaran) juga
masih angka contoh dan diatur di `app/config.php`.

## Yang belum ada (bisa ditambahkan menyusul)

Lupa kata sandi via email, verifikasi email, referral otomatis, pembayaran/e-commerce,
blog, dan ekspor laporan. Semuanya bisa menyusul tanpa membongkar struktur ini.
