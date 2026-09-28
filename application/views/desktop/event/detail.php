<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('shared/preview_notice'); ?>
<section class="ev-hero">
  <div class="container">
    <div class="breadcrumb-k mb-3"><a href="<?= $this->fmt->url('') ?>">Beranda</a> / <a href="<?= $this->fmt->url('explore?kategori=' . $event->category_slug) ?>"><?= html_escape($event->category_name) ?></a></div>
    <span class="banner-frame ev-banner"><img src="<?= html_escape($this->fmt->img($event->banner)) ?>" alt="Banner <?= html_escape($event->title) ?>"></span>
    <h1><?= html_escape($event->title) ?></h1>
    <div class="hero-meta d-flex flex-wrap gap-4 fw-semibold">
      <span><i class="bi bi-calendar3 me-2"></i><?= $this->fmt->tgl($event->start_date, FALSE) ?><?= $event->end_date !== $event->start_date ? ' – ' . $this->fmt->tgl($event->end_date, FALSE) : '' ?></span>
      <span><i class="bi bi-geo-alt me-2"></i><?= html_escape($event->venue) ?>, <?= html_escape($event->city) ?></span>
      <span><i class="bi bi-person-badge me-2"></i><?= html_escape($event->organizer) ?></span>
    </div>
  </div>
</section>

<div class="container py-5">
  <div class="row g-4">
    <div class="col-lg-8">
      <ul class="nav ev-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-desc" type="button" role="tab">Deskripsi</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fac" type="button" role="tab">Fasilitas</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-guest" type="button" role="tab">Bintang Tamu</button></li>
      </ul>

      <div class="tab-content">
        <!-- Deskripsi: deskripsi, tipe event, galeri -->
        <div class="tab-pane fade show active" id="tab-desc" role="tabpanel">
          <div class="panel">
            <h5>Tentang event</h5>
            <div class="prose"><?php foreach (preg_split("/\n\s*\n/", $event->description) as $p): ?><p><?= nl2br(html_escape($p)) ?></p><?php endforeach; ?></div>
          </div>
          <div class="panel">
            <h5>Tipe event</h5>
            <dl class="info-kv">
              <dt>Tipe</dt><dd><?= html_escape($event->event_type) ?></dd>
              <dt>Kategori</dt><dd><?= html_escape($event->category_name) ?></dd>
              <dt>Penyelenggara</dt><dd><?= html_escape($event->organizer) ?></dd>
              <dt>Durasi</dt><dd><?= (int) $event->total_days ?> hari</dd>
            </dl>
          </div>
          <?php if ($gallery): ?>
          <div class="panel">
            <h5>Galeri</h5>
            <div class="gallery-grid">
              <?php foreach ($gallery as $g): ?>
                <a href="<?= html_escape($this->fmt->img($g->image)) ?>" target="_blank" rel="noopener"><img src="<?= html_escape($this->fmt->img($g->image)) ?>" alt="<?= html_escape($g->caption) ?>" loading="lazy"></a>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="tab-fac" role="tabpanel">
          <div class="panel">
            <h5>Fasilitas di lokasi</h5>
            <?php if ($facilities): ?>
            <div class="row g-3">
              <?php foreach ($facilities as $f): ?><div class="col-md-6"><div class="facility"><i class="bi <?= html_escape($f->icon) ?>"></i><?= html_escape($f->name) ?></div></div><?php endforeach; ?>
            </div>
            <?php else: ?><p class="text-muted-k mb-0">Penyelenggara belum menambahkan informasi fasilitas.</p><?php endif; ?>
          </div>
        </div>

        <div class="tab-pane fade" id="tab-guest" role="tabpanel">
          <div class="panel">
            <h5>Bintang tamu</h5>
            <?php if ($guests): ?>
            <div class="row g-4">
              <?php foreach ($guests as $g): ?>
              <div class="col-md-3 col-6 guest text-center">
                <img src="<?= html_escape($this->fmt->img($g->photo)) ?>" alt="<?= html_escape($g->name) ?>" loading="lazy">
                <div class="guest-name"><?= html_escape($g->name) ?></div>
                <div class="guest-role"><?= html_escape($g->role) ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php else: ?><p class="text-muted-k mb-0">Event ini tidak memiliki bintang tamu.</p><?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Pembelian tiket -->
    <div class="col-lg-4">
      <div class="buy-box" id="jadwal">
        <div class="panel">
          <h5>Jadwal &amp; lokasi</h5>
          <?php foreach ($schedules as $s): $d = $this->fmt->tgl_pendek($s->event_date); ?>
          <div class="sched">
            <div class="sched-day"><div class="d"><?= $d['d'] ?></div><div class="m"><?= $d['m'] ?></div></div>
            <div>
              <div class="fw-bold"><?= html_escape($s->label) ?> <span class="fw-normal text-muted-k">· <?= $this->fmt->tgl($s->event_date) ?></span></div>
              <div class="small"><i class="bi bi-clock me-1"></i><?= $this->fmt->jam($s->start_time) ?>–<?= $this->fmt->jam($s->end_time) ?> WIB</div>
              <div class="small text-muted-k"><i class="bi bi-geo-alt me-1"></i><?= html_escape($s->venue) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
          <div class="small mt-2"><?= html_escape($event->address) ?>, <?= html_escape($event->city) ?>
            <?php if ($event->maps_url): ?> <a href="<?= html_escape($event->maps_url) ?>" target="_blank" rel="noopener">Buka peta</a><?php endif; ?></div>
        </div>
        <div class="panel">
          <?php $any = FALSE; foreach ($ticket_types as $t) if ((int) $t->available > 0) $any = TRUE; ?>
          <div class="small text-muted-k">Harga mulai</div>
          <div class="display-type" style="font-size:2rem;line-height:1.1"><?= $this->fmt->rupiah($event->min_price) ?></div>
          <div class="mt-3"><?php $this->load->view('shared/ticket_preview'); ?></div>
          <?php if ( ! empty($preview)): ?>
            <button class="btn btn-sun btn-lg w-100 mt-3" disabled>Belum dijual (<?= $event->status === 'draft' ? 'draft' : 'selesai' ?>)</button>
          <?php elseif ($any): ?>
            <a href="<?= $this->fmt->url('event/tickets/' . $event->slug) ?>" class="btn btn-sun btn-lg w-100 mt-3">Pesan tiket</a>
            <p class="fee-note text-center mt-2 mb-0">Maksimal <?= $this->fmt->max_tickets() ?> tiket per transaksi, tanpa login.</p>
          <?php else: ?>
            <button class="btn btn-sun btn-lg w-100 mt-3" disabled>Tiket habis</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
