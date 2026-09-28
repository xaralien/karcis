<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars('actions', '<a class="btn btn-primary btn-sm" href="' . $this->fmt->url('admin/events/create') . '"><i class="bi bi-plus-lg me-1"></i>Tambah event</a>');
$st = array('published' => 'Tayang', 'draft' => 'Draft', 'ended' => 'Selesai', 'paid' => 'Lunas', 'pending' => 'Menunggu', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa');
$labels = array(); $rev = array(); $tix = array();
foreach ($daily as $d) { $labels[] = date('d/m', strtotime($d['date'])); $rev[] = $d['revenue']; $tix[] = $d['tickets']; }
?>
<div class="row g-3">
  <div class="col-xl-3 col-sm-6"><div class="stat hero">
    <div class="lbl">Total pendapatan</div><div class="val"><?= $this->fmt->rupiah($stats['revenue']) ?></div>
    <div class="sub">Tiket <?= $this->fmt->rupiah($stats['ticket_revenue']) ?> + biaya <?= $this->fmt->rupiah($stats['fee_revenue']) ?></div>
  </div></div>
  <div class="col-xl-3 col-sm-6"><div class="stat">
    <div class="lbl">Tiket terjual</div><div class="val"><?= number_format($stats['tickets_sold'], 0, ',', '.') ?></div>
    <div class="sub">dari <?= number_format($stats['paid_orders'], 0, ',', '.') ?> pesanan lunas</div>
  </div></div>
  <div class="col-xl-3 col-sm-6"><div class="stat">
    <div class="lbl">Sudah check-in</div><div class="val"><?= number_format($stats['checked_in'], 0, ',', '.') ?></div>
    <div class="sub"><?= $stats['tickets_sold'] ? round($stats['checked_in'] / $stats['tickets_sold'] * 100) : 0 ?>% dari tiket terjual</div>
  </div></div>
  <div class="col-xl-3 col-sm-6"><div class="stat">
    <div class="lbl">Menunggu pembayaran</div><div class="val"><?= $stats['pending_orders'] ?></div>
    <div class="sub"><?= $stats['active_events'] ?> event aktif</div>
  </div></div>
</div>

<div class="row g-3 mt-1">
  <div class="col-xl-8">
    <div class="card-k h-100">
      <div class="hd"><h5>Penjualan 14 hari terakhir</h5></div>
      <div class="bd"><div style="height:280px"><canvas id="salesChart" aria-label="Grafik pendapatan harian"></canvas></div></div>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="card-k h-100">
      <div class="hd"><h5>Pesanan terbaru</h5><a class="small fw-semibold" href="<?= $this->fmt->url('admin/orders') ?>">Semua</a></div>
      <?php if ($orders): ?>
      <div class="list-group list-group-flush">
        <?php foreach ($orders as $o): ?>
        <a class="list-group-item list-group-item-action px-3 py-2" href="<?= $this->fmt->url('admin/orders/detail/' . $o->order_code) ?>">
          <div class="d-flex justify-content-between"><strong class="small"><?= html_escape($o->buyer_name) ?></strong><span class="badge-st <?= $o->status ?>"><?= $st[$o->status] ?></span></div>
          <div class="d-flex justify-content-between small text-muted-k"><span class="text-truncate me-2"><?= html_escape($o->event_title) ?> · <?= $o->ticket_qty ?> tiket</span><span><?= $this->fmt->rupiah($o->total) ?></span></div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?><div class="empty-k"><i class="bi bi-receipt"></i>Belum ada pesanan.</div><?php endif; ?>
    </div>
  </div>
</div>

<div class="card-k mt-3">
  <div class="hd"><h5>Penjualan per event</h5></div>
  <div class="table-responsive">
    <table class="table table-k">
      <thead><tr><th>Event</th><th>Tanggal</th><th>Status</th><th>Terjual / kuota</th><th class="num">Check-in</th><th class="num">Pendapatan tiket</th></tr></thead>
      <tbody>
      <?php foreach ($events as $e): $pct = $e->quota ? min(100, round($e->sold / $e->quota * 100)) : 0; ?>
        <tr>
          <td><a class="fw-semibold" href="<?= $this->fmt->url('admin/events/edit/' . $e->id) ?>"><?= html_escape($e->title) ?></a></td>
          <td class="text-nowrap"><?= $this->fmt->tgl($e->start_date, FALSE) ?></td>
          <td><span class="badge-st <?= $e->status ?>"><?= $st[$e->status] ?></span></td>
          <td><div class="d-flex align-items-center gap-2"><div class="progress-k flex-grow-1"><span style="width:<?= $pct ?>%"></span></div><span class="small text-nowrap"><?= (int) $e->sold ?>/<?= (int) $e->quota ?></span></div></td>
          <td class="num"><?= (int) $e->checked_in ?></td>
          <td class="num fw-semibold"><?= $this->fmt->rupiah($e->revenue) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php ob_start(); ?>
<script src="<?= $this->fmt->asset('assets/vendor/js/chart.umd.min.js') ?>"></script>
<script>
(function () {
  if (!window.Chart) return;
  new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [
        { label: 'Pendapatan', data: <?= json_encode($rev) ?>, backgroundColor: '#5B3FD9', borderRadius: 6, yAxisID: 'y' },
        { label: 'Tiket', data: <?= json_encode($tix) ?>, type: 'line', borderColor: '#FFB800', backgroundColor: '#FFB800', tension: 0, yAxisID: 'y1' }
      ]
    },
    options: {
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function (c) { return c.dataset.yAxisID === 'y' ? 'Rp' + c.raw.toLocaleString('id-ID') : c.raw + ' tiket'; } } } },
      scales: {
        y: { beginAtZero: true, ticks: { callback: function (v) { return 'Rp' + (v / 1000).toLocaleString('id-ID') + 'rb'; } }, grid: { color: '#EEE9FF' } },
        y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });
})();
</script>
<?php $this->load->vars('scripts', ob_get_clean()); ?>
