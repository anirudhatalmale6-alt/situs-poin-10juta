-- Skema MySQL untuk aplikasi "10 Juta Poin".
-- Tabel juga dibuat otomatis saat aplikasi pertama kali dibuka
-- (lihat app/lib/db.php). File ini disediakan untuk impor manual lewat phpMyAdmin.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(190) NOT NULL UNIQUE,
    phone         VARCHAR(30)  NULL,
    password_hash VARCHAR(255) NOT NULL,
    points        BIGINT       NOT NULL DEFAULT 0,
    role          ENUM('user','admin')      NOT NULL DEFAULT 'user',
    status        ENUM('active','blocked')  NOT NULL DEFAULT 'active',
    last_bonus_on DATE         NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buku besar poin. Append-only: tidak pernah di-UPDATE atau dihapus.
CREATE TABLE IF NOT EXISTS point_transactions (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED NOT NULL,
    type          VARCHAR(40)  NOT NULL,   -- bonus_daftar, bonus_harian, tugas, referral,
                                           -- belanja, penukaran, koreksi, kedaluwarsa
    amount        BIGINT       NOT NULL,   -- positif = masuk, negatif = keluar
    balance_after BIGINT       NOT NULL,   -- saldo sesudah transaksi ini
    note          VARCHAR(255) NULL,
    reference     VARCHAR(80)  NULL,
    created_by    INT UNSIGNED NULL,       -- id admin, bila perubahan manual
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_time (user_id, id),
    CONSTRAINT fk_tx_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rewards (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description VARCHAR(255) NULL,
    cost        BIGINT       NOT NULL,
    icon        VARCHAR(16)  NULL,
    stock       INT          NOT NULL DEFAULT 0,
    active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS redemptions (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    reward_id  INT UNSIGNED NOT NULL,
    cost       BIGINT       NOT NULL,
    status     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    note       VARCHAR(255) NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rd_user   FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_rd_reward FOREIGN KEY (reward_id) REFERENCES rewards(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Katalog hadiah contoh (silakan ganti lewat panel admin).
INSERT INTO rewards (title, description, cost, icon, stock, active) VALUES
 ('Pulsa 10.000',         'Pulsa semua operator, diproses maksimal 1x24 jam.', 1000,  '📱', 100, 1),
 ('Paket Data 3 GB',      'Kuota utama 30 hari.',                              2500,  '🌐', 60,  1),
 ('Voucher Belanja 25rb', 'Berlaku di merchant rekanan.',                      5000,  '🛒', 40,  1),
 ('Beras 5 kg',           'Diantar ke alamat terdaftar.',                      12000, '🍚', 25,  1),
 ('Token Listrik 20rb',   'Kode token dikirim ke dashboard.',                  4000,  '💡', 50,  1),
 ('Saldo E-Wallet 50rb',  'Transfer ke nomor e-wallet terdaftar.',             9000,  '💳', 30,  1);
