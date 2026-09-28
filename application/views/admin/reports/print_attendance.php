<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('admin/reports/_print_head');
$pct = $summary->total ? round($summary->hadir / $summary->total * 100) : 0;
$max = 0; foreach ($by_hour as $h) $max = max($max, (int) $h->n);
?>
<div class="cards">
  <div class="card"><div class="k">Tiket terbit</div><div class="v"><?= (int) $summary->total ?></div></div>
  <div class="card"><div class="k">Sudah scan</div><div class="v" style="color:var(--mint)"><?= (int) $summary->hadir ?></div></div>
  <div class="card"><div class="k">Belum scan</div><div class="v" style="color:var(--danger)"><?= (int) $summary->belum ?></div></div>
  <div class="card"><div class="k">Tingkat kehadiran</div><div class="v"><?= $pct ?>%</div></div>
  <div class="card"><div class="k">Masuk pertama</div><div class="v" style="font-size:12px"><?= $summary->masuk_pertama ? date('d/m/Y H:i', strtotime($summary->masuk_pertama)) : '-' ?></div></div>
  <div class="card"><div class="k">Masuk terakhir</div><div class="v" style="font-size:12px"><?= $summary->masuk_terakhir ? date('d/m/Y H:i', strtotime($summary->masuk_terakhir)) : '-' ?></div></div>
</div>

<div style="display:flex; gap:14px; margin-bottom:14px; flex-wrap:wrap">
  <div style="flex:1; min-width:220px">
    <h2 style="font-size:14px;margin:0 0 6px">Kehadiran per kategori tiket</h2>
    <table>
      <thead><tr><th>Kategori</th><th class="num">Terbit</th><th class="num">Hadir</th><th class="num">Belum</th><th class="num">%</th></tr></thead>
      <tbody>
      <?php foreach ($per_type as $t): $p = $t->total ? round($t->hadir / $t->total * 100) : 0; ?>
        <tr><td><?= html_escape($t->ticket_name) ?></td><td class="num"><?= (int) $t->total ?></td><td class="num"><?= (int) $t->hadir ?></td><td class="num"><?= (int) $t->total - (int) $t->hadir ?></td><td class="num"><?= $p ?>%</td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($by_hour): ?>
  <div style="flex:1; min-width:220px">
    <h2 style="font-size:14px;margin:0 0 6px">Kepadatan jam masuk</h2>
    <table>
      <thead><tr><th>Jam</th><th class="num">Masuk</th><th style="width:45%">Grafik</th></tr></thead>
      <tbody>
      <?php foreach ($by_hour as $h): ?>
        <tr>
          <td><?= date('d/m H:i', strtotime($h->jam)) ?></td>
          <td class="num"><?= (int) $h->n ?></td>
          <td><span style="display:inline-block;height:9px;border-radius:5px;background:var(--plum);width:<?= $max ? round($h->n / $max * 100) : 0 ?>%"></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<h2 style="font-size:14px;margin:0 0 6px">Rincian per tiket</h2>
<table>
  <thead><tr><th>#</th><th>Kode tiket</th><th>Kategori</th><th>Pemesan</th><th>Email</th><th>Kode pesanan</th><th>Status</th><th>Waktu masuk</th><th>Petugas</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $i => $t): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><strong><?= html_escape($t->ticket_code) ?></strong></td>
      <td><?= html_escape($t->ticket_name) ?></td>
      <td><?= html_escape($t->buyer_name) ?></td>
      <td><?= html_escape($t->buyer_email) ?></td>
      <td><?= html_escape($t->order_code) ?></td>
      <td><span class="tag <?= $t->is_checked_in ? 'ok' : 'no' ?>"><?= $t->is_checked_in ? 'Sudah scan' : 'Belum scan' ?></span></td>
      <td><?= $t->checked_in_at ? date('d/m/Y H:i', strtotime($t->checked_in_at)) : '-' ?></td>
      <td><?= html_escape($t->petugas ?: '-') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php $this->load->view('admin/reports/_print_foot'); ?>
