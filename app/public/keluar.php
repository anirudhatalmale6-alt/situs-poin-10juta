<?php
require __DIR__ . '/../lib/bootstrap.php';

logout_user();
start_session();
flash('Anda sudah keluar. Sampai jumpa lagi!');
redirect(base_url('index.php'));
