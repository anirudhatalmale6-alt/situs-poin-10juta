# Panduan Pengelolaan — 10 Juta Poin

Panduan singkat untuk mengelola isi situs dan data poin. Tidak perlu bisa coding
untuk sebagian besar hal di bawah ini.

---

## 1. Masuk sebagai admin

1. Buka `https://domainanda.com/masuk.php`
2. Masuk dengan akun yang emailnya terdaftar di `admin_emails` (lihat `app/config.php`)
3. Menu **Admin** akan muncul di bagian atas

Panel admin punya tiga halaman:

| Halaman | Untuk apa |
|---|---|
| Anggota | melihat daftar anggota, mencari, menambah/mengurangi poin |
| Katalog hadiah | menambah, mengubah, menonaktifkan hadiah |
| Penukaran | menandai penukaran selesai, atau menolak (poin otomatis kembali) |

---

## 2. Menambah atau mengurangi poin anggota

Panel admin → **Anggota** → kotak "Tambah / kurangi poin anggota".

- Pilih anggota
- Isi jumlah: **positif menambah** (`500`), **negatif mengurangi** (`-500`)
- Pilih jenis transaksi (mis. "Selesai tugas", "Penyesuaian admin")
- Isi keterangan — ini yang dibaca anggota di riwayatnya

Setelah disimpan, saldo berubah **dan** satu baris riwayat dibuat otomatis.
Tidak ada cara mengubah saldo tanpa meninggalkan jejak — ini disengaja.

> Sistem menolak pengurangan yang membuat saldo minus.

---

## 3. Mengelola katalog hadiah

Panel admin → **Katalog hadiah**.

- **Tambah hadiah**: isi nama, harga poin, keterangan, stok, dan satu emoji sebagai ikon
- **Ubah**: setiap hadiah punya formulirnya sendiri, klik "Simpan"
- **Nonaktifkan**: hadiah hilang dari katalog publik, tetapi riwayat penukaran lama tetap utuh
  (jangan hapus hadiah yang pernah ditukar)

Stok berkurang otomatis setiap kali ada penukaran, dan bertambah lagi bila penukaran ditolak.

---

## 4. Memproses penukaran

Panel admin → **Penukaran**. Permintaan baru selalu berada di urutan atas dengan status
**Diproses**.

- **Selesai** — pakai setelah hadiah benar-benar dikirim/diberikan
- **Tolak** — poin dikembalikan penuh ke anggota, stok dikembalikan, dan pengembalian itu
  tercatat di riwayat anggota

Keduanya tidak bisa dibatalkan lewat panel. Bila salah klik, gunakan penyesuaian poin manual
di halaman Anggota dan tulis alasannya di keterangan.

---

## 5. Mengubah nilai poin dan nama situs

Semua di satu file: `app/config.php`.

```php
'site_name'    => '10 Juta Poin',                       // nama di header & footer
'site_tagline' => 'Kumpulkan poin, tukar jadi kebutuhan harian.',
'site_url'     => 'https://domainanda.com',             // dipakai sitemap & canonical

'points' => [
    'goal'         => 10000000, // target yang tampil di progress bar
    'signup_bonus' => 1000,     // bonus anggota baru
    'daily_bonus'  => 250,      // bonus absen harian
    'min_redeem'   => 500,      // minimal poin untuk menukar
],

'admin_emails' => ['email-admin@domain.com'],
```

Simpan file, muat ulang halaman — perubahan langsung berlaku. Tidak perlu menyentuh file lain.

---

## 6. Mengubah teks halaman informasi

Teks halaman ada langsung di dalam file PHP-nya, di dalam tag HTML biasa:

| Halaman di situs | File yang diubah |
|---|---|
| Beranda | `app/public/index.php` |
| Cara Kerja | `app/public/cara-kerja.php` |
| Bantuan / FAQ | `app/public/bantuan.php` (daftar `$faq` di bagian atas file) |
| Syarat & Ketentuan | `app/public/syarat.php` |
| Kebijakan Privasi | `app/public/privasi.php` |
| Hubungi Kami | `app/public/kontak.php` |

Ubah hanya teks di antara tag, jangan menghapus tag `<?php ... ?>`.
**Sebelum situs dipublikasikan, teks Syarat, Privasi, dan Bantuan wajib diganti** —
isinya sekarang masih contoh.

---

## 7. Mengubah warna / tema

Semua warna ada di baris pertama `app/public/assets/style.css`:

```css
:root {
  --brand:      #0f766e;   /* warna utama */
  --brand-dark: #115e59;
  --gold:       #b45309;   /* aksen angka poin */
  --ink:        #0f172a;   /* warna judul */
  --bg:         #f6f8f9;   /* latar halaman */
  --radius:     14px;      /* kelengkungan sudut */
}
```

Ganti kode warnanya saja — seluruh situs ikut berubah, termasuk tombol, badge, dan hero.

---

## 8. Pesan dari form kontak

Saat ini pesan masuk disimpan ke berkas `app/db/pesan-masuk.log`. Bila nanti sudah ada
alamat email tujuan, pengiriman email tinggal diaktifkan di `app/public/kontak.php`.

---

## 9. Cadangan data

Yang wajib dicadangkan rutin:

- Database MySQL (tabel `users`, `point_transactions`, `rewards`, `redemptions`)
- Berkas `app/config.php`

`point_transactions` adalah bukti saldo setiap anggota — **jangan pernah dihapus atau
dipangkas**. Bila tabel ini hilang, saldo tidak bisa dipertanggungjawabkan lagi.

---

## 10. Pertanyaan yang sering muncul

**Saldo anggota terlihat aneh, bagaimana mengeceknya?**
Buka riwayat anggota tersebut. Kolom "Saldo setelahnya" pada baris paling atas harus sama
persis dengan saldo yang tampil di dashboard. Kalau berbeda, ada perubahan yang dilakukan
langsung di database — jangan pernah mengedit tabel `users` secara manual.

**Bisakah poin dibuat kedaluwarsa otomatis?**
Bisa, lewat cron job yang memanggil `deduct_points()` dengan jenis `kedaluwarsa`.
Belum dipasang karena kebijakannya belum ditentukan.

**Bagaimana menambah sumber poin baru (mis. dari transaksi kasir)?**
Panggil `add_points($userId, $jumlah, 'belanja', 'keterangan')` dari kode mana pun.
Pencatatan riwayat dan pembaruan saldo terjadi otomatis.
