<?php defined('BASEPATH') OR exit('No direct script access allowed'); $f = $cart['fees']; $ev = $cart['event']; ?>
<div class="d-flex gap-3 align-items-center mb-3">
  <img src="<?= html_escape($this->fmt->img($ev->thumbnail)) ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:12px">
  <div>
    <div class="fw-bold lh-sm"><?= html_escape($ev->title) ?></div>
    <div class="small text-muted-k"><?= html_escape($ev->venue) ?>, <?= html_escape($ev->city) ?></div>
  </div>
</div>
<?php foreach ($schedules as $s): ?>
  <div class="small"><i class="bi bi-calendar3 me-2 text-muted-k"></i><?= html_escape($s->label) ?>: <?= $this->fmt->tgl($s->event_date) ?>, <?= $this->fmt->jam($s->start_time) ?>–<?= $this->fmt->jam($s->end_time) ?> WIB</div>
<?php endforeach; ?>

<div class="table-responsive mt-3">
<table class="detail-table">
  <thead><tr><th>Kategori tiket</th><th class="num d-none d-sm-table-cell">Harga</th><th class="num d-none d-sm-table-cell">Jml</th><th class="num">Subtotal</th></tr></thead>
  <tbody>
  <?php foreach ($cart['lines'] as $l): ?>
    <tr><td><div class="fw-semibold"><?= html_escape($l['name']) ?></div><div class="small text-muted-k d-sm-none"><?= $this->fmt->rupiah($l['price']) ?> × <?= $l['qty'] ?></div></td><td class="num d-none d-sm-table-cell"><?= $this->fmt->rupiah($l['price']) ?></td><td class="num d-none d-sm-table-cell"><?= $l['qty'] ?></td><td class="num"><?= $this->fmt->rupiah($l['price'] * $l['qty']) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<table class="fee-table mt-2">
  <tr><td>Harga tiket (<?= $f['qty'] ?> tiket)</td><td><?= $this->fmt->rupiah($f['subtotal']) ?></td></tr>
  <tr><td>Biaya layanan <span class="text-muted-k">(<?= $f['qty'] ?> × <?= $this->fmt->rupiah($this->config->item('service_fee_per_ticket')) ?>)</span></td><td><?= $this->fmt->rupiah($f['service_fee']) ?></td></tr>
  <tr><td>Biaya transaksi</td><td><?= $this->fmt->rupiah($f['transaction_fee']) ?></td></tr>
  <tr class="total"><td>Total bayar</td><td><?= $this->fmt->rupiah($f['total']) ?></td></tr>
</table>
<a href="<?= $this->fmt->url('event/tickets/' . $ev->slug) ?>" class="btn btn-outline-ink btn-sm mt-3"><i class="bi bi-pencil me-1"></i>Ubah pilihan tiket</a>
