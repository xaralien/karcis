<?php defined('BASEPATH') OR exit('No direct script access allowed');
$qs = function ($over) use ($filter) { return $this->fmt->url('explore') . '?' . http_build_query(array_filter(array_merge($filter, $over))); };
?>
<form class="m-search" method="get" action="<?= $this->fmt->url('explore') ?>">
  <?php if ($filter['kategori']): ?><input type="hidden" name="kategori" value="<?= html_escape($filter['kategori']) ?>"><?php endif; ?>
  <div class="wrap"><i class="bi bi-search"></i><input class="form-control" type="search" name="q" value="<?= html_escape($filter['q']) ?>" placeholder="Cari event atau venue" aria-label="Cari event"></div>
  <div class="d-flex gap-2 mt-2">
    <select class="form-select form-select-sm" name="kota" onchange="this.form.submit()" aria-label="Kota">
      <option value="">Semua kota</option>
      <?php foreach ($cities as $c): ?><option <?= $filter['kota'] === $c->city ? 'selected' : '' ?>><?= html_escape($c->city) ?></option><?php endforeach; ?>
    </select>
    <select class="form-select form-select-sm" name="sort" onchange="this.form.submit()" aria-label="Urutkan">
      <option value="terdekat" <?= $filter['sort'] === 'terdekat' ? 'selected' : '' ?>>Terdekat</option>
      <option value="terbaru" <?= $filter['sort'] === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
      <option value="az" <?= $filter['sort'] === 'az' ? 'selected' : '' ?>>A–Z</option>
    </select>
  </div>
</form>
<div class="pill-scroll">
  <a class="pill <?= ! $filter['kategori'] ? 'active' : '' ?>" href="<?= $qs(array('kategori' => '')) ?>">Semua</a>
  <?php foreach ($categories as $c): ?>
  <a class="pill <?= $filter['kategori'] === $c->slug ? 'active' : '' ?>" href="<?= $qs(array('kategori' => $c->slug)) ?>"><?= html_escape($c->name) ?></a>
  <?php endforeach; ?>
</div>

<div class="m-page">
  <div class="small text-muted-k mb-2"><?= (int) $total ?> event ditemukan</div>
  <?php if ($events): ?>
    <?php foreach ($events as $e): ?><?php $this->load->view('mobile/partials/row_card', array('e' => $e)); ?><?php endforeach; ?>
    <nav class="mt-4 d-flex justify-content-center" aria-label="Halaman"><?php $this->load->view('shared/pagination', array('pages' => $pages, 'page_base' => $this->fmt->url('explore'))); ?></nav>
  <?php else: ?>
    <div class="m-card text-center py-5">
      <i class="bi bi-search fs-2 text-muted-k"></i>
      <h5 class="mt-2">Belum ada event yang cocok</h5>
      <p class="small text-muted-k">Coba kata kunci lain atau pilih kategori “Semua”.</p>
      <a href="<?= $this->fmt->url('explore') ?>" class="btn btn-primary btn-sm">Hapus filter</a>
    </div>
  <?php endif; ?>
</div>
