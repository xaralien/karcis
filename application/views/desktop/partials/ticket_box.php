<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Dipakai desktop & mobile. Variabel: $event, $ticket_types, $selected, $sticky_bar (bool) */
$sticky_bar = isset($sticky_bar) ? $sticky_bar : FALSE;
$selected   = isset($selected) ? $selected : array();
?>
<form method="post" action="<?= $this->fmt->url('checkout') ?>" data-ticket-form data-max="<?= $this->fmt->max_tickets() ?>" data-service-fee="<?= $this->fmt->service_fee() ?>" data-transaction-fee="<?= $this->fmt->transaction_fee() ?>" id="ticketForm"><?= $this->fmt->csrf() ?>
  <input type="hidden" name="event_id" value="<?= (int) $event->id ?>">
  <?php foreach ($ticket_types as $t):
      $avail = max(0, (int) $t->available); $soldout = $avail <= 0; ?>
  <div class="tt-row <?= $soldout ? 'is-soldout' : '' ?>">
    <div class="flex-grow-1 min-w-0">
      <div class="tt-name"><?= html_escape($t->name) ?></div>
      <div class="tt-desc"><?= html_escape($t->description) ?></div>
      <div class="tt-price mt-1"><?= $this->fmt->rupiah($t->price) ?></div>
      <?php if ( ! $soldout && $avail <= 20): ?><div class="small fw-semibold" style="color:var(--danger)">Sisa <?= $avail ?> tiket</div><?php endif; ?>
    </div>
    <?php if ($soldout): ?>
      <span class="soldout-badge">Habis</span>
    <?php else: ?>
      <div class="stepper">
        <button type="button" data-minus aria-label="Kurangi <?= html_escape($t->name) ?>"><i class="bi bi-dash"></i></button>
        <input type="number" inputmode="numeric" name="qty[<?= (int) $t->id ?>]" value="<?= isset($selected[$t->id]) ? min((int) $selected[$t->id], $avail) : 0 ?>" min="0" max="<?= min($this->fmt->max_tickets(), $avail) ?>"
               data-qty data-price="<?= (int) $t->price ?>" data-stock="<?= $avail ?>" aria-label="Jumlah <?= html_escape($t->name) ?>">
        <button type="button" data-plus aria-label="Tambah <?= html_escape($t->name) ?>"><i class="bi bi-plus"></i></button>
      </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <div class="fee-note mt-2" data-out="limit">Maksimal <?= $this->fmt->max_tickets() ?> tiket per transaksi.</div>

  <table class="fee-table mt-3">
    <tr><td>Harga tiket (<span data-out="qty">0</span>)</td><td data-out="subtotal">Rp0</td></tr>
    <tr><td>Biaya layanan <span class="text-muted-k">(<span data-out="service-label">0 × <?= $this->fmt->rupiah($this->fmt->service_fee()) ?></span>)</span></td><td data-out="service">Rp0</td></tr>
    <tr><td>Biaya transaksi</td><td data-out="trx">Rp0</td></tr>
    <tr class="total"><td>Total</td><td data-out="total">Rp0</td></tr>
  </table>

  <?php if ( ! $sticky_bar): ?>
  <button type="submit" class="btn btn-sun btn-lg w-100 mt-3" data-submit-ticket disabled>Lihat rincian tiket</button>
  <p class="fee-note text-center mt-2 mb-0">Kamu bisa cek rincian dulu sebelum membayar.</p>
  <?php endif; ?>
</form>
