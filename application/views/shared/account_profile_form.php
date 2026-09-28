<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<form method="post" action="<?= $this->fmt->url('account/profile') ?>"><?= $this->fmt->csrf() ?>
  <input type="hidden" name="form" value="profil">
  <div class="mb-3">
    <label class="form-label" for="name">Nama lengkap</label>
    <input class="form-control" id="name" name="name" value="<?= $this->fmt->old('name', $user->name) ?>" required minlength="3">
    <?= $this->fmt->err('name') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input class="form-control" id="email" value="<?= html_escape($user->email) ?>" disabled>
    <div class="form-text">Email tidak bisa diubah karena dipakai mencocokkan riwayat tiket.</div>
  </div>
  <div class="mb-4">
    <label class="form-label" for="phone">Nomor HP</label>
    <input class="form-control" id="phone" name="phone" type="tel" value="<?= $this->fmt->old('phone', $user->phone) ?>" required>
    <?= $this->fmt->err('phone') ?>
  </div>
  <button class="btn btn-primary w-100">Simpan perubahan</button>
</form>
