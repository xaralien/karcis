<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('admin/reports/_print_head'); ?>
<div class="cards">
  <div class="card"><div class="k">Pesanan lunas</div><div class="v"><?= (int) $summary->paid ?></div></div>
  <div class="card"><div class="k">Tiket terjual</div><div class="v"><?= (int) $summary->tickets ?></div></div>
  <div class="card"><div class="k">Nilai tiket</div><div class="v"><?= $this->fmt->rupiah($summary->ticket_revenue) ?></div></div>
  <div class="card"><div class="k">Biaya layanan</div><div class="v"><?= $this->fmt->rupiah($summary->service_fee) ?></div></div>
  <div class="card"><div class="k">Biaya transaksi</div><div class="v"><?= $this->fmt->rupiah($summary->transaction_fee) ?></div></div>
  <div class="card"><div class="k">Total diterima</div><div class="v"><?= $this->fmt->rupiah($summary->gross) ?></div></div>
</div>
<p style="font-size:11px;color:var(--muted);margin:0 0 12px">
  Pesanan tidak lunas: <?= (int) $summary->pending ?> menunggu, <?= (int) $summary->failed ?> gagal, <?= (int) $summary->expired ?> kedaluwarsa.
</p>

<h2 style="font-size:14px;margin:0 0 6px">Rekap per kategori tiket</h2>
<table style="margin-bottom:14px">
  <thead><tr><th>Event</th><th>Kategori tiket</th><th class="num">Harga</th><th class="num">Kuota</th><th class="num">Terjual</th><th class="num">Nilai</th></tr></thead>
  <tbody>
  <?php foreach ($per_type as $t): ?>
    <tr>
      <td><?= html_escape($t->event_title) ?></td>
      <td><?= html_escape($t->ticket_name) ?></td>
      <td class="num"><?= $this->fmt->rupiah($t->price) ?></td>
      <td class="num"><?= (int) $t->quota ?></td>
      <td class="num"><?= (int) $t->qty_paid ?></td>
      <td class="num"><?= $this->fmt->rupiah($t->revenue) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<h2 style="font-size:14px;margin:0 0 6px">Rincian transaksi</h2>
<table>
  <thead><tr><th>Kode</th><th>Dibuat</th><th>Dibayar</th><th>Event</th><th>Pembeli</th><th class="num">Tiket</th><th class="num">Tiket (Rp)</th><th class="num">Layanan</th><th class="num">Transaksi</th><th class="num">Total</th><th>Status</th><th>Ref Duitku</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $o): ?>
    <tr>
      <td><?= html_escape($o->order_code) ?></td>
      <td><?= date('d/m/y H:i', strtotime($o->created_at)) ?></td>
      <td><?= $o->paid_at ? date('d/m/y H:i', strtotime($o->paid_at)) : '-' ?></td>
      <td><?= html_escape($o->event_title) ?></td>
      <td><?= html_escape($o->buyer_name) ?><br><span style="color:var(--muted)"><?= html_escape($o->buyer_email) ?></span></td>
      <td class="num"><?= (int) $o->ticket_qty ?></td>
      <td class="num"><?= $this->fmt->angka($o->subtotal) ?></td>
      <td class="num"><?= $this->fmt->angka($o->service_fee) ?></td>
      <td class="num"><?= $this->fmt->angka($o->transaction_fee) ?></td>
      <td class="num"><?= $this->fmt->angka($o->total) ?></td>
      <td><span class="tag <?= $o->status === 'paid' ? 'ok' : 'no' ?>"><?= $label[$o->status] ?></span></td>
      <td><?= html_escape($o->duitku_reference) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="5">Total lunas</td>
      <td class="num"><?= (int) $summary->tickets ?></td>
      <td class="num"><?= $this->fmt->angka($summary->ticket_revenue) ?></td>
      <td class="num"><?= $this->fmt->angka($summary->service_fee) ?></td>
      <td class="num"><?= $this->fmt->angka($summary->transaction_fee) ?></td>
      <td class="num"><?= $this->fmt->angka($summary->gross) ?></td>
      <td colspan="2"></td>
    </tr>
  </tfoot>
</table>
<?php $this->load->view('admin/reports/_print_foot'); ?>
