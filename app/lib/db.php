<?php
/**
 * Koneksi database + pembuatan tabel otomatis.
 * Mendukung MySQL (produksi) dan SQLite (lokal / demo).
 */

function config(?string $key = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $file = __DIR__ . '/../config.php';
        if (!is_file($file)) {
            $file = __DIR__ . '/../config.example.php';
        }
        $cfg = require $file;
    }
    if ($key === null) {
        return $cfg;
    }
    // dukung notasi titik: config('points.goal')
    $node = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($node) || !array_key_exists($part, $node)) {
            return null;
        }
        $node = $node[$part];
    }
    return $node;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $c      = config('db');
    $driver = $c['driver'] ?? 'sqlite';
    $opts   = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if ($driver === 'sqlite') {
        $path = $c['sqlite'];
        $dir  = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new PDO('sqlite:' . $path, null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
    } else {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'], $c['port'], $c['name'], $c['charset'] ?? 'utf8mb4'
        );
        $pdo = new PDO($dsn, $c['user'], $c['pass'], $opts);
    }

    db_migrate($pdo, $driver);
    return $pdo;
}

function db_driver(): string
{
    return config('db.driver') ?? 'sqlite';
}

/**
 * Membuat tabel bila belum ada. Aman dipanggil berkali-kali.
 * Untuk produksi Anda juga bisa mengimpor db/schema.mysql.sql secara manual.
 */
function db_migrate(PDO $pdo, string $driver): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    if ($driver === 'sqlite') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                name          TEXT    NOT NULL,
                email         TEXT    NOT NULL UNIQUE,
                phone         TEXT    NULL,
                password_hash TEXT    NOT NULL,
                points        INTEGER NOT NULL DEFAULT 0,
                role          TEXT    NOT NULL DEFAULT 'user',
                status        TEXT    NOT NULL DEFAULT 'active',
                last_bonus_on TEXT    NULL,
                created_at    TEXT    NOT NULL DEFAULT (datetime('now')),
                updated_at    TEXT    NOT NULL DEFAULT (datetime('now'))
            )");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS point_transactions (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id       INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                type          TEXT    NOT NULL,
                amount        INTEGER NOT NULL,
                balance_after INTEGER NOT NULL,
                note          TEXT    NULL,
                reference     TEXT    NULL,
                created_by    INTEGER NULL,
                created_at    TEXT    NOT NULL DEFAULT (datetime('now'))
            )");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rewards (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                title       TEXT    NOT NULL,
                description TEXT    NULL,
                cost        INTEGER NOT NULL,
                icon        TEXT    NULL,
                stock       INTEGER NOT NULL DEFAULT 0,
                active      INTEGER NOT NULL DEFAULT 1,
                created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
            )");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS redemptions (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                reward_id  INTEGER NOT NULL REFERENCES rewards(id),
                cost       INTEGER NOT NULL,
                status     TEXT    NOT NULL DEFAULT 'pending',
                note       TEXT    NULL,
                created_at TEXT    NOT NULL DEFAULT (datetime('now'))
            )");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pages (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                slug       TEXT NOT NULL UNIQUE,
                title      TEXT NOT NULL,
                body       TEXT NOT NULL,
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )");
    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name          VARCHAR(120) NOT NULL,
                email         VARCHAR(190) NOT NULL UNIQUE,
                phone         VARCHAR(30)  NULL,
                password_hash VARCHAR(255) NOT NULL,
                points        BIGINT       NOT NULL DEFAULT 0,
                role          ENUM('user','admin') NOT NULL DEFAULT 'user',
                status        ENUM('active','blocked') NOT NULL DEFAULT 'active',
                last_bonus_on DATE         NULL,
                created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS point_transactions (
                id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id       INT UNSIGNED NOT NULL,
                type          VARCHAR(40)  NOT NULL,
                amount        BIGINT       NOT NULL,
                balance_after BIGINT       NOT NULL,
                note          VARCHAR(255) NULL,
                reference     VARCHAR(80)  NULL,
                created_by    INT UNSIGNED NULL,
                created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_time (user_id, id),
                CONSTRAINT fk_tx_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rewards (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title       VARCHAR(150) NOT NULL,
                description VARCHAR(255) NULL,
                cost        BIGINT       NOT NULL,
                icon        VARCHAR(16)  NULL,
                stock       INT          NOT NULL DEFAULT 0,
                active      TINYINT(1)   NOT NULL DEFAULT 1,
                created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS redemptions (
                id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id    INT UNSIGNED NOT NULL,
                reward_id  INT UNSIGNED NOT NULL,
                cost       BIGINT       NOT NULL,
                status     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                note       VARCHAR(255) NULL,
                created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_rd_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
                CONSTRAINT fk_rd_reward FOREIGN KEY (reward_id) REFERENCES rewards(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS pages (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug       VARCHAR(80)  NOT NULL UNIQUE,
                title      VARCHAR(150) NOT NULL,
                body       TEXT         NOT NULL,
                updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    seed_rewards($pdo);
}

/** Isi katalog hadiah contoh sekali saja (bisa diubah lewat panel admin). */
function seed_rewards(PDO $pdo): void
{
    $n = (int) $pdo->query('SELECT COUNT(*) FROM rewards')->fetchColumn();
    if ($n > 0) {
        return;
    }
    $rows = [
        ['Pulsa 10.000',        'Pulsa semua operator, diproses maksimal 1x24 jam.', 1000,  '📱', 100],
        ['Paket Data 3 GB',     'Kuota utama 30 hari.',                              2500,  '🌐', 60],
        ['Voucher Belanja 25rb','Berlaku di merchant rekanan.',                      5000,  '🛒', 40],
        ['Beras 5 kg',          'Diantar ke alamat terdaftar.',                      12000, '🍚', 25],
        ['Token Listrik 20rb',  'Kode token dikirim ke dashboard.',                  4000,  '💡', 50],
        ['Saldo E-Wallet 50rb', 'Transfer ke nomor e-wallet terdaftar.',             9000,  '💳', 30],
    ];
    $st = $pdo->prepare('INSERT INTO rewards (title, description, cost, icon, stock, active) VALUES (?,?,?,?,?,1)');
    foreach ($rows as $r) {
        $st->execute($r);
    }
}
