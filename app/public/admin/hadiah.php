<?php
require __DIR__ . '/../../lib/bootstrap.php';

require_admin();

if (is_post()) {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah' || $aksi === 'ubah') {
        $data = [
            trim($_POST['title'] ?? ''),
            trim($_POST['description'] ?? ''),
            max(0, (int) ($_POST['cost'] ?? 0)),
            trim($_POST['icon'] ?? '🎁'),
            max(0, (int) ($_POST['stock'] ?? 0)),
            isset($_POST['active']) ? 1 : 0,
        ];
        if ($data[0] === '' || $data[2] <= 0) {
            flash('Nama hadiah dan harga poin wajib diisi.', 'error');
        } elseif ($aksi === 'tambah') {
            db()->prepare('INSERT INTO rewards (title, description, cost, icon, stock, active) VALUES (?,?,?,?,?,?)')
                ->execute($data);
            flash('Hadiah baru ditambahkan.');
        } else {
            $data[] = (int) ($_POST['id'] ?? 0);
            db()->prepare('UPDATE rewards SET title=?, description=?, cost=?, icon=?, stock=?, active=? WHERE id=?')
                ->execute($data);
            flash('Hadiah diperbarui.');
        }
    } elseif ($aksi === 'nonaktif') {
        db()->prepare('UPDATE rewards SET active = 0 WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        flash('Hadiah dinonaktifkan (data penukaran lama tetap tersimpan).');
    }
    redirect(base_url('admin/hadiah.php'));
}

$rewards = db()->query('SELECT * FROM rewards ORDER BY active DESC, cost ASC')->fetchAll();

page_header(['title' => 'Katalog Hadiah — Admin', 'noindex' => true]);
?>
<div class="page-head"><div class="wrap">
  <h1>Katalog hadiah</h1>
  <p class="muted">
    <a href="<?= e(base_url('admin/index.php')) ?>">Anggota</a> ·
    <a href="<?= e(base_url('admin/hadiah.php')) ?>">Katalog hadiah</a> ·
    <a href="<?= e(base_url('admin/penukaran.php')) ?>">Penukaran</a>
  </p>
</div></div>

<section class="section" style="padding-top:26px"><div class="wrap">

  <div class="card" style="margin-bottom:26px">
    <h3>Tambah hadiah baru</h3>
    <form method="post" class="grid grid-2" style="gap:14px">
      <?= csrf_field() ?>
      <input type="hidden" name="aksi" value="tambah">
      <div class="field mb0"><label>Nama hadiah</label><input class="input" name="title" required></div>
      <div class="field mb0"><label>Harga poin</label><input class="input" name="cost" type="number" min="1" required></div>
      <div class="field mb0"><label>Keterangan</label><input class="input" name="description"></div>
      <div class="field mb0"><label>Stok</label><input class="input" name="stock" type="number" min="0" value="10"></div>
      <div class="field mb0"><label>Ikon (emoji)</label><input class="input" name="icon" value="🎁" maxlength="4"></div>
      <div class="field mb0"><label>&nbsp;</label>
        <label class="small"><input type="checkbox" name="active" checked> Tampilkan di katalog</label></div>
      <div><button class="btn btn-primary" type="submit">Tambah</button></div>
    </form>
  </div>

  <?php foreach ($rewards as $r): ?>
    <div class="card" style="margin-bottom:14px<?= $r['active'] ? '' : ';opacity:.6' ?>">
      <form method="post" class="grid grid-2" style="gap:12px">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="ubah">
        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
        <div class="field mb0"><label>Nama</label><input class="input" name="title" value="<?= e($r['title']) ?>"></div>
        <div class="field mb0"><label>Harga poin</label><input class="input" name="cost" type="number" value="<?= (int) $r['cost'] ?>"></div>
        <div class="field mb0"><label>Keterangan</label><input class="input" name="description" value="<?= e($r['description']) ?>"></div>
        <div class="field mb0"><label>Stok</label><input class="input" name="stock" type="number" value="<?= (int) $r['stock'] ?>"></div>
        <div class="field mb0"><label>Ikon</label><input class="input" name="icon" value="<?= e($r['icon']) ?>" maxlength="4"></div>
        <div class="field mb0"><label>&nbsp;</label>
          <label class="small"><input type="checkbox" name="active" <?= $r['active'] ? 'checked' : '' ?>> Aktif</label></div>
        <div class="flex">
          <button class="btn btn-primary btn-sm" type="submit">Simpan</button>
          <button class="btn btn-ghost btn-sm" type="submit" name="aksi" value="nonaktif"
                  onclick="return confirm('Nonaktifkan hadiah ini?')">Nonaktifkan</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
</div></section>
<?php page_footer(); ?>
