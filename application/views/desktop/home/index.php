<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- Carousel event unggulan -->
<section class="hero">
  <div class="container pt-3">
    <?php if ($featured): ?>
    <div id="heroCarousel" class="carousel slide hero-shell" data-bs-ride="carousel" data-bs-interval="6000">
      <div class="carousel-inner">
        <?php foreach ($featured as $i => $e): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <a class="hero-card" href="<?= $this->fmt->url('event/detail/' . $e->slug) ?>">
            <span class="banner-frame"><img src="<?= html_escape($this->fmt->img($e->banner)) ?>" alt="Banner <?= html_escape($e->title) ?>"></span>
            <span class="hero-card-bd">
              <span class="info">
                <span class="chip"><?= html_escape($e->event_type) ?></span>
                <span class="t"><?= html_escape($e->title) ?></span>
                <span class="meta">
                  <span><i class="bi bi-calendar3 me-2"></i><?= $this->fmt->tgl($e->start_date, FALSE) ?><?= $e->total_days > 1 ? ' (' . (int) $e->total_days . ' hari)' : '' ?></span>
                  <span><i class="bi bi-geo-alt me-2"></i><?= html_escape($e->venue) ?>, <?= html_escape($e->city) ?></span>
                </span>
              </span>
              <span class="btn btn-sun btn-lg text-nowrap">Beli tiket · <?= $this->fmt->rupiah($e->min_price) ?></span>
            </span>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($featured) > 1): ?>
      <div class="hero-nav">
        <button class="hero-arrow" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Sebelumnya"><i class="bi bi-chevron-left"></i></button>
        <div class="carousel-indicators position-static m-0">
          <?php foreach ($featured as $i => $e): ?><button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button><?php endforeach; ?>
        </div>
        <button class="hero-arrow" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Berikutnya"><i class="bi bi-chevron-right"></i></button>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Event terkini -->
<section class="sec">
  <div class="container">
    <div class="sec-head">
      <div>
        <h2>Event terdekat</h2>
        <p>Acara yang akan berlangsung dalam waktu dekat. Amankan kursimu sebelum kehabisan.</p>
      </div>
      <a class="sec-link" href="<?= $this->fmt->url('explore?sort=terdekat') ?>">Lihat semua</a>
    </div>
    <div class="row g-4">
      <?php foreach (array_slice($upcoming, 0, 4) as $e): ?>
        <div class="col-lg-3 col-md-6"><?php $this->load->view('desktop/partials/event_card', array('e' => $e)); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Kategori -->
<section class="sec">
  <div class="container">
    <div class="sec-head"><div><h2>Cari berdasarkan kategori</h2></div></div>
    <div class="row g-3">
      <?php foreach ($categories as $c): ?>
      <div class="col-lg-2 col-md-4 col-6">
        <a class="cat-tile flex-column align-items-start h-100" href="<?= $this->fmt->url('explore?kategori=' . $c->slug) ?>">
          <span class="ico"><i class="bi <?= html_escape($c->icon) ?>"></i></span>
          <div><strong><?= html_escape($c->name) ?></strong><span><?= (int) $c->total ?> event</span></div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Katalog -->
<section class="sec">
  <div class="container">
    <div class="sec-head">
      <div>
        <h2>Katalog event</h2>
        <p>Semua tiket berlaku untuk satu orang dan dikirim sebagai kode QR.</p>
      </div>
      <a class="btn btn-outline-ink" href="<?= $this->fmt->url('explore') ?>">Jelajahi katalog</a>
    </div>
    <div class="row g-4">
      <?php foreach ($catalog as $e): ?>
        <div class="col-lg-3 col-md-6"><?php $this->load->view('desktop/partials/event_card', array('e' => $e)); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Kenapa Karcis -->
<section class="why">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-lg-5">
        <h2>Kenapa beli tiket di <?= html_escape($app_name) ?>?</h2>
        <p class="text-white-50 mt-3" style="max-width:42ch">Biaya ditampilkan sejak awal, jadi total yang kamu lihat adalah total yang kamu bayar.</p>
        <div class="fee-demo mt-4">
          <div class="fw-bold mb-2">Contoh 4 tiket @ <?= $this->fmt->rupiah(100000) ?></div>
          <?php $demo = $this->fmt->fees(array(array('price' => 100000, 'qty' => 4))); ?>
          <table class="w-100">
            <tr><td>Harga tiket</td><td><?= $this->fmt->rupiah($demo['subtotal']) ?></td></tr>
            <tr><td>Biaya layanan (4 × <?= $this->fmt->rupiah($this->config->item('service_fee_per_ticket')) ?>)</td><td><?= $this->fmt->rupiah($demo['service_fee']) ?></td></tr>
            <tr><td>Biaya transaksi</td><td><?= $this->fmt->rupiah($demo['transaction_fee']) ?></td></tr>
            <tr><td class="pt-2 text-white fw-bold">Total bayar</td><td class="pt-2" style="color:var(--sun)"><?= $this->fmt->rupiah($demo['total']) ?></td></tr>
          </table>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="row g-4">
          <div class="col-md-6 why-item"><i class="bi bi-person-x"></i><h3>Tanpa daftar akun</h3><p>Cukup isi nama, email, dan nomor HP. Pesanan bisa dicek kapan saja dengan kode pesanan.</p></div>
          <div class="col-md-6 why-item"><i class="bi bi-qr-code"></i><h3>Tiket QR langsung ke email</h3><p>Setiap tiket punya QR sendiri, jadi rombonganmu bisa masuk tanpa harus menunggu satu sama lain.</p></div>
          <div class="col-md-6 why-item"><i class="bi bi-wallet2"></i><h3>Bayar pakai metode favorit</h3><p>Virtual account, QRIS, dan e-wallet lewat Duitku. Status pembayaran diperbarui otomatis.</p></div>
          <div class="col-md-6 why-item"><i class="bi bi-shield-check"></i><h3>Kuota dijaga sistem</h3><p>Stok tiket dicek setiap kali pesanan dibuat, sehingga tiket yang kamu bayar pasti tersedia.</p></div>
        </div>
      </div>
    </div>
  </div>
</section>
