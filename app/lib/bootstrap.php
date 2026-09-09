<?php
/** Satu-satunya file yang perlu di-require dari setiap halaman. */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/points.php';
require_once __DIR__ . '/layout.php';

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

date_default_timezone_set('Asia/Jakarta');

start_session();
