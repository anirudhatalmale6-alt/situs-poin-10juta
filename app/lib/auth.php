<?php
/** Pendaftaran, login, sesi, dan proteksi halaman. */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE || PHP_SAPI === 'cli') {
        return;
    }
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function current_user(): ?array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache ?: null;
    }
    $id = $_SESSION['uid'] ?? null;
    if (!$id) {
        $cache = false;
        return null;
    }
    $st = db()->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$id]);
    $u = $st->fetch();
    if (!$u || $u['status'] !== 'active') {
        unset($_SESSION['uid']);
        $cache = false;
        return null;
    }
    $cache = $u;
    return $u;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        flash('Silakan masuk terlebih dahulu.', 'info');
        redirect(base_url('masuk.php'));
    }
    return $u;
}

function require_admin(): array
{
    $u = require_login();
    if ($u['role'] !== 'admin') {
        http_response_code(403);
        exit('Halaman ini khusus admin.');
    }
    return $u;
}

/**
 * Mendaftarkan user baru. Mengembalikan [id, null] atau [null, array_error].
 */
function register_user(string $name, string $email, string $phone, string $password): array
{
    $errors = [];
    $name   = trim($name);
    $email  = strtolower(trim($email));
    $phone  = trim($phone);

    if (mb_strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $errors[] = 'Nomor HP tidak valid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Kata sandi minimal 8 karakter.';
    }
    if ($errors) {
        return [null, $errors];
    }

    $st = db()->prepare('SELECT id FROM users WHERE email = ?');
    $st->execute([$email]);
    if ($st->fetch()) {
        return [null, ['Email tersebut sudah terdaftar.']];
    }

    $role = in_array($email, (array) config('admin_emails'), true) ? 'admin' : 'user';
    $ins  = db()->prepare(
        'INSERT INTO users (name, email, phone, password_hash, points, role) VALUES (?,?,?,?,0,?)'
    );
    $ins->execute([$name, $email, $phone !== '' ? $phone : null, password_hash($password, PASSWORD_DEFAULT), $role]);
    $id = (int) db()->lastInsertId();

    $bonus = (int) config('points.signup_bonus');
    if ($bonus > 0) {
        add_points($id, $bonus, 'bonus_daftar', 'Bonus sambutan anggota baru');
    }
    return [$id, null];
}

/** Mengembalikan array user saat berhasil, atau null. */
function login_user(string $email, string $password): ?array
{
    $st = db()->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([strtolower(trim($email))]);
    $u = $st->fetch();

    if (!$u || !password_verify($password, $u['password_hash'])) {
        return null;
    }
    if ($u['status'] !== 'active') {
        return null;
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $u['id'];
    return $u;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
}
