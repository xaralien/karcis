<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php if ($error): ?><div class="alert alert-danger small"><?= html_escape($error) ?></div><?php endif; ?>
<form method="post" action="<?= $this->fmt->url('account/login') ?>"><?= $this->fmt->csrf() ?>
  <div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input class="form-control" id="email" name="email" type="email" value="<?= $this->fmt->old('email') ?>" required autofocus autocomplete="email">
    <?= $this->fmt->err('email') ?>
  </div>
  <div class="mb-4">
    <label class="form-label" for="password">Password</label>
    <input class="form-control" id="password" name="password" type="password" required autocomplete="current-password">
    <?= $this->fmt->err('password') ?>
  </div>
  <button class="btn btn-primary btn-lg w-100">Masuk</button>
</form>
<p class="text-center mt-3 mb-0">Belum punya akun? <a class="fw-bold" href="<?= $this->fmt->url('account/register') ?>">Daftar</a></p>
<hr class="my-4" style="border-top:2px dashed var(--line);opacity:1">
<p class="small text-muted-k text-center mb-0">
  Beli tanpa akun? Tiketmu tetap bisa dibuka lewat
  <a href="<?= $this->fmt->url('order') ?>">cek pesanan</a> memakai kode pesanan dan email.
</p>
