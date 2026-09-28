<?php defined('BASEPATH') OR exit('No direct script access allowed');
$map = array(
  'paid'    => array('bi-check-circle-fill', 'Pembayaran berhasil', 'Tiket sudah dikirim ke ' . $order->buyer_email . '. Belum masuk? Cek folder spam atau promosi.'),
  'pending' => array('bi-hourglass-split', 'Menunggu pembayaran', 'Selesaikan pembayaran sebelum ' . date('H.i', strtotime($order->expired_at)) . ' WIB, ' . $this->fmt->tgl($order->expired_at) . '. Halaman ini bisa dimuat ulang untuk memeriksa status.'),
  'failed'  => array('bi-x-circle-fill', 'Pembayaran gagal', 'Tidak ada dana yang ditarik. Kamu bisa memesan ulang dari halaman event.'),
  'expired' => array('bi-clock-history', 'Waktu pembayaran habis', 'Pesanan dibatalkan otomatis. Silakan pesan ulang.'),
);
list($icon, $head, $desc) = $map[$order->status];
?>
<div class="text-center">
  <span class="status-pill status-<?= $order->status ?>"><i class="bi <?= $icon ?>"></i><?= $head ?></span>
  <h1 class="mt-3" style="font-size:2rem"><?= html_escape($event->title) ?></h1>
  <p class="text-muted-k mx-auto" style="max-width:46ch"><?= html_escape($desc) ?></p>
</div>
<div class="email-check my-4">
  <div class="d-flex justify-content-between"><span>Kode pesanan</span><strong><?= html_escape($order->order_code) ?></strong></div>
  <div class="d-flex justify-content-between mt-1"><span>Jumlah tiket</span><strong><?= (int) $order->ticket_qty ?></strong></div>
  <div class="d-flex justify-content-between mt-1"><span>Total</span><strong><?= $this->fmt->rupiah($order->total) ?></strong></div>
</div>
<?php if ($order->status === 'paid') $this->load->view('shared/resend_form'); ?>

<div class="d-grid gap-2 mt-3">
  <?php if ($order->status === 'paid'): ?>
    <a class="btn btn-sun btn-lg" href="<?= $ticket_url ?>">Lihat tiket QR</a>
  <?php elseif ($order->status === 'pending' && $order->payment_url): ?>
    <a class="btn btn-sun btn-lg" href="<?= html_escape($order->payment_url) ?>">Lanjutkan pembayaran</a>
    <a class="btn btn-outline-ink" href="<?= $this->fmt->url('payment/status?order=' . rawurlencode($order->order_code)) ?>">Periksa status</a>
  <?php else: ?>
    <a class="btn btn-primary btn-lg" href="<?= $this->fmt->url('event/detail/' . $event->slug) ?>">Pesan ulang</a>
  <?php endif; ?>
  <a class="btn btn-link" href="<?= $this->fmt->base() ?>">Kembali ke beranda</a>
</div>
