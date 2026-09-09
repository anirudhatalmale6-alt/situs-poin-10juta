<?php
require __DIR__ . '/../../lib/bootstrap.php';

$admin = require_admin();

if (is_post()) {
    csrf_check();
    $id   = (int) ($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';

    $st = db()->prepare('SELECT * FROM redemptions WHERE id = ?');
    $st->execute([$id]);
    $rd = $st->fetch();

    if (!$rd) {
        flash('Data penukaran tidak ditemukan.', 'error');
    } elseif ($rd['status'] !== 'pending') {
        flash('Penukaran ini sudah diproses sebelumnya.', 'info');
    } elseif ($aksi === 'setujui') {
        db()->prepare("UPDATE redemptions SET status = 'approved' WHERE id = ?")->execute([$id]);
        flash('Penukaran ditandai selesai.');
    } elseif ($aksi === 'tolak') {
        // Poin dikembalikan penuh dan stok dikembalikan, semuanya tercatat.
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE redemptions SET status = 'rejected' WHERE id = ?")->execute([$id]);
            $pdo->prepare('UPDATE rewards SET stock = stock + 1 WHERE id = ?')->execute([(int) $rd['reward_id']]);
            add_points((int) $rd['user_id'], (int) $rd['cost'], 'koreksi',
                       'Pengembalian poin — penukaran #' . $id . ' ditolak', 'redemption#' . $id, (int) $admin['id']);
            $pdo->commit();
            flash('Penukaran ditolak dan ' . poin($rd['cost']) . ' poin dikembalikan ke anggota.');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            flash('Gagal: ' . $ex->getMessage(), 'error');
        }
    }
    redirect(base_url('admin/penukaran.php'));
}

$rows = db()->query(
    'SELECT r.*, u.name AS user_name, u.email, w.title, w.icon
       FROM redemptions r
       JOIN users u   ON u.id = r.user_id
       JOIN rewards w ON w.id = r.reward_id
      ORDER BY (CASE WHEN r.status = \'pending\' THEN 0 ELSE 1 END), r.id DESC
      LIMIT 100'
)->fetchAll();

page_header(['title' => 'Penukaran — Admin', 'noindex' => true]);
?>
<div class="page-head"><div class="wrap">
  <h1>Permintaan penukaran</h1>
  <p class="muted">
    <a href="<?= e(base_url('admin/index.php')) ?>">Anggota</a> ·
    <a href="<?= e(base_url('admin/hadiah.php')) ?>">Katalog hadiah</a> ·
    <a href="<?= e(base_url('admin/penukaran.php')) ?>">Penukaran</a>
  </p>
</div></div>

<section class="section" style="padding-top:26px"><div class="wrap">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Tanggal</th><th>Anggota</th><th>Hadiah</th><th class="num">Poin</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="7"><div class="empty">Belum ada permintaan penukaran.</div></td></tr>
      <?php else: foreach ($rows as $r):
          $badge = $r['status'] === 'approved' ? 'badge-in' : ($r['status'] === 'rejected' ? 'badge-out' : 'badge-neutral');
          $label = ['pending' => 'Diproses', 'approved' => 'Selesai', 'rejected' => 'Ditolak'][$r['status']] ?? $r['status'];
      ?>
        <tr>
          <td class="small muted"><?= (int) $r['id'] ?></td>
          <td class="small nowrap"><?= e(tanggal($r['created_at'])) ?></td>
          <td><?= e($r['user_name']) ?><div class="small muted"><?= e($r['email']) ?></div></td>
          <td><?= e($r['icon'] ?: '🎁') ?> <?= e($r['title']) ?></td>
          <td class="num"><?= poin($r['cost']) ?></td>
          <td><span class="badge <?= $badge ?>"><?= e($label) ?></span></td>
          <td>
            <?php if ($r['status'] === 'pending'): ?>
              <form method="post" class="flex" style="gap:6px;margin:0">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button class="btn btn-primary btn-sm" name="aksi" value="setujui" type="submit">Selesai</button>
                <button class="btn btn-ghost btn-sm" name="aksi" value="tolak" type="submit"
                        onclick="return confirm('Tolak penukaran ini? Poin akan dikembalikan.')">Tolak</button>
              </form>
            <?php else: ?>
              <span class="small muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div></section>
<?php page_footer(); ?>
