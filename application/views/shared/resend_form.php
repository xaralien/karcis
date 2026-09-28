<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Kirim ulang tiket. Variabel: $order */
$terkirim = $order->email_sent_at;
?>
<div class="email-check mt-3">
  <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
    <div>
      <strong>Tiket dikirim ke <?= html_escape($order->buyer_email) ?></strong>
      <div class="small text-muted-k">
        <?= $terkirim ? 'Terkirim ' . $this->fmt->waktu($terkirim) . ' WIB.' : 'Email belum berhasil terkirim.' ?>
        Belum masuk? Cek folder spam atau promosi dulu.
      </div>
    </div>
    <button class="btn btn-outline-ink btn-sm text-nowrap" type="button" data-bs-toggle="collapse" data-bs-target="#kirimUlang">
      <i class="bi bi-envelope-arrow-up me-1"></i>Kirim ulang
    </button>
  </div>

  <div class="collapse mt-3" id="kirimUlang">
    <form method="post" action="<?= $this->fmt->url('payment/resend') ?>"><?= $this->fmt->csrf() ?>
      <input type="hidden" name="order_code" value="<?= html_escape($order->order_code) ?>">
      <input type="hidden" name="token" value="<?= html_escape($order->access_token) ?>">
      <label class="form-label small" for="resend_email">Kirim ke alamat</label>
      <div class="d-flex gap-2 flex-wrap">
        <input class="form-control" style="flex:1 1 220px" id="resend_email" name="email" type="email" value="<?= html_escape($order->buyer_email) ?>" required>
        <button class="btn btn-sun text-nowrap">Kirim tiket</button>
      </div>
      <div class="form-text">Salah ketik email saat memesan? Perbaiki di sini, lalu tiket dikirim ke alamat yang baru.</div>
    </form>
  </div>
</div>
