<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<form method="post" action="<?= $this->fmt->url('account/register') ?>"><?= $this->fmt->csrf() ?>
  <div class="mb-3">
    <label class="form-label" for="name">Nama lengkap</label>
    <input class="form-control" id="name" name="name" value="<?= $this->fmt->old('name') ?>" required minlength="3" autocomplete="name">
    <?= $this->fmt->err('name') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input class="form-control" id="email" name="email" type="email" value="<?= $this->fmt->old('email') ?>" required autocomplete="email">
    <div class="form-text">Dipakai untuk masuk dan menerima tiket QR.</div>
    <?= $this->fmt->err('email') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="phone">Nomor HP</label>
    <input class="form-control" id="phone" name="phone" type="tel" value="<?= $this->fmt->old('phone') ?>" required autocomplete="tel" placeholder="081234567890">
    <?= $this->fmt->err('phone') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <input class="form-control" id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
    <div class="form-text">Minimal 8 karakter.</div>
    <?= $this->fmt->err('password') ?>
  </div>
  <div class="mb-4">
    <label class="form-label" for="password_confirm">Ulangi password</label>
    <input class="form-control" id="password_confirm" name="password_confirm" type="password" required autocomplete="new-password">
    <?= $this->fmt->err('password_confirm') ?>
  </div>
  <button class="btn btn-primary btn-lg w-100">Buat akun</button>
</form>
<p class="text-center mt-3 mb-0">Sudah punya akun? <a class="fw-bold" href="<?= $this->fmt->url('account/login') ?>">Masuk</a></p>
