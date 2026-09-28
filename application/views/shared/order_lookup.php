<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<form method="post" action="<?= $this->fmt->url('order') ?>"><?= $this->fmt->csrf() ?>
  <div class="mb-3">
    <label class="form-label" for="order_code">Kode pesanan</label>
    <input id="order_code" name="order_code" value="<?= $this->fmt->old('order_code') ?>" placeholder="KRC260916ABC123" required class="form-control uppercase">
    <?= $this->fmt->err('order_code') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="email">Email pembeli</label>
    <input class="form-control" id="email" name="email" type="email" value="<?= $this->fmt->old('email') ?>" placeholder="nama@email.com" required>
    <?= $this->fmt->err('email') ?>
  </div>
  <button class="btn btn-primary w-100">Cari pesanan</button>
</form>

<?php if ( ! empty($not_found)): ?>
  <div class="alert alert-warning mt-3 mb-0">Pesanan tidak ditemukan. Periksa kode pesanan di email konfirmasi dan pastikan email sama dengan saat membeli.</div>
<?php endif; ?>

<?php if ($order): ?>
<div class="email-check mt-4">
  <div class="d-flex justify-content-between align-items-center">
    <strong><?= html_escape($event->title) ?></strong>
    <span class="status-pill status-<?= $order->status ?>"><?= $order->status === 'paid' ? 'Lunas' : ucfirst($order->status) ?></span>
  </div>
  <div class="small mt-2"><?= (int) $order->ticket_qty ?> tiket · <?= $this->fmt->rupiah($order->total) ?> · dipesan <?= $this->fmt->tgl($order->created_at, FALSE) ?></div>
  <div class="d-flex gap-2 mt-3 flex-wrap">
    <?php if ($order->status === 'paid'): ?>
      <a class="btn btn-sun btn-sm" href="<?= $this->fmt->url('ticket/show/' . $order->order_code . '/' . $order->access_token) ?>">Lihat tiket QR</a>
      <form method="post" action="<?= $this->fmt->url('order/resend') ?>" class="d-inline"><?= $this->fmt->csrf() ?>
        <input type="hidden" name="order_code" value="<?= html_escape($order->order_code) ?>">
        <input type="hidden" name="email" value="<?= html_escape($order->buyer_email) ?>">
        <button class="btn btn-outline-ink btn-sm">Kirim ulang ke email</button>
      </form>
    <?php elseif ($order->status === 'pending'): ?>
      <a class="btn btn-sun btn-sm" href="<?= $this->fmt->url('payment/status?order=' . rawurlencode($order->order_code)) ?>">Periksa pembayaran</a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
