<?php
require __DIR__ . '/../lib/bootstrap.php';

/* Teks di bawah ini adalah CONTOH. Ganti dengan ketentuan program Anda sendiri. */
$faq = [
    ['Bagaimana cara mendapatkan poin?',
     'Poin didapat dari bonus pendaftaran, absen harian, penyelesaian tugas, dan aktivitas lain yang diatur pengelola program. Setiap poin yang masuk langsung tampil di dashboard Anda.'],
    ['Apakah poin bisa kedaluwarsa?',
     'Kebijakan masa berlaku poin ditentukan pengelola program. Bila diberlakukan, poin yang hangus tetap tercatat di riwayat sebagai transaksi "Poin kedaluwarsa" sehingga terlihat jelas.'],
    ['Berapa lama proses penukaran?',
     'Setelah penukaran dibuat, statusnya "Diproses". Pengelola akan menandainya "Selesai" bila hadiah sudah dikirim. Anda bisa memantau statusnya di dashboard.'],
    ['Poin saya terpotong tapi hadiah belum diterima, bagaimana?',
     'Buka halaman dashboard dan cek status penukaran. Jika masih "Diproses", tunggu sesuai jadwal pengiriman. Bila ditolak, poin dikembalikan ke saldo Anda dan tercatat di riwayat.'],
    ['Apakah akun bisa dipakai di HP?',
     'Bisa. Situs ini dirancang mobile-first, jadi seluruh halaman berfungsi penuh di layar kecil tanpa perlu aplikasi tambahan.'],
    ['Bagaimana kalau lupa kata sandi?',
     'Hubungi pengelola melalui halaman kontak untuk permintaan reset kata sandi.'],
];

page_header([
    'title' => 'Bantuan',
    'desc'  => 'Pertanyaan yang sering ditanyakan seputar poin, penukaran, dan akun.',
    'active'=> 'bantuan',
]);
?>
<div class="page-head"><div class="wrap">
  <h1>Pusat bantuan</h1>
  <p class="muted">Jawaban singkat untuk pertanyaan yang paling sering masuk.</p>
</div></div>

<section class="section" style="padding-top:30px"><div class="wrap">
  <div class="grid grid-2">
    <?php foreach ($faq as [$q, $a]): ?>
      <div class="card"><h3><?= e($q) ?></h3><p><?= e($a) ?></p></div>
    <?php endforeach; ?>
  </div>
  <p style="margin-top:26px">Belum terjawab? <a href="<?= e(base_url('kontak.php')) ?>">Kirim pertanyaan Anda</a>.</p>
</div></section>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type' => 'Question',
        'name'  => $f[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
    ], $faq),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<?php page_footer(); ?>
