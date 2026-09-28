<?php defined('BASEPATH') OR exit('No direct script access allowed');
$qs = function ($over) use ($filter) { return $this->fmt->url('explore') . '?' . http_build_query(array_filter(array_merge($filter, $over))); };
?>
<section class="page-head">
  <div class="container">
    <h1>Jelajahi event</h1>
    <form class="row g-2 mt-3" method="get" action="<?= $this->fmt->url('explore') ?>">
      <?php if ($filter['kategori']): ?><input type="hidden" name="kategori" value="<?= html_escape($filter['kategori']) ?>"><?php endif; ?>
      <div class="col-md-6"><input class="form-control form-control-lg" type="search" name="q" value="<?= html_escape($filter['q']) ?>" placeholder="Nama event, venue, atau penyelenggara"></div>
      <div class="col-md-3">
        <select class="form-select form-select-lg" name="kota" aria-label="Kota">
          <option value="">Semua kota</option>
          <?php foreach ($cities as $c): ?><option <?= $filter['kota'] === $c->city ? 'selected' : '' ?>><?= html_escape($c->city) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3"><button class="btn btn-sun btn-lg w-100">Cari event</button></div>
    </form>
  </div>
</section>

<div class="container py-5">
  <div class="row g-4">
    <aside class="col-lg-3">
      <div class="filter-card">
        <h6>Kategori</h6>
        <a class="cat-radio <?= ! $filter['kategori'] ? 'active' : '' ?>" href="<?= $qs(array('kategori' => '', 'page' => '')) ?>"><span>Semua</span></a>
        <?php foreach ($categories as $c): ?>
          <a class="cat-radio <?= $filter['kategori'] === $c->slug ? 'active' : '' ?>" href="<?= $qs(array('kategori' => $c->slug)) ?>">
            <span><i class="bi <?= html_escape($c->icon) ?> me-2"></i><?= html_escape($c->name) ?></span><span class="text-muted-k"><?= (int) $c->total ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </aside>
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div><strong><?= (int) $total ?></strong> event ditemukan<?= $filter['q'] ? ' untuk “' . html_escape($filter['q']) . '”' : '' ?></div>
        <form method="get" action="<?= $this->fmt->url('explore') ?>" class="d-flex align-items-center gap-2">
          <?php foreach (array('q', 'kategori', 'kota') as $k): if ($filter[$k]): ?><input type="hidden" name="<?= $k ?>" value="<?= html_escape($filter[$k]) ?>"><?php endif; endforeach; ?>
          <label for="sort" class="text-muted-k small text-nowrap">Urutkan</label>
          <select id="sort" name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="terdekat" <?= $filter['sort'] === 'terdekat' ? 'selected' : '' ?>>Tanggal terdekat</option>
            <option value="terbaru" <?= $filter['sort'] === 'terbaru' ? 'selected' : '' ?>>Baru ditambahkan</option>
            <option value="az" <?= $filter['sort'] === 'az' ? 'selected' : '' ?>>Nama A–Z</option>
          </select>
        </form>
      </div>

      <?php if ($events): ?>
      <div class="row g-4">
        <?php foreach ($events as $e): ?>
          <div class="col-xl-4 col-md-6"><?php $this->load->view('desktop/partials/event_card', array('e' => $e)); ?></div>
        <?php endforeach; ?>
      </div>
      <nav class="mt-5 d-flex justify-content-center" aria-label="Halaman"><?php $this->load->view('shared/pagination', array('pages' => $pages, 'page_base' => $this->fmt->url('explore'))); ?></nav>
      <?php else: ?>
      <div class="panel text-center py-5">
        <i class="bi bi-search fs-1 text-muted-k"></i>
        <h5 class="mt-3">Belum ada event yang cocok</h5>
        <p class="text-muted-k">Coba kata kunci lain atau hapus filter kota dan kategori.</p>
        <a href="<?= $this->fmt->url('explore') ?>" class="btn btn-primary">Hapus semua filter</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
