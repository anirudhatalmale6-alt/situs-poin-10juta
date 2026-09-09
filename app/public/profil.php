<?php
require __DIR__ . '/../lib/bootstrap.php';

$user   = require_login();
$errors = [];

if (is_post()) {
    csrf_check();
    $nama  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $lama  = $_POST['password_lama'] ?? '';
    $baru  = $_POST['password_baru'] ?? '';

    if (mb_strlen($nama) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }
    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $errors[] = 'Nomor HP tidak valid.';
    }
    if ($baru !== '') {
        if (!password_verify($lama, $user['password_hash'])) {
            $errors[] = 'Kata sandi lama salah.';
        } elseif (strlen($baru) < 8) {
            $errors[] = 'Kata sandi baru minimal 8 karakter.';
        }
    }

    if (!$errors) {
        db()->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?')
            ->execute([$nama, $phone !== '' ? $phone : null, $user['id']]);
        if ($baru !== '') {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($baru, PASSWORD_DEFAULT), $user['id']]);
        }
        flash('Profil berhasil diperbarui.');
        redirect(base_url('profil.php'));
    }
}

page_header(['title' => 'Profil Saya', 'noindex' => true]);
?>
<div class="page-head"><div class="wrap"><h1>Profil saya</h1>
<p class="muted">Email <?= e($user['email']) ?> tidak dapat diubah sendiri.</p></div></div>

<section class="section" style="padding-top:30px"><div class="wrap" style="max-width:560px">
  <?php if ($errors): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <div class="card">
    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label for="name">Nama lengkap</label>
        <input class="input" id="name" name="name" value="<?= e($_POST['name'] ?? $user['name']) ?>" required></div>
      <div class="field"><label for="phone">Nomor HP</label>
        <input class="input" id="phone" name="phone" value="<?= e($_POST['phone'] ?? ($user['phone'] ?? '')) ?>"></div>

      <hr style="border:0;border-top:1px solid var(--line);margin:22px 0">
      <p class="small muted">Kosongkan dua kolom di bawah bila tidak ingin mengganti kata sandi.</p>
      <div class="field"><label for="password_lama">Kata sandi lama</label>
        <input class="input" id="password_lama" name="password_lama" type="password" autocomplete="current-password"></div>
      <div class="field"><label for="password_baru">Kata sandi baru</label>
        <input class="input" id="password_baru" name="password_baru" type="password" autocomplete="new-password"></div>

      <button class="btn btn-primary" type="submit">Simpan perubahan</button>
      <a class="btn btn-ghost" href="<?= e(base_url('dashboard.php')) ?>">Kembali</a>
    </form>
  </div>
</div></section>
<?php page_footer(); ?>
