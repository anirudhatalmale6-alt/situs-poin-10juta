<?php
require __DIR__ . '/../lib/bootstrap.php';

$user = current_user();

if (is_post()) {
    csrf_check();
    if (!$user) {
        flash('Masuk dulu untuk menukar poin.', 'info');
        redirect(base_url('masuk.php'));
    }
    [$ok, $msg] = redeem_reward((int) $user['id'], (int) ($_POST['reward_id'] ?? 0));
    flash($msg, $ok ? 'ok' : 'error');
    redirect(base_url('katalog.php'));
}

$saldo   = $user ? balance_of((int) $user['id']) : 0;
$rewards = rewards_active();

page_header([
    'title'  => 'Tukar Poin',
    'desc'   => 'Tukarkan poin Anda dengan pulsa, paket data, sembako, dan kebutuhan harian lainnya.',
    'active' => 'katalog',
]);
?>
<div class="page-head">
  <div class="wrap flex between">
    <div>
      <h1>Katalog penukaran</h1>
      <p class="muted">Pilih kebutuhan Anda, poin langsung terpotong dari saldo.</p>
    </div>
    <?php if ($user): ?>
      <div class="mini" style="min-width:190px">
        <div class="cap">Saldo Anda</div>
        <div class="num"><?= poin($saldo) ?></div>
      </div>
    <?php endif; ?>
  </div>
</div>

<section class="section" style="padding-top:26px">
  <div class="wrap">
    <?php if (!$user): ?>
      <div class="alert alert-info">
        <a href="<?= e(base_url('masuk.php')) ?>">Masuk</a> atau
        <a href="<?= e(base_url('daftar.php')) ?>">daftar gratis</a> untuk mulai menukar poin.
      </div>
    <?php endif; ?>

    <div class="grid grid-3">
      <?php foreach ($rewards as $r):
          $cukup = $user && $saldo >= (int) $r['cost'];
          $habis = (int) $r['stock'] <= 0;
      ?>
        <div class="reward">
          <div class="thumb"><?= e($r['icon'] ?: '🎁') ?></div>
          <div class="body">
            <h3 class="mb0"><?= e($r['title']) ?></h3>
            <p class="small muted mb0"><?= e($r['description']) ?></p>
            <div class="cost"><?= poin($r['cost']) ?> poin</div>
            <div class="small muted"><?= $habis ? 'Stok habis' : 'Sisa stok: ' . poin($r['stock']) ?></div>
            <?php if ($habis): ?>
              <button class="btn btn-ghost btn-sm is-disabled" disabled>Stok habis</button>
            <?php elseif (!$user): ?>
              <a class="btn btn-ghost btn-sm" href="<?= e(base_url('masuk.php')) ?>">Masuk untuk menukar</a>
            <?php elseif (!$cukup): ?>
              <button class="btn btn-ghost btn-sm is-disabled" disabled>Kurang <?= poin($r['cost'] - $saldo) ?> poin</button>
            <?php else: ?>
              <form method="post" style="margin-top:auto"
                    onsubmit="return confirm('Tukar <?= e($r['title']) ?> seharga <?= poin($r['cost']) ?> poin?')">
                <?= csrf_field() ?>
                <input type="hidden" name="reward_id" value="<?= (int) $r['id'] ?>">
                <button class="btn btn-primary btn-sm btn-block" type="submit">Tukar sekarang</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php page_footer(); ?>
