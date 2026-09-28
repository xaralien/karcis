<?php defined('BASEPATH') OR exit('No direct script access allowed'); $d = $this->fmt->tgl_pendek($e->start_date); ?>
<a class="stub" href="<?= $this->fmt->url('event/detail/' . $e->slug) ?>">
  <div class="stub-media">
    <img src="<?= html_escape($this->fmt->img($e->thumbnail)) ?>" alt="" loading="lazy">
    <span class="chip"><?= html_escape($e->category_name) ?></span>
  </div>
  <div class="stub-tear" aria-hidden="true"></div>
  <div class="stub-body">
    <div class="stub-date"><div class="d"><?= $d['d'] ?></div><div class="m"><?= $d['m'] ?></div></div>
    <div class="stub-info">
      <h3 class="stub-title"><?= html_escape($e->title) ?></h3>
      <div class="stub-meta"><i class="bi bi-geo-alt me-1"></i><?= html_escape($e->venue) ?>, <?= html_escape($e->city) ?></div>
      <?php if ($e->total_days > 1): ?><div class="stub-meta"><i class="bi bi-calendar3 me-1"></i><?= (int) $e->total_days ?> hari</div><?php endif; ?>
      <div class="stub-price"><small>Mulai</small> <?= $this->fmt->rupiah($e->min_price) ?></div>
    </div>
  </div>
</a>
