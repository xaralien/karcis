<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<form method="post" action="<?= $this->fmt->url('account/profile') ?>"><?= $this->fmt->csrf() ?>
  <input type="hidden" name="form" value="password">
  <div class="mb-3">
    <label class="form-label" for="current">Password lama</label>
    <input class="form-control" id="current" name="current" type="password" required autocomplete="current-password">
    <?= $this->fmt->err('current') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="password">Password baru</label>
    <input class="form-control" id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
    <?= $this->fmt->err('password') ?>
  </div>
  <div class="mb-4">
    <label class="form-label" for="password_confirm">Ulangi password baru</label>
    <input class="form-control" id="password_confirm" name="password_confirm" type="password" required autocomplete="new-password">
    <?= $this->fmt->err('password_confirm') ?>
  </div>
  <button class="btn btn-outline-ink w-100">Ganti password</button>
</form>
