<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars('actions', '<a class="btn btn-light-k btn-sm" href="' . $this->fmt->url('admin/orders') . '"><i class="bi bi-arrow-left me-1"></i>Semua pesanan</a>');
$labels = array('paid' => 'Lunas', 'pending' => 'Menunggu pembayaran', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa');
?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-k">
      <div class="hd"><h5>Rincian tiket</h5><span class="badge-st <?= $order->status ?>"><?= $labels[$order->status] ?></span></div>
      <div class="bd">
        <div class="fw-bold"><?= html_escape($event->title) ?></div>
        <div class="small text-muted-k mb-3"><?= html_escape($event->venue) ?>, <?= html_escape($event->city) ?></div>
        <table class="detail-table">
          <thead><tr><th>Kategori</th><th class="num">Harga</th><th class="num">Jml</th><th class="num">Subtotal</th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr><td><?= html_escape($it->ticket_name) ?></td><td class="num"><?= $this->fmt->rupiah($it->price) ?></td><td class="num"><?= (int) $it->qty ?></td><td class="num"><?= $this->fmt->rupiah($it->price * $it->qty) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <table class="fee-table mt-2">
          <tr><td>Harga tiket</td><td><?= $this->fmt->rupiah($order->subtotal) ?></td></tr>
          <tr><td>Biaya layanan (<?= (int) $order->ticket_qty ?> tiket)</td><td><?= $this->fmt->rupiah($order->service_fee) ?></td></tr>
          <tr><td>Biaya transaksi</td><td><?= $this->fmt->rupiah($order->transaction_fee) ?></td></tr>
          <tr class="total"><td>Total</td><td><?= $this->fmt->rupiah($order->total) ?></td></tr>
        </table>
      </div>
    </div>

    <div class="card-k mt-3">
      <div class="hd"><h5>Tiket QR</h5>
        <?php if ($tickets): $purl = $this->fmt->url('ticket/printout/' . $order->order_code . '/' . $order->access_token); ?>
        <a class="btn btn-light-k btn-sm" href="<?= $purl ?>?auto=1" target="_blank" rel="noopener"><i class="bi bi-printer me-1"></i>Cetak semua (<?= count($tickets) ?> halaman)</a>
        <?php else: ?><span class="small text-muted-k">0 tiket</span><?php endif; ?>
      </div>
      <?php if ($tickets): ?>
      <div class="table-responsive">
        <table class="table table-k">
          <thead><tr><th>QR</th><th>Kode tiket</th><th>Kategori</th><th>Check-in</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($tickets as $t): ?>
            <tr>
              <td><a href="<?= $this->fmt->base('uploads/qr/' . $t->qr_file) ?>" target="_blank" rel="noopener"><img src="<?= $this->fmt->base('uploads/qr/' . $t->qr_file) ?>" alt="" width="48" height="48"></a></td>
              <td class="fw-semibold"><?= html_escape($t->ticket_code) ?></td>
              <td><?= html_escape($t->ticket_name) ?></td>
              <td><?= $t->is_checked_in ? '<span class="badge-st valid">Masuk ' . date('d/m H.i', strtotime($t->checked_in_at)) . '</span>' : '<span class="small text-muted-k">Belum</span>' ?></td>
              <td class="text-end text-nowrap">
                <a class="btn btn-icon btn-light-k" href="<?= $purl ?>?id=<?= rawurlencode($t->ticket_code) ?>&amp;auto=1" target="_blank" rel="noopener" title="Cetak tiket ini"><i class="bi bi-printer"></i></a>
                <?php if ($t->is_checked_in): ?>
                <form method="post" action="<?= $this->fmt->url('admin/orders/undo_checkin/' . $order->order_code . '/' . $t->id) ?>" class="d-inline" data-confirm="Batalkan check-in tiket ini? QR bisa dipindai lagi."><?= $this->fmt->csrf() ?><button class="btn btn-light-k btn-sm">Batalkan check-in</button></form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php elseif ($order->status === 'paid'): ?>
        <div class="empty-k"><i class="bi bi-exclamation-triangle"></i>Pesanan sudah lunas tapi tiket belum terbit.
          <form method="post" action="<?= $this->fmt->url('admin/orders/reissue/' . $order->order_code) ?>" class="mt-2"><?= $this->fmt->csrf() ?><button class="btn btn-primary btn-sm">Terbitkan tiket &amp; kirim email</button></form>
        </div>
      <?php else: ?><div class="empty-k"><i class="bi bi-qr-code"></i>Tiket terbit setelah pembayaran lunas.</div><?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card-k">
      <div class="hd"><h5>Pembeli</h5></div>
      <div class="bd small">
        <div class="fw-bold fs-6"><?= html_escape($order->buyer_name) ?></div>
        <div><a href="mailto:<?= html_escape($order->buyer_email) ?>"><?= html_escape($order->buyer_email) ?></a></div>
        <div><a href="tel:<?= html_escape(preg_replace('/\s/', '', $order->buyer_phone)) ?>"><?= html_escape($order->buyer_phone) ?></a></div>
      </div>
    </div>
    <div class="card-k mt-3">
      <div class="hd"><h5>Pembayaran</h5></div>
      <div class="bd small">
        <dl class="row mb-0">
          <dt class="col-5 fw-normal text-muted-k">Kode pesanan</dt><dd class="col-7 fw-semibold"><?= html_escape($order->order_code) ?></dd>
          <dt class="col-5 fw-normal text-muted-k">Dibuat</dt><dd class="col-7"><?= date('d/m/Y H.i', strtotime($order->created_at)) ?></dd>
          <dt class="col-5 fw-normal text-muted-k">Batas bayar</dt><dd class="col-7"><?= date('d/m/Y H.i', strtotime($order->expired_at)) ?></dd>
          <dt class="col-5 fw-normal text-muted-k">Dibayar</dt><dd class="col-7"><?= $order->paid_at ? date('d/m/Y H.i', strtotime($order->paid_at)) : '–' ?></dd>
          <dt class="col-5 fw-normal text-muted-k">Metode</dt><dd class="col-7"><?= $order->payment_code ? html_escape($order->payment_code) : '–' ?></dd>
          <dt class="col-5 fw-normal text-muted-k">Ref Duitku</dt><dd class="col-7 text-break"><?= $order->duitku_reference ? html_escape($order->duitku_reference) : '–' ?></dd>
          <dt class="col-5 fw-normal text-muted-k">Email tiket</dt><dd class="col-7"><?= $order->email_sent_at ? 'Terkirim ' . date('d/m H.i', strtotime($order->email_sent_at)) : 'Belum terkirim' ?></dd>
        </dl>
        <div class="d-grid gap-2 mt-3">
          <?php if ($order->status === 'paid'): ?>
            <form method="post" action="<?= $this->fmt->url('admin/orders/resend/' . $order->order_code) ?>"><?= $this->fmt->csrf() ?>
              <label class="form-label small mb-1" for="resend_email">Kirim tiket ke</label>
              <input class="form-control form-control-sm mb-2" id="resend_email" name="email" type="email" value="<?= html_escape($order->buyer_email) ?>">
              <div class="hint mb-2">Ubah alamat di atas bila pembeli salah ketik email.</div>
              <button class="btn btn-primary btn-sm w-100"><i class="bi bi-envelope me-1"></i>Kirim ulang tiket</button>
            </form>
            <a class="btn btn-light-k btn-sm" href="<?= $this->fmt->url('ticket/show/' . $order->order_code . '/' . $order->access_token) ?>" target="_blank" rel="noopener"><i class="bi bi-ticket-perforated me-1"></i>Buka halaman tiket</a>
          <?php elseif ($order->status === 'pending'): ?>
            <form method="post" action="<?= $this->fmt->url('admin/orders/sync/' . $order->order_code) ?>"><?= $this->fmt->csrf() ?><button class="btn btn-primary btn-sm w-100"><i class="bi bi-arrow-repeat me-1"></i>Cek status ke Duitku</button></form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
