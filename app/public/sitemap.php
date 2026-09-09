<?php
require __DIR__ . '/../lib/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$base  = rtrim((string) config('site_url'), '/');
$pages = [
    ''               => '1.0',
    'index.php'      => '1.0',
    'cara-kerja.php' => '0.8',
    'katalog.php'    => '0.8',
    'bantuan.php'    => '0.6',
    'kontak.php'     => '0.5',
    'daftar.php'     => '0.7',
    'masuk.php'      => '0.4',
    'syarat.php'     => '0.3',
    'privasi.php'    => '0.3',
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $p => $prio) {
    echo "  <url>\n";
    echo '    <loc>' . e($base . '/' . $p) . "</loc>\n";
    echo '    <changefreq>weekly</changefreq>' . "\n";
    echo '    <priority>' . $prio . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
