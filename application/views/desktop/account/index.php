<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:1040px">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
    <div>
      <h1 style="font-size:2.4rem" class="mb-1">Halo, <?= html_escape($user->name) ?></h1>
      <div class="text-muted-k"><?= html_escape($user->email) ?></div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-ink" href="<?= $this->fmt->url('account/profile') ?>">Data akun</a>
      <a class="btn btn-primary" href="<?= $this->fmt->url('explore') ?>">Cari event</a>
    </div>
  </div>

  <div class="row g-3 mt-2">
    <div class="col-md-3 col-6"><div class="panel h-100"><div class="small text-muted-k">Tiket dimiliki</div><div class="display-type" style="font-size:2rem"><?= $stats['tickets'] ?></div></div></div>
    <div class="col-md-3 col-6"><div class="panel h-100"><div class="small text-muted-k">Event mendatang</div><div class="display-type" style="font-size:2rem"><?= $stats['upcoming'] ?></div></div></div>
    <div class="col-md-3 col-6"><div class="panel h-100"><div class="small text-muted-k">Total pesanan</div><div class="display-type" style="font-size:2rem"><?= $stats['orders'] ?></div></div></div>
    <div class="col-md-3 col-6"><div class="panel h-100"><div class="small text-muted-k">Total belanja</div><div class="display-type" style="font-size:1.5rem"><?= $this->fmt->rupiah($stats['spent']) ?></div></div></div>
  </div>

  <?php if ($stats['pending']): ?>
  <div class="alert alert-warning mt-4 mb-0"><i class="bi bi-hourglass-split me-2"></i>Ada <?= $stats['pending'] ?> pesanan yang menunggu pembayaran.</div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-end mt-5 mb-3">
    <h2 style="font-size:1.6rem" class="mb-0">Tiket terbaru</h2>
    <a class="fw-bold" href="<?= $this->fmt->url('account/orders') ?>">Lihat semua</a>
  </div>
  <?php $this->load->view('shared/order_history'); ?>
</div>
