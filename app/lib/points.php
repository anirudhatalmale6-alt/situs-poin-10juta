<?php
/**
 * Inti sistem poin.
 *
 * Aturan yang dipegang di seluruh aplikasi:
 *  - Saldo TIDAK PERNAH diubah dengan UPDATE langsung dari halaman mana pun.
 *    Semua perubahan lewat add_points() / deduct_points() supaya selalu ada
 *    baris di point_transactions (buku besar) yang bisa diaudit.
 *  - Setiap baris menyimpan balance_after, jadi riwayat bisa dibaca ulang
 *    tanpa menghitung ulang dari awal.
 *  - Semua dijalankan dalam satu transaksi DB + penguncian baris user,
 *    supaya dua permintaan bersamaan tidak menghasilkan saldo salah.
 */

const POINT_TYPES = [
    'bonus_daftar' => 'Bonus pendaftaran',
    'bonus_harian' => 'Absen harian',
    'tugas'        => 'Selesai tugas',
    'referral'     => 'Bonus referral',
    'belanja'      => 'Transaksi belanja',
    'penukaran'    => 'Penukaran hadiah',
    'koreksi'      => 'Penyesuaian admin',
    'kedaluwarsa'  => 'Poin kedaluwarsa',
];

function point_type_label(string $type): string
{
    return POINT_TYPES[$type] ?? ucfirst(str_replace('_', ' ', $type));
}

/**
 * Menambah (amount > 0) atau mengurangi (amount < 0) poin milik satu user.
 *
 * @throws RuntimeException bila saldo tidak cukup atau user tidak ditemukan.
 * @return int saldo terbaru
 */
