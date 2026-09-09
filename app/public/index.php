<?php
require __DIR__ . '/../lib/bootstrap.php';

$stats = global_stats();
$goal  = (int) config('points.goal');
$pakai = min(100, $goal > 0 ? round($stats['diberi'] / $goal * 100, 1) : 0);
$top   = array_slice(rewards_active(), 0, 3);

page_header(['title' => config('site_name'), 'active' => 'beranda']);
?>

<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <span class="eyebrow">Program loyalitas</span>
      <h1>10 juta poin untuk kebutuhan harian Anda</h1>
      <p>Kumpulkan poin dari aktivitas sehari-hari, pantau saldonya secara transparan,
         lalu tukarkan dengan pulsa, paket data, sembako, dan kebutuhan lain.
         Setiap poin masuk dan keluar tercatat rapi di riwayat Anda.</p>
      <div class="hero-actions">
        <a class="btn btn-gold" href="<?= e(base_url('daftar.php')) ?>">Daftar Gratis</a>
        <a class="btn btn-ghost" href="<?= e(base_url('cara-kerja.php')) ?>">Lihat cara kerjanya</a>
      </div>
    </div>

    <div class="poin-card">
      <div class="label">Total poin dibagikan</div>
      <div class="value"><?= poin($stats['diberi']) ?></div>
      <div class="progress" style="margin-bottom:16px"><i style="width:<?= $pakai ?>%"></i></div>
      <div class="row"><span>Target program</span><span><?= poin($goal) ?> poin</span></div>
      <div class="row"><span>Anggota terdaftar</span><span><?= poin($stats['users']) ?></span></div>
      <div class="row"><span>Transaksi poin</span><span><?= poin($stats['tx']) ?></span></div>
      <div class="row"><span>Penukaran diproses</span><span><?= poin($stats['ditukar']) ?></span></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head center">
      <h2>Kenapa programnya sederhana tapi rapi</h2>
      <p>Fondasinya dibuat stabil dulu, supaya fitur lanjutan bisa ditambah tanpa membongkar ulang.</p>
    </div>
    <div class="grid grid-3">
      <div class="card">
        <div class="card-icon">📒</div>
        <h3>Buku besar poin</h3>
        <p>Saldo tidak pernah diubah diam-diam. Setiap penambahan dan pengurangan
           membuat satu baris riwayat lengkap dengan sisa saldo saat itu.</p>
      </div>
      <div class="card">
        <div class="card-icon">📱</div>
        <h3>Mobile-first</h3>
        <p>Tata letak dirancang dari layar kecil dulu. Halaman ringan, tanpa framework berat,
           jadi tetap cepat di jaringan seluler.</p>
      </div>
      <div class="card">
        <div class="card-icon">🔒</div>
        <h3>Aman sejak awal</h3>
        <p>Kata sandi di-hash, semua form dilindungi token CSRF, dan seluruh query
           memakai prepared statement.</p>
      </div>
      <div class="card">
        <div class="card-icon">🎁</div>
        <h3>Katalog fleksibel</h3>
        <p>Daftar hadiah, harga poin, dan stok diatur dari panel admin —
           tanpa perlu menyentuh kode.</p>
      </div>
      <div class="card">
        <div class="card-icon">🔍</div>
        <h3>SEO dasar siap</h3>
        <p>Judul, meta description, canonical, Open Graph, sitemap, dan data terstruktur
           sudah terpasang di setiap halaman.</p>
      </div>
      <div class="card">
        <div class="card-icon">🧩</div>
        <h3>Siap dikembangkan</h3>
        <p>Struktur kode terpisah antara data, logika poin, dan tampilan.
           E-commerce, blog, atau referral bisa menyusul.</p>
      </div>
    </div>
  </div>
</section>

<section class="section alt">
  <div class="wrap">
    <div class="section-head center"><h2>Tiga langkah saja</h2></div>
    <div class="grid grid-3">
      <div class="step"><div class="step-num">1</div>
        <div><h3>Daftar</h3><p>Buat akun dengan email dan kata sandi. Langsung dapat bonus sambutan
        <strong><?= poin(config('points.signup_bonus')) ?> poin</strong>.</p></div></div>
      <div class="step"><div class="step-num">2</div>
        <div><h3>Kumpulkan poin</h3><p>Absen harian, selesaikan tugas, atau dapat poin dari transaksi.
        Semua tercatat otomatis.</p></div></div>
      <div class="step"><div class="step-num">3</div>
        <div><h3>Tukar kebutuhan</h3><p>Pilih dari katalog, poin langsung terpotong, dan status
        penukaran bisa dipantau di dashboard.</p></div></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="flex between" style="margin-bottom:22px">
      <h2 class="mb0">Bisa ditukar dengan</h2>
      <a href="<?= e(base_url('katalog.php')) ?>">Lihat semua &rarr;</a>
    </div>
    <div class="grid grid-3">
      <?php foreach ($top as $r): ?>
        <div class="reward">
          <div class="thumb"><?= e($r['icon'] ?: '🎁') ?></div>
          <div class="body">
            <h3 class="mb0"><?= e($r['title']) ?></h3>
            <p class="small muted"><?= e($r['description']) ?></p>
            <div class="cost"><?= poin($r['cost']) ?> poin</div>
            <a class="btn btn-ghost btn-sm" href="<?= e(base_url('katalog.php')) ?>">Tukar sekarang</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section alt">
  <div class="wrap center">
    <h2>Siap mulai mengumpulkan poin?</h2>
    <p>Pendaftaran gratis dan hanya butuh satu menit.</p>
    <a class="btn btn-primary" href="<?= e(base_url('daftar.php')) ?>">Buat akun sekarang</a>
  </div>
</section>

<?php page_footer(); ?>
