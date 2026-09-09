<?php
/**
 * Mengisi data contoh untuk keperluan demo / uji coba.
 * Jalankan dari terminal:  php app/db/seed.php
 *
 * JANGAN dijalankan di server produksi yang sudah berisi data asli.
 */

require __DIR__ . '/../lib/bootstrap.php';

$pdo = db();

$demo = [
    ['Siti Rahayu',    'siti@contoh.id',   'user'],
    ['Budi Santoso',   'budi@contoh.id',   'user'],
    ['Dewi Lestari',   'dewi@contoh.id',   'user'],
    ['Admin Program',  'admin@example.com', 'admin'],
];

foreach ($demo as [$nama, $email, $role]) {
    $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $st->execute([$email]);
    if ($st->fetch()) {
        echo "lewati (sudah ada): $email\n";
        continue;
    }

    $pdo->prepare('INSERT INTO users (name, email, phone, password_hash, points, role) VALUES (?,?,?,?,0,?)')
        ->execute([$nama, $email, '08123456789', password_hash('rahasia123', PASSWORD_DEFAULT), $role]);
    $id = (int) $pdo->lastInsertId();

    add_points($id, (int) config('points.signup_bonus'), 'bonus_daftar', 'Bonus sambutan anggota baru');
    echo "dibuat: $email (kata sandi: rahasia123)\n";
}

// Beberapa transaksi contoh untuk akun pertama, supaya riwayat tidak kosong.
$st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$st->execute(['siti@contoh.id']);
$uid = (int) $st->fetchColumn();

if ($uid && transactions_count($uid) < 4) {
    add_points($uid, 250,   'bonus_harian', 'Absen harian');
    add_points($uid, 3500,  'tugas',        'Menyelesaikan survei kebutuhan rumah tangga');
    add_points($uid, 1500,  'referral',     'Referral: Budi Santoso bergabung');
    add_points($uid, 7250,  'belanja',      'Transaksi belanja Rp 725.000');
    add_points($uid, -2500, 'penukaran',    'Tukar: Paket Data 3 GB', 'reward#2');
    echo "riwayat contoh ditambahkan untuk siti@contoh.id\n";
}

echo "\nSelesai. Saldo saat ini:\n";
foreach ($pdo->query('SELECT name, email, points FROM users ORDER BY id') as $r) {
    printf("  %-16s %-20s %10s poin\n", $r['name'], $r['email'], poin($r['points']));
}
