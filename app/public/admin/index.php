<?php
require __DIR__ . '/../../lib/bootstrap.php';

$admin = require_admin();

if (is_post() && ($_POST['aksi'] ?? '') === 'sesuaikan') {
    csrf_check();
    $uid    = (int) ($_POST['user_id'] ?? 0);
    $jumlah = (int) ($_POST['jumlah'] ?? 0);
    $catatan= trim($_POST['catatan'] ?? '');
    $jenis  = $_POST['jenis'] ?? 'koreksi';

    try {
        if ($jumlah === 0) {
            throw new RuntimeException('Jumlah poin tidak boleh 0.');
        }
        $saldo = add_points($uid, $jumlah, $jenis, $catatan, null, (int) $admin['id']);
        flash('Berhasil. Saldo anggota sekarang ' . poin($saldo) . ' poin.');
    } catch (Throwable $ex) {
        flash($ex->getMessage(), 'error');
    }
    redirect(base_url('admin/index.php'));
}

$cari = trim($_GET['cari'] ?? '');
if ($cari !== '') {
    $st = db()->prepare('SELECT * FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT 50');
    $st->execute(['%' . $cari . '%', '%' . $cari . '%']);
} else {
    $st = db()->query('SELECT * FROM users ORDER BY id DESC LIMIT 50');
}
$users = $st->fetchAll();
$stats = global_stats();

page_header(['title' => 'Panel Admin', 'noindex' => true]);
?>
<div class="page-head"><div class="wrap">
  <h1>Panel admin</h1>
  <p class="muted">
    <a href="<?= e(base_url('admin/index.php')) ?>">Anggota</a> ·
    <a href="<?= e(base_url('admin/hadiah.php')) ?>">Katalog hadiah</a> ·
    <a href="<?= e(base_url('admin/penukaran.php')) ?>">Penukaran</a>
  </p>
</div></div>

<section class="section" style="padding-top:26px"><div class="wrap">

  <div class="stats" style="margin-bottom:26px">
    <div class="stat"><div class="num"><?= poin($stats['users']) ?></div><div class="cap">Anggota</div></div>
    <div class="stat"><div class="num"><?= poin($stats['beredar']) ?></div><div class="cap">Poin beredar</div></div>
    <div class="stat"><div class="num"><?= poin($stats['tx']) ?></div><div class="cap">Transaksi</div></div>
    <div class="stat"><div class="num"><?= poin($stats['ditukar']) ?></div><div class="cap">Penukaran</div></div>
  </div>

  <div class="card" style="margin-bottom:26px">
    <h3>Tambah / kurangi poin anggota</h3>
    <form method="post" class="grid grid-2" style="gap:14px">
      <?= csrf_field() ?>
      <input type="hidden" name="aksi" value="sesuaikan">
      <div class="field mb0">
        <label for="user_id">Anggota</label>
        <select class="input" id="user_id" name="user_id" required>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int) $u['id'] ?>">#<?= (int) $u['id'] ?> — <?= e($u['name']) ?> (<?= poin($u['points']) ?> poin)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field mb0">
        <label for="jumlah">Jumlah poin</label>
        <input class="input" id="jumlah" name="jumlah" type="number" step="1" placeholder="contoh: 500 atau -500" required>
        <div class="hint">Angka positif menambah, negatif mengurangi.</div>
      </div>
      <div class="field mb0">
        <label for="jenis">Jenis transaksi</label>
        <select class="input" id="jenis" name="jenis">
          <?php foreach (POINT_TYPES as $k => $label): ?>
            <option value="<?= e($k) ?>" <?= $k === 'koreksi' ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field mb0">
        <label for="catatan">Keterangan</label>
        <input class="input" id="catatan" name="catatan" placeholder="alasan penyesuaian">
      </div>
      <div><button class="btn btn-primary" type="submit">Simpan penyesuaian</button></div>
    </form>
  </div>

  <form method="get" class="flex" style="margin-bottom:14px">
    <input class="input" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama atau email" style="max-width:280px">
    <button class="btn btn-ghost btn-sm" type="submit">Cari</button>
    <?php if ($cari !== ''): ?><a class="btn btn-ghost btn-sm" href="<?= e(base_url('admin/index.php')) ?>">Reset</a><?php endif; ?>
  </form>

  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama</th><th>Email</th><th class="num">Saldo poin</th><th>Peran</th><th>Terdaftar</th></tr></thead>
      <tbody>
      <?php if (!$users): ?>
        <tr><td colspan="6"><div class="empty">Tidak ada anggota yang cocok.</div></td></tr>
      <?php else: foreach ($users as $u): ?>
        <tr>
          <td class="small muted"><?= (int) $u['id'] ?></td>
          <td><?= e($u['name']) ?></td>
          <td class="small"><?= e($u['email']) ?></td>
          <td class="num"><strong><?= poin($u['points']) ?></strong></td>
          <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-in' : 'badge-neutral' ?>"><?= e($u['role']) ?></span></td>
          <td class="small nowrap"><?= e(tanggal($u['created_at'])) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div></section>
<?php page_footer(); ?>
