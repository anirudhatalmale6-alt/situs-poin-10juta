<?php
require __DIR__ . '/../lib/bootstrap.php';

$user = require_login();

if (is_post() && ($_POST['aksi'] ?? '') === 'bonus_harian') {
    csrf_check();
    [$ok, $msg] = claim_daily_bonus((int) $user['id']);
    flash($msg, $ok ? 'ok' : 'info');
    redirect(base_url('dashboard.php'));
}

$saldo   = balance_of((int) $user['id']);
$ring    = points_summary((int) $user['id']);
$riwayat = transactions_of((int) $user['id'], 8);
$tukar   = redemptions_of((int) $user['id'], 5);
$goal    = (int) config('points.goal');
$persen  = $goal > 0 ? min(100, round($saldo / $goal * 100, 2)) : 0;
$absenOk = substr((string) ($user['last_bonus_on'] ?? ''), 0, 10) !== date('Y-m-d');

page_header(['title' => 'Dashboard', 'active' => 'dashboard', 'noindex' => true]);
?>
<div class="page-head">
  <div class="wrap">
    <h1>Halo, <?= e($user['name']) ?> 👋</h1>
    <p class="muted">Anggota sejak <?= e(tanggal($user['created_at'])) ?> ·
      <a href="<?= e(base_url('profil.php')) ?>">Ubah profil</a></p>
  </div>
</div>

<section class="section" style="padding-top:26px">
  <div class="wrap">
    <div class="grid" style="grid-template-columns:1fr;gap:18px">

      <div class="balance">
        <div class="label">Saldo poin Anda</div>
        <div class="value"><?= poin($saldo) ?></div>
        <div class="sub"><?= $persen ?>% dari target program <?= poin($goal) ?> poin</div>
        <div class="progress"><i style="width:<?= max(1.5, $persen) ?>%"></i></div>
        <div class="actions">
          <form method="post" style="margin:0">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="bonus_harian">
            <button class="btn <?= $absenOk ? 'btn-gold' : 'btn-ghost is-disabled' ?>" type="submit" <?= $absenOk ? '' : 'disabled' ?>>
              <?= $absenOk ? 'Ambil bonus harian +' . poin(config('points.daily_bonus')) : 'Bonus harian sudah diambil' ?>
            </button>
          </form>
          <a class="btn btn-ghost" href="<?= e(base_url('katalog.php')) ?>">Tukar poin</a>
          <a class="btn btn-ghost" href="<?= e(base_url('riwayat.php')) ?>">Riwayat lengkap</a>
        </div>
      </div>

      <div class="grid grid-3">
        <div class="mini"><div class="cap">Total poin masuk</div><div class="num" style="color:var(--ok)">+<?= poin($ring['masuk']) ?></div></div>
        <div class="mini"><div class="cap">Total poin terpakai</div><div class="num" style="color:var(--bad)"><?= $ring['keluar'] > 0 ? '−' : '' ?><?= poin($ring['keluar']) ?></div></div>
        <div class="mini"><div class="cap">Jumlah transaksi</div><div class="num"><?= poin($ring['jumlah']) ?></div></div>
      </div>

      <div>
        <div class="flex between" style="margin-bottom:12px">
          <h2 class="mb0" style="font-size:1.15rem">Transaksi poin terakhir</h2>
          <a class="small" href="<?= e(base_url('riwayat.php')) ?>">Lihat semua &rarr;</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Tanggal</th><th>Keterangan</th><th>Jenis</th>
              <th class="num">Poin</th><th class="num">Saldo</th>
            </tr></thead>
            <tbody>
            <?php if (!$riwayat): ?>
              <tr><td colspan="5"><div class="empty">Belum ada transaksi. Coba ambil bonus harian dulu.</div></td></tr>
            <?php else: foreach ($riwayat as $t): $in = $t['amount'] > 0; ?>
              <tr>
                <td class="nowrap small"><?= e(tanggal($t['created_at'])) ?></td>
                <td><?= e($t['note'] ?: point_type_label($t['type'])) ?></td>
                <td><span class="badge <?= $in ? 'badge-in' : 'badge-out' ?>"><?= e(point_type_label($t['type'])) ?></span></td>
                <td class="num <?= $in ? 'amt-in' : 'amt-out' ?>"><?= ($in ? '+' : '−') . poin(abs($t['amount'])) ?></td>
                <td class="num"><?= poin($t['balance_after']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php if ($tukar): ?>
      <div>
        <h2 style="font-size:1.15rem">Penukaran Anda</h2>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Tanggal</th><th>Hadiah</th><th class="num">Poin</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($tukar as $r):
                $badge = $r['status'] === 'approved' ? 'badge-in' : ($r['status'] === 'rejected' ? 'badge-out' : 'badge-neutral');
                $label = ['pending' => 'Diproses', 'approved' => 'Selesai', 'rejected' => 'Ditolak'][$r['status']] ?? $r['status'];
            ?>
              <tr>
                <td class="nowrap small"><?= e(tanggal($r['created_at'])) ?></td>
                <td><?= e($r['icon'] ?: '🎁') ?> <?= e($r['title']) ?></td>
                <td class="num"><?= poin($r['cost']) ?></td>
                <td><span class="badge <?= $badge ?>"><?= e($label) ?></span></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</section>
<?php page_footer(); ?>
