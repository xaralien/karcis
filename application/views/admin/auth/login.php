<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
  <title>Masuk Admin | <?= html_escape($app_name) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="<?= $this->fmt->asset('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->asset('assets/css/base.css') ?>" rel="stylesheet">
  <link href="<?= $this->fmt->asset('assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="adm-brand p-0 mb-4" style="color:var(--ink)"><span class="mark" aria-hidden="true"></span><?= html_escape($app_name) ?> <small>Admin</small></div>
    <h1 style="font-size:1.7rem">Masuk ke panel admin</h1>
    <p class="text-muted-k small mb-4">Kelola event, pesanan, dan check-in tiket.</p>
    <?php if ($error): ?><div class="alert alert-danger small"><?= html_escape($error) ?></div><?php endif; ?>
    <form method="post" action="<?= $this->fmt->url('admin/auth/login') ?>"><?= $this->fmt->csrf() ?>
      <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" id="email" name="email" type="email" value="<?= $this->fmt->old('email') ?>" required autofocus autocomplete="username">
        <?= $this->fmt->err('email') ?>
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password">
        <?= $this->fmt->err('password') ?>
      </div>
      <button class="btn btn-primary btn-lg w-100">Masuk</button>
    </form>
  </div>
</div>
</body>
</html>
