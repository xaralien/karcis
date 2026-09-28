<?php defined('BASEPATH') or exit('No direct script access allowed');
$nav = isset($active_nav) ? $active_nav : '';
$back = isset($back_url) ? $back_url : NULL;
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#1E1540">
  <title><?= html_escape($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= $this->fmt->base('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->base('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->base('assets/css/base.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->base('assets/css/mobile.css') ?>" rel="stylesheet">
</head>

<body class="<?= ! empty($body_class) ? html_escape($body_class) : '' ?>">
  <header class="m-top">
    <?php if ($back): ?>
      <a class="icon-btn" href="<?= $back ?>" aria-label="Kembali"><i class="bi bi-arrow-left"></i></a>
      <span class="title"><?= html_escape(isset($top_title) ? $top_title : '') ?></span>
    <?php else: ?>
      <a class="k-brand" href="<?= $this->fmt->base() ?>"><span class="mark" aria-hidden="true"></span><?= html_escape($app_name) ?></a>
      <a class="icon-btn ms-auto" href="<?= $this->fmt->url('explore') ?>" aria-label="Cari event"><i class="bi bi-search"></i></a>
    <?php endif; ?>
  </header>

  <?php if ($m = $this->session->flashdata('error')): ?><div class="alert alert-danger rounded-0 mb-0 small"><?= html_escape($m) ?></div><?php endif; ?>
  <?php if ($m = $this->session->flashdata('success')): ?><div class="alert alert-success rounded-0 mb-0 small"><?= html_escape($m) ?></div><?php endif; ?>

  <main><?= $content ?></main>

  <div class="text-center small py-4 text-muted-k">
    <!-- &copy; <?= date('Y') ?> <?= html_escape($app_name) ?> · <a href="<?= $this->fmt->url('home/mode/desktop') ?>">Tampilan desktop</a> -->
  </div>

  <nav class="m-tabbar" aria-label="Navigasi utama">
    <a class="<?= $nav === 'home' ? 'active' : '' ?>" href="<?= $this->fmt->base() ?>"><i class="bi bi-house<?= $nav === 'home' ? '-fill' : '' ?>"></i>Beranda</a>
    <a class="<?= $nav === 'explore' ? 'active' : '' ?>" href="<?= $this->fmt->url('explore') ?>"><i class="bi bi-compass<?= $nav === 'explore' ? '-fill' : '' ?>"></i>Jelajahi</a>
    <a class="<?= $nav === 'order' ? 'active' : '' ?>" href="<?= $this->fmt->url('order') ?>"><i class="bi bi-ticket-perforated<?= $nav === 'order' ? '-fill' : '' ?>"></i>Cek Tiket</a>
    <a class="<?= $nav === 'account' ? 'active' : '' ?>" href="<?= $this->fmt->url($user ? 'account' : 'account/login') ?>"><i class="bi bi-person<?= $nav === 'account' ? '-fill' : '' ?>"></i><?= $user ? 'Akun' : 'Masuk' ?></a>
  </nav>

  <script src="<?= $this->fmt->base('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= $this->fmt->base('assets/js/app.js') ?>"></script>
</body>

</html>