<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars(array('back_url' => $this->fmt->url('explore'), 'top_title' => $event->title, 'body_class' => 'has-buybar'));
?>
<div class="m-ev-banner"><span class="banner-frame"><img src="<?= html_escape($this->fmt->img($event->banner)) ?>" alt="Banner <?= html_escape($event->title) ?>"></span></div>
<section class="m-ev-head">
  <span class="chip" style="background:var(--plum-tint)"><?= html_escape($event->category_name) ?></span>
  <h1><?= html_escape($event->title) ?></h1>
  <div class="ln"><i class="bi bi-calendar3"></i><span><?= $this->fmt->tgl($event->start_date, FALSE) ?><?= $event->end_date !== $event->start_date ? ' – ' . $this->fmt->tgl($event->end_date, FALSE) : '' ?></span></div>
  <div class="ln"><i class="bi bi-geo-alt"></i><span><?= html_escape($event->venue) ?>, <?= html_escape($event->city) ?></span></div>
  <div class="ln"><i class="bi bi-person-badge"></i><span><?= html_escape($event->organizer) ?></span></div>
</section>

<ul class="nav m-tabs" role="tablist">
  <li class="flex-fill" role="presentation"><button class="nav-link w-100 active" data-bs-toggle="tab" data-bs-target="#m-desc" type="button" role="tab">Deskripsi</button></li>
  <li class="flex-fill" role="presentation"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#m-fac" type="button" role="tab">Fasilitas</button></li>
  <li class="flex-fill" role="presentation"><button class="nav-link w-100" data-bs-toggle="tab" data-bs-target="#m-guest" type="button" role="tab">Bintang Tamu</button></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="m-desc" role="tabpanel">
    <div class="m-block">
      <h5>Tentang event</h5>
      <div class="prose"><?php foreach (preg_split("/\n\s*\n/", $event->description) as $p): ?><p><?= nl2br(html_escape($p)) ?></p><?php endforeach; ?></div>
    </div>
    <div class="m-block">
      <h5>Tipe event</h5>
      <dl class="info-kv">
        <dt>Tipe</dt><dd><?= html_escape($event->event_type) ?></dd>
        <dt>Kategori</dt><dd><?= html_escape($event->category_name) ?></dd>
        <dt>Durasi</dt><dd><?= (int) $event->total_days ?> hari</dd>
      </dl>
    </div>
    <?php if ($gallery): ?>
    <div class="m-block">
      <h5>Galeri</h5>
      <div class="gallery-grid">
        <?php foreach ($gallery as $g): ?><a href="<?= html_escape($this->fmt->img($g->image)) ?>" target="_blank" rel="noopener"><img src="<?= html_escape($this->fmt->img($g->image)) ?>" alt="<?= html_escape($g->caption) ?>" loading="lazy"></a><?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <div class="tab-pane fade" id="m-fac" role="tabpanel">
    <div class="m-block">
      <h5>Fasilitas di lokasi</h5>
      <?php if ($facilities): ?>
      <div class="row g-2"><?php foreach ($facilities as $f): ?><div class="col-6"><div class="facility h-100"><i class="bi <?= html_escape($f->icon) ?>"></i><?= html_escape($f->name) ?></div></div><?php endforeach; ?></div>
      <?php else: ?><p class="small text-muted-k mb-0">Belum ada informasi fasilitas.</p><?php endif; ?>
    </div>
  </div>
  <div class="tab-pane fade" id="m-guest" role="tabpanel">
    <div class="m-block">
      <h5>Bintang tamu</h5>
      <?php if ($guests): ?>
      <div class="row g-3"><?php foreach ($guests as $g): ?>
        <div class="col-4 guest text-center"><img src="<?= html_escape($this->fmt->img($g->photo)) ?>" alt="<?= html_escape($g->name) ?>" loading="lazy"><div class="guest-name small"><?= html_escape($g->name) ?></div><div class="guest-role" style="font-size:.72rem"><?= html_escape($g->role) ?></div></div>
      <?php endforeach; ?></div>
      <?php else: ?><p class="small text-muted-k mb-0">Event ini tidak memiliki bintang tamu.</p><?php endif; ?>
    </div>
  </div>
</div>

<div class="m-block" id="jadwal">
  <h5>Jadwal &amp; lokasi</h5>
  <?php foreach ($schedules as $s): $d = $this->fmt->tgl_pendek($s->event_date); ?>
  <div class="sched">
    <div class="sched-day"><div class="d"><?= $d['d'] ?></div><div class="m"><?= $d['m'] ?></div></div>
    <div class="small">
      <div class="fw-bold" style="font-size:.95rem"><?= html_escape($s->label) ?></div>
      <div><?= $this->fmt->tgl($s->event_date) ?>, <?= $this->fmt->jam($s->start_time) ?>–<?= $this->fmt->jam($s->end_time) ?> WIB</div>
      <div class="text-muted-k"><?= html_escape($s->venue) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if ($event->maps_url): ?><a class="btn btn-outline-ink btn-sm mt-2" href="<?= html_escape($event->maps_url) ?>" target="_blank" rel="noopener"><i class="bi bi-map me-1"></i>Buka peta</a><?php endif; ?>
</div>

<?php $any = FALSE; foreach ($ticket_types as $t) if ((int) $t->available > 0) $any = TRUE; ?>
<div class="m-block">
  <h5 class="mb-1">Kategori tiket</h5>
  <?php $this->load->view('shared/ticket_preview'); ?>
</div>

<div class="m-buybar">
  <div>
    <div class="lbl">Harga mulai</div>
    <div class="amt"><?= $this->fmt->rupiah($event->min_price) ?></div>
  </div>
  <?php if ($any): ?>
    <a href="<?= $this->fmt->url('event/tickets/' . $event->slug) ?>" class="btn btn-sun">Pesan tiket</a>
  <?php else: ?>
    <button class="btn btn-sun" disabled>Tiket habis</button>
  <?php endif; ?>
</div>
