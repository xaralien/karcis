<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($featured): ?>
<section class="m-hero">
  <div id="mHero" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-touch="true">
    <div class="carousel-indicators">
      <?php foreach ($featured as $i => $e): ?><button type="button" data-bs-target="#mHero" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button><?php endforeach; ?>
    </div>
    <div class="carousel-inner">
      <?php foreach ($featured as $i => $e): ?>
      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
        <img src="<?= html_escape($this->fmt->img($e->banner)) ?>" alt="">
        <div class="m-hero-cap">
          <span class="chip"><?= html_escape($e->event_type) ?></span>
          <h2><?= html_escape($e->title) ?></h2>
          <div class="meta"><i class="bi bi-calendar3 me-1"></i><?= $this->fmt->tgl($e->start_date, FALSE) ?> · <?= html_escape($e->city) ?></div>
          <a href="<?= $this->fmt->url('event/detail/' . $e->slug) ?>" class="btn btn-sun w-100">Beli tiket · <?= $this->fmt->rupiah($e->min_price) ?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="m-sec">
  <div class="m-sec-head"><h2>Kategori</h2></div>
  <div class="h-scroll">
    <?php foreach ($categories as $c): ?>
    <a class="cat-chip" href="<?= $this->fmt->url('explore?kategori=' . $c->slug) ?>"><span class="ico"><i class="bi <?= html_escape($c->icon) ?>"></i></span><?= html_escape($c->name) ?></a>
    <?php endforeach; ?>
  </div>
</section>

<section class="m-sec">
  <div class="m-sec-head"><h2>Event terdekat</h2><a href="<?= $this->fmt->url('explore?sort=terdekat') ?>">Semua</a></div>
  <div class="h-scroll">
    <?php foreach ($upcoming as $e): ?><?php $this->load->view('mobile/partials/event_card', array('e' => $e)); ?><?php endforeach; ?>
  </div>
</section>

<section class="m-sec">
  <div class="m-sec-head"><h2>Katalog event</h2><a href="<?= $this->fmt->url('explore') ?>">Jelajahi</a></div>
  <?php foreach ($catalog as $e): ?><?php $this->load->view('mobile/partials/row_card', array('e' => $e)); ?><?php endforeach; ?>
</section>

<section class="m-why">
  <h2>Kenapa beli di <?= html_escape($app_name) ?>?</h2>
  <div class="m-why-item mt-3"><i class="bi bi-person-x"></i><div><strong>Tanpa daftar akun</strong><p>Isi nama, email, dan HP, lalu bayar.</p></div></div>
  <div class="m-why-item"><i class="bi bi-qr-code"></i><div><strong>Tiket QR ke email</strong><p>Satu QR untuk setiap orang di rombonganmu.</p></div></div>
  <div class="m-why-item"><i class="bi bi-wallet2"></i><div><strong>VA, QRIS, e-wallet</strong><p>Pembayaran aman diproses oleh Duitku.</p></div></div>
  <div class="m-why-item"><i class="bi bi-receipt"></i><div><strong>Biaya jelas dari awal</strong><p>Rp2.000 per tiket + Rp4.000 per transaksi. Tidak ada biaya lain.</p></div></div>
</section>
