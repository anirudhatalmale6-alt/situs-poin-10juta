<?php
/** Fungsi bantu umum: escaping, format angka, redirect, flash message, CSRF. */

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** 1234567 -> "1.234.567" (format Indonesia) */
function poin(int|float|string|null $n): string
{
    return number_format((float) $n, 0, ',', '.');
}

function rupiah(int|float $n): string
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

/** "2026-09-09 14:05:00" -> "9 Sep 2026, 14:05" */
function tanggal(?string $sql): string
{
    if (!$sql) {
        return '-';
    }
    $ts = strtotime($sql);
    if (!$ts) {
        return $sql;
    }
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts) - 1] . ' ' . date('Y, H:i', $ts);
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

function base_url(string $path = ''): string
{
    $root = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    if (str_ends_with($root, '/admin')) {
        $root = substr($root, 0, -6);
    }
    return ($root === '' ? '' : $root) . '/' . ltrim($path, '/');
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Sesi kedaluwarsa. Silakan muat ulang halaman dan coba lagi.');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}
