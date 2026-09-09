<?php
/**
 * Konfigurasi aplikasi.
 * Salin file ini menjadi config.php lalu sesuaikan isinya.
 * config.php TIDAK ikut masuk ke git (lihat .gitignore).
 */

return [

    // Identitas situs — dipakai di header, footer, dan <title>
    'site_name'    => '10 Juta Poin',
    'site_tagline' => 'Kumpulkan poin, tukar jadi kebutuhan harian.',
    'site_url'     => 'http://localhost:8000',

    // Database.
    // driver: 'mysql' untuk hosting/produksi, 'sqlite' untuk coba-coba lokal.
    'db' => [
        'driver'   => 'sqlite',
        'sqlite'   => __DIR__ . '/db/poin.sqlite',

        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'poin_app',
        'user'     => 'poin_user',
        'pass'     => '',
        'charset'  => 'utf8mb4',
    ],

    // Aturan poin
    'points' => [
        'goal'         => 10000000, // target "10 juta poin" yang tampil di progress bar
        'signup_bonus' => 1000,     // poin sambutan saat user baru mendaftar
        'daily_bonus'  => 250,      // poin absen harian (1x per hari)
        'min_redeem'   => 500,      // minimal poin untuk menukar
    ],

    // Akun admin pertama. Email ini otomatis mendapat hak admin saat mendaftar.
    'admin_emails' => ['admin@example.com'],

    // Set true hanya di komputer sendiri — menampilkan pesan error lengkap.
    'debug' => false,
];
