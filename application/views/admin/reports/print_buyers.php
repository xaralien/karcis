<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('admin/reports/_print_head');
$tot_spent = 0; $tot_tickets = 0;
foreach ($rows as $r) { $tot_spent += (int) $r->spent; $tot_tickets += (int) $r->tickets; }
?>
<div class="cards">
  <div class="card"><div class="k">Pembeli unik</div><div class="v"><?= count($rows) ?></div></div>
  <div class="card"><div class="k">Tiket terjual</div><div class="v"><?= $this->fmt->angka($tot_tickets) ?></div></div>
  <div class="card"><div class="k">Nilai transaksi</div><div class="v"><?= $this->fmt->rupiah($tot_spent) ?></div></div>
  <div class="card"><div class="k">Pembeli berulang</div><div class="v"><?= count(array_filter($rows, function ($r) { return $r->orders > 1; })) ?></div></div>
</div>
<table>
  <thead><tr><th>#</th><th>Nama</th><th>Email</th><th>Nomor HP</th><th class="num">Pesanan</th><th class="num">Tiket</th><th class="num">Belanja</th><th class="num">Hadir</th><th>Terakhir beli</th><th>Event yang dibeli</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= html_escape($r->buyer_name) ?><?= $r->has_account ? ' <span class="tag ok">akun</span>' : '' ?></td>
      <td><?= html_escape($r->buyer_email) ?></td>
      <td><?= html_escape($r->buyer_phone) ?></td>
      <td class="num"><?= (int) $r->orders ?></td>
      <td class="num"><?= (int) $r->tickets ?></td>
      <td class="num"><?= $this->fmt->rupiah($r->spent) ?></td>
      <td class="num"><?= (int) $r->attended ?></td>
      <td><?= $r->last_order ? date('d/m/Y', strtotime($r->last_order)) : '-' ?></td>
      <td><?= html_escape($r->events) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot><tr><td colspan="4">Total <?= count($rows) ?> pembeli</td><td class="num"><?= array_sum(array_map(function ($r) { return (int) $r->orders; }, $rows)) ?></td><td class="num"><?= $tot_tickets ?></td><td class="num"><?= $this->fmt->rupiah($tot_spent) ?></td><td colspan="3"></td></tr></tfoot>
</table>
<?php $this->load->view('admin/reports/_print_foot'); ?>
