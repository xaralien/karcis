<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Daftar pesanan pembeli. Variabel: $orders */
$labels = array('paid' => 'Lunas', 'pending' => 'Menunggu pembayaran', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa');
?>
<?php if ( ! $orders): ?>
  <div class="text-center py-5">
    <i class="bi bi-ticket-perforated fs-1 text-muted-k"></i>
    <h5 class="mt-3">Belum ada tiket</h5>
    <p class="text-muted-k">Tiket yang kamu beli akan muncul di sini, termasuk pembelian sebelum punya akun selama emailnya sama.</p>
    <a class="btn btn-primary" href="<?= $this->fmt->url('explore') ?>">Cari event</a>
  </div>
<?php else: ?>
  <?php foreach ($orders as $o): ?>
  <div class="hist">
    <img src="<?= html_escape($this->fmt->img($o->thumbnail)) ?>" alt="">
    <div class="hist-bd">
      <div class="d-flex justify-content-between align-items-start gap-2">
        <a class="fw-bold lh-sm" href="<?= $this->fmt->url('event/detail/' . $o->event_slug) ?>"><?= html_escape($o->event_title) ?></a>
        <span class="status-pill status-<?= $o->status ?>"><?= $labels[$o->status] ?></span>
      </div>
      <div class="small text-muted-k mt-1">
        <i class="bi bi-calendar3 me-1"></i><?= $this->fmt->tgl($o->start_date, FALSE) ?>
        <span class="mx-1">·</span><i class="bi bi-geo-alt me-1"></i><?= html_escape($o->city) ?>
      </div>
      <div class="small mt-1"><?= (int) $o->ticket_qty ?> tiket · <?= $this->fmt->rupiah($o->total) ?> · <?= html_escape($o->order_code) ?></div>
      <div class="d-flex flex-wrap gap-2 mt-2">
        <?php if ($o->status === 'paid'): ?>
          <a class="btn btn-sun btn-sm" href="<?= $this->fmt->url('ticket/show/' . $o->order_code . '/' . $o->access_token) ?>">Lihat tiket QR</a>
          <a class="btn btn-outline-ink btn-sm" href="<?= $this->fmt->url('ticket/printout/' . $o->order_code . '/' . $o->access_token) ?>?auto=1" target="_blank" rel="noopener"><i class="bi bi-printer me-1"></i>Cetak</a>
        <?php elseif ($o->status === 'pending'): ?>
          <a class="btn btn-sun btn-sm" href="<?= $this->fmt->url('payment/status?order=' . rawurlencode($o->order_code)) ?>">Lanjutkan pembayaran</a>
        <?php else: ?>
          <a class="btn btn-outline-ink btn-sm" href="<?= $this->fmt->url('event/tickets/' . $o->event_slug) ?>">Pesan lagi</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
