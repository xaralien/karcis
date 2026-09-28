<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars('actions', '<a class="btn btn-primary btn-sm" href="' . $this->fmt->url('admin/events/create') . '"><i class="bi bi-plus-lg me-1"></i>Tambah event</a>');
?>
<div class="card-k">
  <div class="hd">
    <form class="d-flex gap-2 flex-wrap" method="get" action="<?= $this->fmt->url('admin/events') ?>">
      <input class="form-control form-control-sm" style="width:220px" type="search" name="q" value="<?= html_escape($filter['q']) ?>" placeholder="Cari judul event">
      <select class="form-select form-select-sm" style="width:150px" name="status" onchange="this.form.submit()">
        <option value="">Semua status</option>
        <?php foreach (array('published' => 'Tayang', 'draft' => 'Draft', 'ended' => 'Selesai') as $k => $v): ?>
          <option value="<?= $k ?>" <?= $filter['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-light-k btn-sm">Cari</button>
    </form>
    <span class="small text-muted-k"><?= count($events) ?> event</span>
  </div>
  <?php if ($events): ?>
  <div class="table-responsive">
    <table class="table table-k">
      <thead><tr><th></th><th>Event</th><th>Tanggal</th><th>Kota</th><th>Status</th><th>Tiket terjual</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($events as $e): $pct = $e->quota ? min(100, round($e->sold / $e->quota * 100)) : 0; ?>
        <tr>
          <td style="width:64px"><img class="thumb" src="<?= html_escape($this->fmt->img($e->thumbnail)) ?>" alt=""></td>
          <td>
            <a class="fw-semibold" href="<?= $this->fmt->url('admin/events/edit/' . $e->id) ?>"><?= html_escape($e->title) ?></a>
            <div class="small text-muted-k"><?= html_escape($e->category_name) ?><?= $e->is_featured ? ' · <i class="bi bi-star-fill text-warning"></i> Unggulan' : '' ?></div>
          </td>
          <td class="text-nowrap"><?= $this->fmt->tgl($e->start_date, FALSE) ?><div class="small text-muted-k"><?= (int) $e->total_days ?> hari</div></td>
          <td><?= html_escape($e->city) ?></td>
          <td><span class="badge-st <?= $e->status ?>"><?= array('published' => 'Tayang', 'draft' => 'Draft', 'ended' => 'Selesai')[$e->status] ?></span></td>
          <td style="min-width:150px"><div class="d-flex align-items-center gap-2"><div class="progress-k flex-grow-1"><span style="width:<?= $pct ?>%"></span></div><span class="small"><?= (int) $e->sold ?>/<?= (int) $e->quota ?></span></div></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-icon btn-light-k" href="<?= $this->fmt->url('event/detail/' . $e->slug) ?>" target="_blank" rel="noopener" title="Lihat di website"><i class="bi bi-eye"></i></a>
            <a class="btn btn-icon btn-light-k" href="<?= $this->fmt->url('admin/events/edit/' . $e->id) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
    <div class="empty-k"><i class="bi bi-calendar-x"></i>Belum ada event yang cocok.<div class="mt-2"><a class="btn btn-primary btn-sm" href="<?= $this->fmt->url('admin/events/create') ?>">Tambah event pertama</a></div></div>
  <?php endif; ?>
</div>
