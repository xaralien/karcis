<?php defined('BASEPATH') OR exit('No direct script access allowed'); $nav = isset($nav) ? $nav : ''; $is_admin = $admin->role === 'admin'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= html_escape($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= $this->fmt->base('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->base('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->base('assets/css/base.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->base('assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="adm">
  <aside class="adm-side" id="admSide">
    <a class="adm-brand" href="<?= $this->fmt->url($is_admin ? 'admin' : 'admin/checkin') ?>"><span class="mark" aria-hidden="true"></span><?= html_escape($app_name) ?> <small>Admin</small></a>
    <nav class="adm-nav">
      <?php if ($is_admin): ?>
      <a class="<?= $nav === 'dashboard' ? 'active' : '' ?>" href="<?= $this->fmt->url('admin') ?>"><i class="bi bi-grid-1x2"></i>Dashboard</a>
      <div class="grp">Konten</div>
      <a class="<?= $nav === 'events' ? 'active' : '' ?>" href="<?= $this->fmt->url('admin/events') ?>"><i class="bi bi-calendar-event"></i>Event</a>
      <a class="<?= $nav === 'categories' ? 'active' : '' ?>" href="<?= $this->fmt->url('admin/categories') ?>"><i class="bi bi-tags"></i>Kategori</a>
      <div class="grp">Penjualan</div>
      <a class="<?= $nav === 'orders' ? 'active' : '' ?>" href="<?= $this->fmt->url('admin/orders') ?>"><i class="bi bi-receipt"></i>Pesanan</a>
      <a class="<?= $nav === 'reports' ? 'active' : '' ?>" href="<?= $this->fmt->url('admin/reports') ?>"><i class="bi bi-file-earmark-bar-graph"></i>Laporan &amp; ekspor</a>
      <?php endif; ?>
      <a class="<?= $nav === 'checkin' ? 'active' : '' ?>" href="<?= $this->fmt->url('admin/checkin') ?>"><i class="bi bi-qr-code-scan"></i>Check-in</a>
      <div class="grp">Akun</div>
      <?php if ($is_admin): ?>
      <a class="<?= $nav === 'users' ? 'active' : '' ?>" href="<?= $this->fmt->url('admin/users') ?>"><i class="bi bi-people"></i>Akun admin</a>
      <?php endif; ?>
      <a class="<?= $nav === 'account' ? 'active' : '' ?>" href="<?= $this->fmt->url('admin/account') ?>"><i class="bi bi-key"></i>Ganti password</a>
      <a href="<?= $this->fmt->base() ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i>Lihat website</a>
    </nav>
    <div class="me">
      <strong><?= html_escape($admin->name) ?></strong>
      <span><?= $is_admin ? 'Admin' : 'Petugas check-in' ?></span>
      <a class="d-block mt-2 text-white-50" href="<?= $this->fmt->url('admin/auth/logout') ?>"><i class="bi bi-box-arrow-left me-1"></i>Keluar</a>
    </div>
  </aside>
  <div class="adm-backdrop" id="admBackdrop"></div>

  <div class="adm-main">
    <header class="adm-top">
      <button class="btn btn-icon btn-light-k d-lg-none" type="button" id="admToggle" aria-label="Buka menu"><i class="bi bi-list"></i></button>
      <h1><?= html_escape($page_title) ?></h1>
      <div class="ms-auto d-flex gap-2"><?= isset($actions) ? $actions : '' ?></div>
    </header>
    <div class="adm-body">
      <?php if ($m = $this->session->flashdata('error')): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= html_escape($m) ?></div><?php endif; ?>
      <?php if ($m = $this->session->flashdata('success')): ?><div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= html_escape($m) ?></div><?php endif; ?>
      <?= $content ?>
    </div>
  </div>
</div>

<script src="<?= $this->fmt->base('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script>
(function () {
  var side = document.getElementById('admSide'), bd = document.getElementById('admBackdrop'), tg = document.getElementById('admToggle');
  var toggle = function (open) { side.classList.toggle('open', open); bd.classList.toggle('open', open); };
  if (tg) tg.addEventListener('click', function () { toggle(!side.classList.contains('open')); });
  bd.addEventListener('click', function () { toggle(false); });
  document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  });
  // Pratinjau ikon Bootstrap Icons + peringatan bila nama ikonnya tidak ada
  var cekIkon = function (input) {
    var nama = input.value.trim();
    var prev = input.closest('tr') ? input.closest('tr').querySelector('[data-icon-preview]') : null;
    var test = document.createElement('i');
    test.className = 'bi ' + nama;
    test.style.cssText = 'position:absolute;visibility:hidden';
    document.body.appendChild(test);
    var isi = getComputedStyle(test, '::before').content;
    document.body.removeChild(test);
    var ada = nama !== '' && isi && isi !== 'none' && isi !== 'normal' && isi !== '""';

    if (prev) prev.className = 'bi ' + (ada ? nama : 'bi-question-circle') + ' fs-4';
    if (prev) prev.style.color = ada ? 'var(--plum)' : 'var(--danger)';
    input.classList.toggle('is-invalid', !ada && nama !== '');
    var pesan = input.parentNode.querySelector('.icon-warn');
    if (!ada && nama !== '') {
      if (!pesan) {
        pesan = document.createElement('div');
        pesan.className = 'icon-warn invalid-feedback d-block';
        pesan.textContent = 'Nama ikon tidak ditemukan. Lihat daftar di icons.getbootstrap.com.';
        input.parentNode.appendChild(pesan);
      }
    } else if (pesan) { pesan.remove(); }
  };
  document.querySelectorAll('input[name=icon]').forEach(function (inp) {
    inp.addEventListener('input', function () { cekIkon(inp); });
    if (inp.value.trim()) cekIkon(inp);
  });

  document.querySelectorAll('input[type=file][data-preview]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var img = document.querySelector(inp.dataset.preview);
      if (img && inp.files[0]) img.src = URL.createObjectURL(inp.files[0]);
    });
  });
})();
</script>
<?= isset($scripts) ? $scripts : '' ?>
</body>
</html>
