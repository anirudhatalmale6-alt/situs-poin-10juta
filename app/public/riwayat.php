<?php
require __DIR__ . '/../lib/bootstrap.php';

$user  = require_login();
$per   = 20;
$page  = max(1, (int) ($_GET['hal'] ?? 1));
$total = transactions_count((int) $user['id']);
$pages = max(1, (int) ceil($total / $per));
$page  = min($page, $pages);
$rows  = transactions_of((int) $user['id'], $per, ($page - 1) * $per);

page_header(['title' => 'Riwayat Poin', 'noindex' => true]);
?>
<div class="page-head">
  <div class="wrap">
    <h1>Riwayat poin</h1>
    <p class="muted"><?= poin($total) ?> transaksi tercatat · saldo saat ini <strong><?= poin(balance_of((int) $user['id'])) ?></strong> poin</p>
  </div>
</div>

<section class="section" style="padding-top:26px">
  <div class="wrap">
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>#</th><th>Tanggal</th><th>Keterangan</th><th>Jenis</th>
          <th class="num">Poin</th><th class="num">Saldo setelahnya</th>
        </tr></thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6"><div class="empty">Belum ada transaksi poin.</div></td></tr>
        <?php else: foreach ($rows as $t): $in = $t['amount'] > 0; ?>
          <tr>
            <td class="small muted"><?= (int) $t['id'] ?></td>
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

    <?php if ($pages > 1): ?>
      <div class="flex" style="margin-top:18px;justify-content:center">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-ghost' ?>"
             href="<?= e(base_url('riwayat.php?hal=' . $i)) ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php page_footer(); ?>
