<?php defined('BASEPATH') OR exit('No direct script access allowed'); /* $t, $event, $schedules, $order, $index, $count */ ?>
<article class="eticket">
  <div class="eticket-head">
    <div class="d-flex justify-content-between small"><span class="type"><?= html_escape($t->ticket_name) ?></span><span class="text-white-50">Tiket <?= $index ?> dari <?= $count ?></span></div>
    <div class="fw-bold fs-5 mt-1 lh-sm"><?= html_escape($event->title) ?></div>
    <div class="small text-white-50 mt-1"><?= html_escape($event->venue) ?>, <?= html_escape($event->city) ?></div>
  </div>
  <div class="eticket-qr">
    <img src="<?= $this->fmt->base('uploads/qr/' . $t->qr_file) ?>" alt="Kode QR tiket <?= html_escape($t->ticket_code) ?>">
    <div class="eticket-code mt-2"><?= html_escape($t->ticket_code) ?></div>
    <div class="small text-muted-k">ID Event: <?= (int) $t->event_id ?></div>
    <?php if ($t->is_checked_in): ?><span class="status-pill status-failed mt-2">Sudah dipakai</span><?php endif; ?>
  </div>
  <div class="stub-tear" aria-hidden="true"></div>
  <div class="p-3 small">
    <div class="d-flex justify-content-between"><span class="text-muted-k">Pemesan</span><span class="fw-semibold"><?= html_escape($order->buyer_name) ?></span></div>
    <?php foreach ($schedules as $s): ?>
    <div class="d-flex justify-content-between"><span class="text-muted-k"><?= html_escape($s->label) ?></span><span class="fw-semibold"><?= $this->fmt->tgl($s->event_date, FALSE) ?>, <?= $this->fmt->jam($s->start_time) ?></span></div>
    <?php endforeach; ?>
    <?php if ( ! empty($print_url)): ?>
    <a class="btn btn-outline-ink btn-sm w-100 mt-3" href="<?= $print_url ?>?id=<?= rawurlencode($t->ticket_code) ?>&amp;auto=1" target="_blank" rel="noopener"><i class="bi bi-printer me-1"></i>Cetak tiket ini</a>
    <?php endif; ?>
  </div>
</article>
