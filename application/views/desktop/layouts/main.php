<?php defined('BASEPATH') OR exit('No direct script access allowed'); $nav = isset($active_nav) ? $active_nav : ''; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= html_escape($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= $this->fmt->asset('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->asset('assets/css/base.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->asset('assets/css/desktop.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="k-nav">
  <div class="container d-flex align-items-center gap-4">
    <a class="k-brand" href="<?= $this->fmt->base() ?>"><span class="mark" aria-hidden="true"></span><?= html_escape($app_name) ?></a>
    <ul class="nav">
      <li><a class="nav-link <?= $nav === 'home' ? 'active' : '' ?>" href="<?= $this->fmt->base() ?>">Beranda</a></li>
      <li><a class="nav-link <?= $nav === 'explore' ? 'active' : '' ?>" href="<?= $this->fmt->url('explore') ?>">Jelajahi Event</a></li>
      <li><a class="nav-link <?= $nav === 'order' ? 'active' : '' ?>" href="<?= $this->fmt->url('order') ?>">Cek Pesanan</a></li>
    </ul>
    <form class="k-search ms-auto" action="<?= $this->fmt->url('explore') ?>" method="get" role="search">
      <i class="bi bi-search" aria-hidden="true"></i>
      <input class="form-control" type="search" name="q" placeholder="Cari konser, festival, kota…" aria-label="Cari event">
    </form>
    <?php if ($user): ?>
    <div class="dropdown">
      <button class="btn btn-link p-0 user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="av"><?= html_escape(strtoupper(mb_substr($user->name, 0, 1))) ?></span>
        <span class="d-none d-xl-inline"><?= html_escape(explode(' ', $user->name)[0]) ?></span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?= $this->fmt->url('account') ?>">Akun saya</a></li>
        <li><a class="dropdown-item" href="<?= $this->fmt->url('account/orders') ?>">Riwayat tiket</a></li>
        <li><a class="dropdown-item" href="<?= $this->fmt->url('account/profile') ?>">Data akun</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="<?= $this->fmt->url('account/logout') ?>">Keluar</a></li>
      </ul>
    </div>
    <?php else: ?>
    <a class="nav-link text-nowrap" href="<?= $this->fmt->url('account/login') ?>">Masuk</a>
    <a class="btn btn-sun btn-sm text-nowrap" href="<?= $this->fmt->url('account/register') ?>">Daftar</a>
    <?php endif; ?>
  </div>
</nav>

<?php if ($this->session->flashdata('error') || $this->session->flashdata('success')): ?>
<div class="container mt-3">
  <?php if ($m = $this->session->flashdata('error')): ?><div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle me-2"></i><?= html_escape($m) ?></div><?php endif; ?>
  <?php if ($m = $this->session->flashdata('success')): ?><div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i><?= html_escape($m) ?></div><?php endif; ?>
</div>
<?php endif; ?>

<main><?= $content ?></main>

<footer class="k-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5">
        <a class="k-brand mb-3" href="<?= $this->fmt->base() ?>"><span class="mark" aria-hidden="true"></span><?= html_escape($app_name) ?></a>
        <p class="mt-3" style="max-width:40ch">Beli tiket event tanpa perlu daftar akun. Tiket QR dikirim ke emailmu begitu pembayaran berhasil.</p>
      </div>
      <div class="col-md-3">
        <h6 class="text-white">Jelajahi</h6>
        <ul class="list-unstyled d-grid gap-2">
          <li><a href="<?= $this->fmt->url('explore') ?>">Semua event</a></li>
          <li><a href="<?= $this->fmt->url('order') ?>">Cek pesanan</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="text-white">Bantuan</h6>
        <p class="mb-2"><?= html_escape($this->config->item('support_email')) ?></p>
        <p class="mb-0">Pembayaran diproses oleh Duitku.</p>
      </div>
    </div>
    <hr class="border-secondary my-4">
    <div class="d-flex justify-content-between">
      <span>&copy; <?= date('Y') ?> <?= html_escape($app_name) ?></span>
      <a href="<?= $this->fmt->url('home/mode/mobile') ?>"><i class="bi bi-phone me-1"></i>Tampilan mobile</a>
    </div>
  </div>
</footer>

<script src="<?= $this->fmt->asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= $this->fmt->asset('assets/js/app.js') ?>"></script>
</body>
</html>
