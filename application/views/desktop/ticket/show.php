<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:1040px">
  <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
    <div>
      <span class="status-pill status-<?= $order->status ?>"><?= $order->status === 'paid' ? 'Lunas' : ucfirst($order->status) ?></span>
      <h1 class="mt-2 mb-1" style="font-size:2.4rem">Tiket kamu</h1>
      <div class="text-muted-k">Pesanan <?= html_escape($order->order_code) ?> · dikirim ke <?= html_escape($order->buyer_email) ?></div>
    </div>
    <?php if ($tickets): ?>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-ink" href="<?= $print_url ?>" target="_blank" rel="noopener"><i class="bi bi-eye me-2"></i>Pratinjau cetak</a>
      <a class="btn btn-sun" href="<?= $print_url ?>?auto=1" target="_blank" rel="noopener"><i class="bi bi-printer me-2"></i>Cetak semua (<?= count($tickets) ?> halaman)</a>
    </div>
    <?php endif; ?>
  </div>
  <?php if ($tickets): ?>
  <?php $this->load->view('shared/resend_form'); ?>
  <p class="mt-4 mb-4">Tunjukkan satu QR untuk satu orang di pintu masuk. Saat dicetak, setiap tiket berada di halaman A4 sendiri sehingga mudah dibagikan ke rombongan.</p>
  <div class="row g-4">
    <?php foreach ($tickets as $i => $t): ?>
      <div class="col-md-6 col-lg-4"><?php $this->load->view('shared/eticket', array('t' => $t, 'index' => $i + 1, 'count' => count($tickets))); ?></div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="panel text-center py-5">
    <h5>Tiket belum terbit</h5>
    <p class="text-muted-k">Tiket QR muncul di sini setelah pembayaran berhasil.</p>
    <a class="btn btn-primary" href="<?= $this->fmt->url('payment/status?order=' . rawurlencode($order->order_code)) ?>">Periksa status pembayaran</a>
  </div>
  <?php endif; ?>
</div>