function add_points(int $userId, int $amount, string $type, string $note = '', ?string $reference = null, ?int $actorId = null): int
{
    if ($amount === 0) {
        throw new RuntimeException('Jumlah poin tidak boleh 0.');
    }

    $pdo    = db();
    $nested = $pdo->inTransaction();
    if (!$nested) {
        $pdo->beginTransaction();
    }

    try {
        // Kunci baris user selama transaksi (MySQL). SQLite mengunci di level file.
        $sql = 'SELECT id, points FROM users WHERE id = ?';
        if (db_driver() !== 'sqlite') {
            $sql .= ' FOR UPDATE';
        }
        $st = $pdo->prepare($sql);
        $st->execute([$userId]);
        $user = $st->fetch();

        if (!$user) {
            throw new RuntimeException('Pengguna tidak ditemukan.');
        }

        $balance = (int) $user['points'] + $amount;
        if ($balance < 0) {
            throw new RuntimeException('Poin tidak mencukupi.');
        }

        $pdo->prepare('UPDATE users SET points = ? WHERE id = ?')->execute([$balance, $userId]);
        $pdo->prepare(
            'INSERT INTO point_transactions (user_id, type, amount, balance_after, note, reference, created_by)
             VALUES (?,?,?,?,?,?,?)'
        )->execute([$userId, $type, $amount, $balance, $note !== '' ? $note : null, $reference, $actorId]);

        if (!$nested) {
            $pdo->commit();
        }
        return $balance;
    } catch (Throwable $ex) {
        if (!$nested && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $ex;
    }
}

/** Pembungkus agar terbaca jelas di halaman pemanggil. */
function deduct_points(int $userId, int $amount, string $type, string $note = '', ?string $reference = null, ?int $actorId = null): int
{
    return add_points($userId, -abs($amount), $type, $note, $reference, $actorId);
}

function balance_of(int $userId): int
{
    $st = db()->prepare('SELECT points FROM users WHERE id = ?');
    $st->execute([$userId]);
    return (int) $st->fetchColumn();
}

/** Riwayat transaksi terbaru milik satu user. */
function transactions_of(int $userId, int $limit = 20, int $offset = 0): array
{
    $st = db()->prepare(
        'SELECT * FROM point_transactions WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?'
    );
    $st->bindValue(1, $userId, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->bindValue(3, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
}

function transactions_count(int $userId): int
{
    $st = db()->prepare('SELECT COUNT(*) FROM point_transactions WHERE user_id = ?');
    $st->execute([$userId]);
    return (int) $st->fetchColumn();
}

/** Total masuk & keluar sepanjang waktu, untuk kartu ringkasan dashboard. */
function points_summary(int $userId): array
{
    $st = db()->prepare(
        'SELECT
            COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) AS masuk,
            COALESCE(SUM(CASE WHEN amount < 0 THEN -amount ELSE 0 END), 0) AS keluar,
            COUNT(*) AS jumlah
         FROM point_transactions WHERE user_id = ?'
    );
    $st->execute([$userId]);
    $r = $st->fetch() ?: [];
    return [
        'masuk'  => (int) ($r['masuk'] ?? 0),
        'keluar' => (int) ($r['keluar'] ?? 0),
        'jumlah' => (int) ($r['jumlah'] ?? 0),
    ];
}

/** Absen harian: satu kali per hari kalender. Mengembalikan [berhasil, pesan]. */
function claim_daily_bonus(int $userId): array
{
    $bonus = (int) config('points.daily_bonus');
    if ($bonus <= 0) {
        return [false, 'Bonus harian sedang dinonaktifkan.'];
    }

    $today = date('Y-m-d');
    $st    = db()->prepare('SELECT last_bonus_on FROM users WHERE id = ?');
    $st->execute([$userId]);
    $last = (string) $st->fetchColumn();

    if ($last !== '' && substr($last, 0, 10) === $today) {
        return [false, 'Bonus harian hari ini sudah diambil. Kembali lagi besok ya.'];
    }

    add_points($userId, $bonus, 'bonus_harian', 'Absen harian ' . $today);
    db()->prepare('UPDATE users SET last_bonus_on = ? WHERE id = ?')->execute([$today, $userId]);

    return [true, 'Berhasil! ' . poin($bonus) . ' poin masuk ke saldo Anda.'];
}

/**
 * Menukar poin dengan hadiah. Semua langkah dalam satu transaksi:
 * cek stok -> potong poin -> catat penukaran -> kurangi stok.
 */
function redeem_reward(int $userId, int $rewardId): array
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $sql = 'SELECT * FROM rewards WHERE id = ? AND active = 1';
        if (db_driver() !== 'sqlite') {
            $sql .= ' FOR UPDATE';
        }
        $st = $pdo->prepare($sql);
        $st->execute([$rewardId]);
        $reward = $st->fetch();

        if (!$reward) {
            throw new RuntimeException('Hadiah tidak tersedia.');
        }
        if ((int) $reward['stock'] <= 0) {
            throw new RuntimeException('Stok hadiah ini sedang habis.');
        }
        $min = (int) config('points.min_redeem');
        if ((int) $reward['cost'] < $min) {
            throw new RuntimeException('Penukaran minimal ' . poin($min) . ' poin.');
        }

        deduct_points($userId, (int) $reward['cost'], 'penukaran', 'Tukar: ' . $reward['title'], 'reward#' . $reward['id']);

        $pdo->prepare('INSERT INTO redemptions (user_id, reward_id, cost, status) VALUES (?,?,?,\'pending\')')
            ->execute([$userId, $rewardId, (int) $reward['cost']]);
        $pdo->prepare('UPDATE rewards SET stock = stock - 1 WHERE id = ?')->execute([$rewardId]);

        $pdo->commit();
        return [true, 'Penukaran "' . $reward['title'] . '" diterima dan sedang diproses.'];
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [false, $ex->getMessage()];
    }
}

function rewards_active(): array
{
    return db()->query('SELECT * FROM rewards WHERE active = 1 ORDER BY cost ASC')->fetchAll();
}

function redemptions_of(int $userId, int $limit = 10): array
{
    $st = db()->prepare(
        'SELECT r.*, w.title, w.icon
           FROM redemptions r JOIN rewards w ON w.id = r.reward_id
          WHERE r.user_id = ? ORDER BY r.id DESC LIMIT ?'
    );
    $st->bindValue(1, $userId, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
}

/** Angka untuk halaman depan & panel admin. */
function global_stats(): array
{
    $pdo = db();
    return [
        'users'    => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'tx'       => (int) $pdo->query('SELECT COUNT(*) FROM point_transactions')->fetchColumn(),
        'beredar'  => (int) $pdo->query('SELECT COALESCE(SUM(points),0) FROM users')->fetchColumn(),
        'diberi'   => (int) $pdo->query('SELECT COALESCE(SUM(amount),0) FROM point_transactions WHERE amount > 0')->fetchColumn(),
        'ditukar'  => (int) $pdo->query('SELECT COUNT(*) FROM redemptions')->fetchColumn(),
    ];
}
