<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars(array('active_nav' => 'order'));
?>
<div class="m-page">
  <span class="status-pill status-<?= $order->status ?>"><?= $order->status === 'paid' ? 'Lunas' : ucfirst($order->status) ?></span>
  <h1 class="mt-2 mb-1">Tiket kamu</h1>
  <div class="small text-muted-k mb-3"><?= html_escape($order->order_code) ?> · <?= html_escape($order->buyer_email) ?></div>
  <?php if ($tickets): ?>
    <?php $this->load->view('shared/resend_form'); ?>
    <p class="small mt-3">Geser untuk melihat semua tiket. Naikkan kecerahan layar saat QR dipindai.</p>
    <a class="btn btn-sun w-100 mb-3" href="<?= $print_url ?>?auto=1" target="_blank" rel="noopener"><i class="bi bi-printer me-2"></i>Cetak semua (<?= count($tickets) ?> halaman)</a>
    <div class="h-scroll">
      <?php foreach ($tickets as $i => $t): ?>
      <div style="width:86vw;max-width:360px"><?php $this->load->view('shared/eticket', array('t' => $t, 'index' => $i + 1, 'count' => count($tickets))); ?></div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="m-card text-center py-4">
      <h5>Tiket belum terbit</h5>
      <p class="small text-muted-k">Tiket QR muncul setelah pembayaran berhasil.</p>
      <a class="btn btn-primary btn-sm" href="<?= $this->fmt->url('payment/status?order=' . rawurlencode($order->order_code)) ?>">Periksa pembayaran</a>
    </div>
  <?php endif; ?>
</div>
