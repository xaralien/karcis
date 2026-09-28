<?php defined('BASEPATH') OR exit('No direct script access allowed');
$qs = http_build_query(array_filter(array('event_id' => $filter['event_id'], 'from' => $filter['from'], 'to' => $filter['to'])));
$link = function ($jenis, $format) use ($qs) { return $this->fmt->url('admin/reports/' . $jenis . '/' . $format) . ($qs ? '?' . $qs : ''); };
$pct = $attendance->total ? round($attendance->hadir / $attendance->total * 100) : 0;
?>
<div class="card-k">
  <div class="hd"><h5>Pilih data</h5><span class="hint">Filter berlaku untuk semua laporan di bawah</span></div>
  <div class="bd">
    <form class="row g-2 align-items-end" method="get" action="<?= $this->fmt->url('admin/reports') ?>">
      <div class="col-lg-5 col-md-6">
        <label class="form-label small" for="event_id">Event</label>
        <select class="form-select form-select-sm" id="event_id" name="event_id">
          <option value="0">Semua event</option>
          <?php foreach ($events as $e): ?><option value="<?= $e->id ?>" <?= $filter['event_id'] == $e->id ? 'selected' : '' ?>><?= html_escape($e->title) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-3 col-md-3 col-6"><label class="form-label small" for="from">Dari tanggal</label><input class="form-control form-control-sm" id="from" type="date" name="from" value="<?= html_escape($filter['from']) ?>"></div>
      <div class="col-lg-3 col-md-3 col-6"><label class="form-label small" for="to">Sampai tanggal</label><input class="form-control form-control-sm" id="to" type="date" name="to" value="<?= html_escape($filter['to']) ?>"></div>
      <div class="col-lg-1"><button class="btn btn-primary btn-sm w-100">Terapkan</button></div>
    </form>
  </div>
</div>

<div class="row g-3 mt-1">
  <!-- 1. Data pembeli -->
  <div class="col-xl-4">
    <div class="card-k h-100">
      <div class="hd"><h5><i class="bi bi-people me-2"></i>Data pembeli</h5></div>
      <div class="bd">
        <p class="small text-muted-k">Satu baris per pembeli untuk remarketing: email, nomor HP, jumlah pesanan, total belanja, event yang pernah dibeli, kategori favorit, dan berapa tiketnya benar-benar dipakai masuk.</p>
        <div class="stat mb-3"><div class="lbl">Pembeli unik pada filter ini</div><div class="val"><?= $this->fmt->angka($buyers) ?></div></div>
        <div class="d-grid gap-2">
          <a class="btn btn-primary btn-sm" href="<?= $link('buyers', 'xls') ?>"><i class="bi bi-file-earmark-excel me-1"></i>Unduh Excel (.xls)</a>
          <div class="d-flex gap-2">
            <a class="btn btn-light-k btn-sm flex-fill" href="<?= $link('buyers', 'csv') ?>">CSV</a>
            <a class="btn btn-light-k btn-sm flex-fill" href="<?= $link('buyers', 'pdf') ?>" target="_blank" rel="noopener">PDF</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Audit penjualan -->
  <div class="col-xl-4">
    <div class="card-k h-100">
      <div class="hd"><h5><i class="bi bi-clipboard-check me-2"></i>Audit penjualan</h5></div>
      <div class="bd">
        <p class="small text-muted-k">Rincian setiap transaksi beserta pemisahan harga tiket, biaya layanan, dan biaya transaksi, plus rekap per kategori tiket dan referensi Duitku untuk dicocokkan dengan mutasi rekening.</p>
        <div class="row g-2 mb-3">
          <div class="col-6"><div class="stat h-100"><div class="lbl">Total diterima</div><div class="val" style="font-size:1.25rem"><?= $this->fmt->rupiah($summary->gross) ?></div></div></div>
          <div class="col-6"><div class="stat h-100"><div class="lbl">Pesanan lunas</div><div class="val"><?= (int) $summary->paid ?></div></div></div>
        </div>
        <div class="d-grid gap-2">
          <a class="btn btn-primary btn-sm" href="<?= $link('audit', 'xls') ?>"><i class="bi bi-file-earmark-excel me-1"></i>Unduh Excel (.xls)</a>
          <div class="d-flex gap-2">
            <a class="btn btn-light-k btn-sm flex-fill" href="<?= $link('audit', 'csv') ?>">CSV</a>
            <a class="btn btn-light-k btn-sm flex-fill" href="<?= $link('audit', 'pdf') ?>" target="_blank" rel="noopener">PDF</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. Data kehadiran -->
  <div class="col-xl-4">
    <div class="card-k h-100">
      <div class="hd"><h5><i class="bi bi-door-open me-2"></i>Data kehadiran</h5></div>
      <div class="bd">
        <p class="small text-muted-k">Laporan akhir: tiket mana yang sudah dipindai dan mana yang belum, lengkap dengan waktu masuk, petugas yang memindai, kehadiran per kategori, dan kepadatan jam masuk.</p>
        <div class="row g-2 mb-3">
          <div class="col-4"><div class="stat h-100"><div class="lbl">Terbit</div><div class="val"><?= (int) $attendance->total ?></div></div></div>
          <div class="col-4"><div class="stat h-100"><div class="lbl">Hadir</div><div class="val" style="color:var(--mint)"><?= (int) $attendance->hadir ?></div></div></div>
          <div class="col-4"><div class="stat h-100"><div class="lbl">Belum</div><div class="val" style="color:var(--danger)"><?= (int) $attendance->belum ?></div></div></div>
        </div>
        <div class="progress-k mb-3"><span style="width:<?= $pct ?>%"></span></div>
        <div class="d-grid gap-2">
          <a class="btn btn-primary btn-sm" href="<?= $link('attendance', 'xls') ?>"><i class="bi bi-file-earmark-excel me-1"></i>Unduh Excel (.xls)</a>
          <div class="d-flex gap-2">
            <a class="btn btn-light-k btn-sm flex-fill" href="<?= $link('attendance', 'csv') ?>">CSV</a>
            <a class="btn btn-light-k btn-sm flex-fill" href="<?= $link('attendance', 'pdf') ?>" target="_blank" rel="noopener">PDF</a>
          </div>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-ink btn-sm flex-fill" href="<?= $link('attendance', 'xls') ?><?= $qs ? '&' : '?' ?>hadir=0">Hanya yang belum scan</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<p class="hint mt-3 mb-0">
  File .xls terbuka di Excel, LibreOffice, dan Google Sheets. Tombol PDF membuka halaman siap cetak,
  lalu pilih tujuan <strong>Simpan sebagai PDF</strong> pada dialog cetak browser.
</p>
