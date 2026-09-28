<?php defined('BASEPATH') OR exit('No direct script access allowed');
$qs = http_build_query(array_filter($filter));
$this->load->vars('actions', '<a class="btn btn-light-k btn-sm" href="' . $this->fmt->url('admin/orders/export') . ($qs ? '?' . $qs : '') . '"><i class="bi bi-download me-1"></i>Unduh CSV</a>');
$labels = array('paid' => 'Lunas', 'pending' => 'Menunggu', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa');
?>
<div class="card-k">
  <div class="hd">
    <form class="d-flex flex-wrap gap-2 w-100 align-items-center" method="get" action="<?= $this->fmt->url('admin/orders') ?>">
      <input class="form-control form-control-sm" style="flex:1 1 220px;min-width:180px" type="search" name="q" value="<?= html_escape($filter['q']) ?>" placeholder="Kode, nama, email, HP">
      <select class="form-select form-select-sm" style="flex:1 1 200px;min-width:170px" name="event_id" aria-label="Event">
        <option value="">Semua event</option>
        <?php foreach ($events as $e): ?><option value="<?= $e->id ?>" <?= $filter['event_id'] == $e->id ? 'selected' : '' ?>><?= html_escape($e->title) ?></option><?php endforeach; ?>
      </select>
      <select class="form-select form-select-sm" style="flex:0 1 150px;min-width:130px" name="status" aria-label="Status">
        <option value="">Semua status</option>
        <?php foreach ($labels as $k => $v): ?><option value="<?= $k ?>" <?= $filter['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
      </select>
      <input class="form-control form-control-sm" style="flex:0 1 145px" type="date" name="from" value="<?= html_escape($filter['from']) ?>" aria-label="Dari tanggal">
      <input class="form-control form-control-sm" style="flex:0 1 145px" type="date" name="to" value="<?= html_escape($filter['to']) ?>" aria-label="Sampai tanggal">
      <button class="btn btn-primary btn-sm px-4 text-nowrap">Filter</button>
      <?php if (array_filter($filter)): ?><a class="btn btn-light-k btn-sm text-nowrap" href="<?= $this->fmt->url('admin/orders') ?>">Reset</a><?php endif; ?>
    </form>
  </div>
  <?php if ($orders): ?>
  <div class="table-responsive">
    <table class="table table-k">
      <thead><tr><th>Kode</th><th>Pembeli</th><th>Event</th><th class="num">Tiket</th><th class="num">Scan</th><th class="num">Total</th><th>Status</th><th>Dibuat</th></tr></thead>
      <tbody>
      <?php foreach ($orders as $o): ?>
        <tr style="cursor:pointer" onclick="location.href='<?= $this->fmt->url('admin/orders/detail/' . $o->order_code) ?>'">
          <td><a class="fw-semibold text-nowrap" href="<?= $this->fmt->url('admin/orders/detail/' . $o->order_code) ?>"><?= html_escape($o->order_code) ?></a></td>
          <td><?= html_escape($o->buyer_name) ?><div class="small text-muted-k"><?= html_escape($o->buyer_email) ?></div></td>
          <td class="small"><?= html_escape($o->event_title) ?></td>
          <td class="num"><?= (int) $o->ticket_qty ?></td>
          <td class="num">
            <?php if ( ! $o->tickets_issued): ?><span class="text-muted-k">–</span>
            <?php elseif ($o->tickets_scanned >= $o->tickets_issued): ?><span class="badge-st paid"><?= (int) $o->tickets_scanned ?>/<?= (int) $o->tickets_issued ?></span>
            <?php elseif ($o->tickets_scanned > 0): ?><span class="badge-st pending"><?= (int) $o->tickets_scanned ?>/<?= (int) $o->tickets_issued ?></span>
            <?php else: ?><span class="badge-st ended">0/<?= (int) $o->tickets_issued ?></span><?php endif; ?>
          </td>
          <td class="num fw-semibold"><?= $this->fmt->rupiah($o->total) ?></td>
          <td><span class="badge-st <?= $o->status ?>"><?= $labels[$o->status] ?></span></td>
          <td class="small text-nowrap"><?= date('d/m/Y H.i', strtotime($o->created_at)) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="bd d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span class="small text-muted-k">
      <?= number_format($total, 0, ',', '.') ?> pesanan ·
      <strong><?= (int) $scan->scanned ?></strong> dari <strong><?= (int) $scan->issued ?></strong> tiket sudah scan
      <?php if ($scan->issued): ?>(<?= round($scan->scanned / $scan->issued * 100) ?>%), <?= (int) $scan->issued - (int) $scan->scanned ?> belum<?php endif; ?>
    </span>
    <?php $this->load->view('shared/pagination', array('pages' => $pages, 'page_base' => $this->fmt->url('admin/orders'))); ?>
  </div>
  <?php else: ?>
    <div class="empty-k"><i class="bi bi-receipt"></i>Tidak ada pesanan yang cocok dengan filter.</div>
  <?php endif; ?>
</div>
