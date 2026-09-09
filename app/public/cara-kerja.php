<?php
require __DIR__ . '/../lib/bootstrap.php';

page_header([
    'title'  => 'Cara Kerja',
    'desc'   => 'Bagaimana poin dikumpulkan, dicatat, dan ditukarkan menjadi kebutuhan harian.',
    'active' => 'cara',
]);
?>
<div class="page-head"><div class="wrap">
  <h1>Cara kerja poin</h1>
  <p class="muted">Alurnya sederhana dan setiap langkah meninggalkan catatan.</p>
</div></div>

<section class="section" style="padding-top:30px"><div class="wrap">
  <div class="grid grid-2">
    <div class="card">
      <h3>1. Poin masuk</h3>
      <p>Poin bertambah dari bonus pendaftaran, absen harian, penyelesaian tugas,
         bonus referral, atau transaksi belanja. Sumber poin bisa ditambah kapan saja
         karena semuanya lewat satu fungsi yang sama di sisi server.</p>
    </div>
    <div class="card">
      <h3>2. Poin tercatat</h3>
      <p>Setiap perubahan membuat satu baris di buku besar: tanggal, jenis, jumlah,
         keterangan, dan sisa saldo saat itu. Saldo di profil dan total di buku besar
         selalu sama karena keduanya ditulis dalam satu transaksi database.</p>
    </div>
    <div class="card">
      <h3>3. Poin ditukar</h3>
      <p>Dari katalog, poin dipotong dan stok berkurang dalam satu proses.
         Kalau salah satu langkah gagal, semuanya dibatalkan — tidak ada poin
         hilang tanpa hadiah, atau hadiah keluar tanpa potongan poin.</p>
    </div>
    <div class="card">
      <h3>4. Admin memantau</h3>
      <p>Panel admin bisa menambah atau mengoreksi poin anggota, mengatur katalog,
         dan menandai penukaran selesai. Koreksi manual pun tetap masuk buku besar
         lengkap dengan nama admin yang melakukannya.</p>
    </div>
  </div>

  <h2 style="margin-top:40px">Nilai poin</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Aktivitas</th><th class="num">Poin</th><th>Catatan</th></tr></thead>
      <tbody>
        <tr><td>Bonus pendaftaran</td><td class="num amt-in">+<?= poin(config('points.signup_bonus')) ?></td><td>Sekali per akun</td></tr>
        <tr><td>Absen harian</td><td class="num amt-in">+<?= poin(config('points.daily_bonus')) ?></td><td>Sekali per hari</td></tr>
        <tr><td>Penukaran hadiah</td><td class="num amt-out">sesuai katalog</td><td>Minimal <?= poin(config('points.min_redeem')) ?> poin</td></tr>
      </tbody>
    </table>
  </div>
  <p class="small muted" style="margin-top:12px">
    Semua angka di atas diatur lewat satu file konfigurasi, jadi bisa diubah tanpa menyentuh kode halaman.
  </p>
</div></section>
<?php page_footer(); ?>
