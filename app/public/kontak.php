<?php
require __DIR__ . '/../lib/bootstrap.php';

$sent = false;
$errors = [];

if (is_post()) {
    csrf_check();
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if (mb_strlen($nama) < 2)                          { $errors[] = 'Nama wajib diisi.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))    { $errors[] = 'Email tidak valid.'; }
    if (mb_strlen($pesan) < 10)                        { $errors[] = 'Pesan minimal 10 karakter.'; }

    if (!$errors) {
        // Simpan ke file log. Ganti dengan mail() atau SMTP saat alamat tujuan sudah ada.
        $line = sprintf("[%s] %s <%s>\n%s\n%s\n", date('c'), $nama, $email, $pesan, str_repeat('-', 40));
        @file_put_contents(__DIR__ . '/../db/pesan-masuk.log', $line, FILE_APPEND);
        $sent = true;
    }
}

page_header(['title' => 'Hubungi Kami', 'desc' => 'Kirim pertanyaan atau kendala Anda kepada pengelola program.']);
?>
<div class="page-head"><div class="wrap">
  <h1>Hubungi kami</h1>
  <p class="muted">Pesan Anda akan dibalas pada jam kerja.</p>
</div></div>

<section class="section" style="padding-top:30px"><div class="wrap" style="max-width:640px">
  <?php if ($sent): ?>
    <div class="alert alert-ok">Terima kasih, pesan Anda sudah kami terima.</div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <div class="card">
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field"><label for="nama">Nama</label>
        <input class="input" id="nama" name="nama" value="<?= old('nama') ?>" required></div>
      <div class="field"><label for="email">Email</label>
        <input class="input" id="email" name="email" type="email" value="<?= old('email') ?>" required></div>
      <div class="field"><label for="pesan">Pesan</label>
        <textarea class="input" id="pesan" name="pesan" rows="5" required><?= old('pesan') ?></textarea></div>
      <button class="btn btn-primary" type="submit">Kirim pesan</button>
    </form>
  </div>

  <p class="small muted" style="margin-top:16px">
    Catatan teknis: pesan masuk saat ini disimpan ke berkas <code>db/pesan-masuk.log</code>.
    Setelah alamat email tujuan ditentukan, tinggal aktifkan pengiriman email di berkas ini.
  </p>
</div></section>
<?php page_footer(); ?>
