<?php
require __DIR__ . '/../lib/bootstrap.php';

if (current_user()) {
    redirect(base_url('dashboard.php'));
}

$error = null;
if (is_post()) {
    csrf_check();
    if (login_user($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        redirect(base_url('dashboard.php'));
    }
    $error = 'Email atau kata sandi salah.';
}

page_header(['title' => 'Masuk', 'desc' => 'Masuk ke akun Anda untuk melihat saldo poin.']);
?>
<div class="wrap auth-shell">
  <div class="form-card">
    <h1 style="font-size:1.4rem">Masuk</h1>
    <p class="small muted">Belum punya akun? <a href="<?= e(base_url('daftar.php')) ?>">Daftar gratis</a>.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="email">Email</label>
        <input class="input" id="email" name="email" type="email" value="<?= old('email') ?>" required autocomplete="email">
      </div>
      <div class="field">
        <label for="password">Kata sandi</label>
        <input class="input" id="password" name="password" type="password" required autocomplete="current-password">
      </div>
      <button class="btn btn-primary btn-block" type="submit">Masuk</button>
    </form>
  </div>
</div>
<?php page_footer(); ?>
