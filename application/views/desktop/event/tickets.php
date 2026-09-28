<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:1040px">
  <?php $this->load->view('shared/steps', array('step' => 1)); ?>

  <div class="d-flex gap-3 align-items-center mt-5 mb-4">
    <img src="<?= html_escape($this->fmt->img($event->thumbnail)) ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:12px">
    <div>
      <a href="<?= $this->fmt->url('event/detail/' . $event->slug) ?>" class="small fw-semibold"><i class="bi bi-arrow-left me-1"></i>Kembali ke detail event</a>
      <h1 class="mb-0" style="font-size:2.2rem"><?= html_escape($event->title) ?></h1>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="panel">
        <h5 class="mb-1">Pilih kategori tiket</h5>
        <p class="small text-muted-k mb-1">Semua kategori bisa digabung dalam satu transaksi, maksimal <?= $this->fmt->max_tickets() ?> tiket.</p>
        <?php $this->load->view('desktop/partials/ticket_box', array('sticky_bar' => TRUE)); ?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="panel" style="position:sticky;top:20px">
        <h5>Jadwal &amp; lokasi</h5>
        <?php foreach ($schedules as $s): ?>
          <div class="small mb-2"><strong><?= html_escape($s->label) ?></strong> · <?= $this->fmt->tgl($s->event_date) ?>, <?= $this->fmt->jam($s->start_time) ?>–<?= $this->fmt->jam($s->end_time) ?> WIB<br><span class="text-muted-k"><?= html_escape($s->venue) ?></span></div>
        <?php endforeach; ?>
        <hr style="border-top:2px dashed var(--line);opacity:1">
        <div class="d-flex justify-content-between align-items-end">
          <div><div class="small text-muted-k"><span data-out="qty">0</span> tiket dipilih</div><div class="display-type" style="font-size:1.8rem" data-out="total">Rp0</div></div>
        </div>
        <button type="submit" form="ticketForm" class="btn btn-sun btn-lg w-100 mt-3" data-submit-ticket disabled>Lihat rincian tiket</button>
        <p class="fee-note text-center mt-2 mb-0">Rincian biaya ditampilkan sebelum kamu membayar.</p>
      </div>
    </div>
  </div>
</div>
