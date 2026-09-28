<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;background:#F7F5FC;font-family:Arial,Helvetica,sans-serif;color:#1E1540">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F5FC;padding:24px 12px">
<tr><td align="center">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px">
    <tr><td style="background:#1E1540;border-radius:16px 16px 0 0;padding:24px;color:#fff">
      <div style="font-size:22px;font-weight:bold">Karcis</div>
      <div style="margin-top:14px;color:#FFB800;font-weight:bold;font-size:13px">Pembayaran berhasil</div>
      <div style="font-size:20px;font-weight:bold;margin-top:4px"><?= html_escape($event->title) ?></div>
      <div style="color:#C9C3E3;font-size:14px;margin-top:4px"><?= html_escape($event->venue) ?>, <?= html_escape($event->address) ?>, <?= html_escape($event->city) ?></div>
    </td></tr>
    <tr><td style="background:#fff;padding:24px;font-size:14px;line-height:1.6">
      <p style="margin:0 0 12px">Halo <?= html_escape($order->buyer_name) ?>, berikut <?= count($tickets) ?> tiket untuk pesanan <strong><?= html_escape($order->order_code) ?></strong>.</p>
      <?php foreach ($schedules as $s): ?>
        <div><strong><?= html_escape($s->label) ?>:</strong> <?= $this->fmt->tgl($s->event_date) ?>, <?= $this->fmt->jam($s->start_time) ?>–<?= $this->fmt->jam($s->end_time) ?> WIB (<?= html_escape($s->venue) ?>)</div>
      <?php endforeach; ?>
      <table role="presentation" width="100%" style="margin-top:16px;font-size:14px;border-top:2px dashed #E4DFF2">
        <tr><td style="padding-top:10px">Total dibayar</td><td align="right" style="padding-top:10px;font-weight:bold"><?= $this->fmt->rupiah($order->total) ?></td></tr>
      </table>
    </td></tr>
    <?php foreach ($tickets as $i => $t): ?>
    <tr><td style="background:#fff;padding:0 24px 24px">
      <table role="presentation" width="100%" style="border:2px dashed #E4DFF2;border-radius:12px">
        <tr><td align="center" style="padding:18px">
          <div style="font-size:13px;color:#7A7394">Tiket <?= $i + 1 ?> dari <?= count($tickets) ?> · <?= html_escape($t->ticket_name) ?></div>
          <img src="cid:<?= $t->cid ?>" width="200" height="200" alt="QR <?= html_escape($t->ticket_code) ?>" style="display:block;margin:10px auto">
          <div style="font-size:18px;font-weight:bold;letter-spacing:1px"><?= html_escape($t->ticket_code) ?></div>
          <div style="font-size:12px;color:#7A7394">ID Event: <?= (int) $t->event_id ?></div>
        </td></tr>
      </table>
    </td></tr>
    <?php endforeach; ?>
    <tr><td style="background:#fff;border-radius:0 0 16px 16px;padding:0 24px 24px" align="center">
      <a href="<?= $ticket_url ?>" style="display:inline-block;background:#FFB800;color:#1E1540;font-weight:bold;padding:12px 22px;border-radius:999px;text-decoration:none">Buka tiket di browser</a>
      <p style="font-size:12px;color:#7A7394;margin:16px 0 0">Satu QR berlaku untuk satu orang dan hanya bisa dipindai sekali. Jangan bagikan QR ke orang lain.</p>
    </td></tr>
  </table>
</td></tr></table>
</body></html>
