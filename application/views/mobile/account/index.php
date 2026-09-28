<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="m-page">
  <h1 class="mb-1">Halo, <?= html_escape($user->name) ?></h1>
  <div class="small text-muted-k mb-3"><?= html_escape($user->email) ?></div>
  <div class="row g-2">
    <div class="col-6"><div class="m-card"><div class="small text-muted-k">Tiket dimiliki</div><div class="display-type fs-3"><?= $stats['tickets'] ?></div></div></div>
    <div class="col-6"><div class="m-card"><div class="small text-muted-k">Event mendatang</div><div class="display-type fs-3"><?= $stats['upcoming'] ?></div></div></div>
  </div>
  <?php if ($stats['pending']): ?><div class="alert alert-warning small mt-3 mb-0">Ada <?= $stats['pending'] ?> pesanan menunggu pembayaran.</div><?php endif; ?>

  <div class="d-flex justify-content-between align-items-end mt-4 mb-2">
    <h2 class="fs-5 mb-0">Tiket terbaru</h2>
    <a class="small fw-bold" href="<?= $this->fmt->url('account/orders') ?>">Semua</a>
  </div>
  <?php $this->load->view('shared/order_history'); ?>

  <div class="d-grid gap-2 mt-4">
    <a class="btn btn-outline-ink" href="<?= $this->fmt->url('account/profile') ?>">Data akun</a>
    <a class="btn btn-link" href="<?= $this->fmt->url('account/logout') ?>">Keluar</a>
  </div>
</div>
