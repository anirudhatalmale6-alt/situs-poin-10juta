<?php
/** Header & footer bersama untuk seluruh halaman (termasuk tag SEO dasar). */

function page_header(array $o = []): void
{
    $site   = config('site_name');
    $title  = $o['title'] ?? $site;
    $desc   = $o['desc']  ?? config('site_tagline');
    $active = $o['active'] ?? '';
    $u      = current_user();
    $canon  = rtrim((string) config('site_url'), '/') . ($_SERVER['REQUEST_URI'] ?? '/');
    ?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title === $site ? $site . ' — ' . config('site_tagline') : $title . ' — ' . $site) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<meta name="robots" content="<?= !empty($o['noindex']) ? 'noindex,nofollow' : 'index,follow' ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($site) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:locale" content="id_ID">
<meta name="theme-color" content="#0f766e">
<link rel="preload" href="<?= e(base_url('assets/style.css')) ?>" as="style">
<link rel="stylesheet" href="<?= e(base_url('assets/style.css')) ?>">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230f766e'/><text x='16' y='22' font-size='15' font-family='sans-serif' font-weight='bold' fill='white' text-anchor='middle'>10</text></svg>">
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    'name'     => $site,
    'url'      => config('site_url'),
    'inLanguage' => 'id-ID',
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
</head>
<body>
<a class="skip-link" href="#main">Lompat ke konten</a>

<header class="site-header">
  <div class="wrap">
    <a class="logo" href="<?= e(base_url('index.php')) ?>">
      <span class="logo-mark">10</span><?= e($site) ?>
    </a>
    <button class="nav-toggle" type="button" aria-label="Buka menu" aria-expanded="false"
            onclick="var n=document.getElementById('nav');n.classList.toggle('open');this.setAttribute('aria-expanded',n.classList.contains('open'))">☰</button>
    <nav class="nav" id="nav">
      <a href="<?= e(base_url('index.php')) ?>"   class="<?= $active === 'beranda' ? 'active' : '' ?>">Beranda</a>
      <a href="<?= e(base_url('katalog.php')) ?>" class="<?= $active === 'katalog' ? 'active' : '' ?>">Tukar Poin</a>
      <a href="<?= e(base_url('cara-kerja.php')) ?>" class="<?= $active === 'cara' ? 'active' : '' ?>">Cara Kerja</a>
      <a href="<?= e(base_url('bantuan.php')) ?>" class="<?= $active === 'bantuan' ? 'active' : '' ?>">Bantuan</a>
      <?php if ($u): ?>
        <a href="<?= e(base_url('dashboard.php')) ?>" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <?php if ($u['role'] === 'admin'): ?>
          <a href="<?= e(base_url('admin/index.php')) ?>">Admin</a>
        <?php endif; ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(base_url('keluar.php')) ?>">Keluar</a>
      <?php else: ?>
        <a href="<?= e(base_url('masuk.php')) ?>">Masuk</a>
        <a class="btn btn-primary btn-sm" href="<?= e(base_url('daftar.php')) ?>">Daftar Gratis</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main id="main">
<?php
    if ($f = flash()) {
        $cls = $f['type'] === 'error' ? 'alert-error' : ($f['type'] === 'info' ? 'alert-info' : 'alert-ok');
        echo '<div class="wrap" style="padding-top:16px"><div class="alert ' . $cls . '">' . e($f['msg']) . '</div></div>';
    }
}

function page_footer(): void
{
    $site = config('site_name');
    ?>
</main>
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <h4><?= e($site) ?></h4>
        <p style="max-width:34ch"><?= e(config('site_tagline')) ?></p>
      </div>
      <div>
        <h4>Program</h4>
        <a href="<?= e(base_url('cara-kerja.php')) ?>">Cara kerja poin</a>
        <a href="<?= e(base_url('katalog.php')) ?>">Katalog penukaran</a>
        <a href="<?= e(base_url('daftar.php')) ?>">Daftar anggota</a>
      </div>
      <div>
        <h4>Bantuan</h4>
        <a href="<?= e(base_url('bantuan.php')) ?>">Pertanyaan umum</a>
        <a href="<?= e(base_url('kontak.php')) ?>">Hubungi kami</a>
      </div>
      <div>
        <h4>Legal</h4>
        <a href="<?= e(base_url('syarat.php')) ?>">Syarat &amp; ketentuan</a>
        <a href="<?= e(base_url('privasi.php')) ?>">Kebijakan privasi</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= e($site) ?>. Seluruh hak cipta dilindungi.</span>
      <span>Dibuat mobile-first — cepat di jaringan seluler.</span>
    </div>
  </div>
</footer>
</body>
</html>
<?php
}
