<?php
require __DIR__ . '/../lib/bootstrap.php';
page_header(['title' => 'Kebijakan Privasi', 'desc' => 'Bagaimana data anggota disimpan dan digunakan.']);
?>
<div class="page-head"><div class="wrap"><h1>Kebijakan privasi</h1>
<p class="muted">Terakhir diperbarui: <?= date('j F Y') ?></p></div></div>

<section class="section" style="padding-top:30px"><div class="wrap" style="max-width:760px">
  <div class="alert alert-info">
    Teks di halaman ini masih contoh. Sesuaikan dengan praktik penanganan data Anda yang sebenarnya.
  </div>

  <h2>Data yang dikumpulkan</h2>
  <p>Nama, alamat email, nomor HP (opsional), serta seluruh catatan transaksi poin milik anggota.</p>

  <h2>Cara penyimpanan</h2>
  <p>Kata sandi tidak pernah disimpan dalam bentuk asli — hanya hash satu arah.
     Data transaksi poin disimpan permanen sebagai bukti saldo.</p>

  <h2>Penggunaan data</h2>
  <p>Data dipakai untuk mengelola keanggotaan, memproses penukaran, dan mengirim informasi
     terkait program. Data tidak dijual kepada pihak ketiga.</p>

  <h2>Cookie</h2>
  <p>Situs hanya memakai satu cookie sesi untuk menjaga status login. Tidak ada cookie iklan
     ataupun pelacak pihak ketiga.</p>

  <h2>Hak anggota</h2>
  <p>Anggota dapat meminta koreksi data atau penghapusan akun melalui halaman kontak.</p>
</div></section>
<?php page_footer(); ?>
