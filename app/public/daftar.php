<?php
require __DIR__ . '/../lib/bootstrap.php';

if (current_user()) {
    redirect(base_url('dashboard.php'));
}

$errors = [];
if (is_post()) {
    csrf_check();
    [$id, $errors] = register_user(
        $_POST['name']  ?? '',
        $_POST['email'] ?? '',
        $_POST['phone'] ?? '',
        $_POST['password'] ?? ''
    );
    if ($id) {
        login_user($_POST['email'], $_POST['password']);
        flash('Selamat datang! Bonus ' . poin(config('points.signup_bonus')) . ' poin sudah masuk.');
        redirect(base_url('dashboard.php'));
    }
}

page_header(['title' => 'Daftar Anggota', 'desc' => 'Buat akun gratis dan mulai kumpulkan poin.', 'noindex' => false]);
?>
<div class="wrap auth-shell">
  <div class="form-card">
    <h1 style="font-size:1.4rem">Daftar gratis</h1>
    <p class="small muted">Sudah punya akun? <a href="<?= e(base_url('masuk.php')) ?>">Masuk di sini</a>.</p>

    <?php if ($errors): ?>
      <div class="alert alert-error"><strong>Periksa kembali:</strong>
        <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="name">Nama lengkap</label>
        <input class="input" id="name" name="name" value="<?= old('name') ?>" required autocomplete="name">
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input class="input" id="email" name="email" type="email" value="<?= old('email') ?>" required autocomplete="email">
      </div>
      <div class="field">
        <label for="phone">Nomor HP <span class="muted">(opsional)</span></label>
        <input class="input" id="phone" name="phone" type="tel" value="<?= old('phone') ?>" autocomplete="tel">
      </div>
      <div class="field">
        <label for="password">Kata sandi</label>
        <input class="input" id="password" name="password" type="password" required autocomplete="new-password">
        <div class="hint">Minimal 8 karakter.</div>
      </div>
      <button class="btn btn-primary btn-block" type="submit">Buat akun</button>
    </form>

    <p class="small muted" style="margin:16px 0 0">
      Dengan mendaftar Anda menyetujui
      <a href="<?= e(base_url('syarat.php')) ?>">syarat &amp; ketentuan</a>.
    </p>
  </div>
</div>
<?php page_footer(); ?>
