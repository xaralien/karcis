<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<a class="row-card" href="<?= $this->fmt->url('event/detail/' . $e->slug) ?>">
  <img src="<?= html_escape($this->fmt->img($e->thumbnail)) ?>" alt="" loading="lazy">
  <div class="bd">
    <div class="small fw-bold" style="color:var(--plum)"><?= $this->fmt->tgl($e->start_date, FALSE) ?><?= $e->total_days > 1 ? ' · ' . (int) $e->total_days . ' hari' : '' ?></div>
    <div class="t"><?= html_escape($e->title) ?></div>
    <div class="mt"><i class="bi bi-geo-alt"></i> <?= html_escape($e->venue) ?>, <?= html_escape($e->city) ?></div>
    <div class="p">Mulai <?= $this->fmt->rupiah($e->min_price) ?></div>
  </div>
</a>
