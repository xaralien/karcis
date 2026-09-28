<?php defined('BASEPATH') OR exit('No direct script access allowed');
$err = function ($f) { return $this->fmt->err($f); };
$cls = function ($f) { return $this->form_validation->error($f) ? ' is-invalid' : ''; };
$akun = isset($user) ? $user : NULL;
?>
<?php if ( ! $akun): ?>
<div class="email-check mb-3 small">
  Punya akun? <a class="fw-bold" href="<?= $this->fmt->url('account/login') ?>">Masuk</a> supaya tiketnya tersimpan di riwayat.
  Tanpa akun pun pembelian tetap bisa dilanjutkan.
</div>
<?php endif; ?>
<div class="mb-3">
  <label class="form-label" for="buyer_name">Nama lengkap</label>
  <input class="form-control<?= $cls('buyer_name') ?>" id="buyer_name" name="buyer_name" value="<?= $this->fmt->old('buyer_name', $akun ? $akun->name : '') ?>" required minlength="3" autocomplete="name" placeholder="Sesuai kartu identitas">
  <?= $err('buyer_name') ?>
</div>
<div class="mb-3">
  <label class="form-label" for="buyer_email">Email</label>
  <input class="form-control<?= $cls('buyer_email') ?>" id="buyer_email" name="buyer_email" type="email" value="<?= $this->fmt->old('buyer_email', $akun ? $akun->email : '') ?>" required autocomplete="email" inputmode="email" placeholder="nama@email.com" aria-describedby="emailHelp">
  <div class="small mt-1" data-email-hint></div>
  <?= $err('buyer_email') ?>
  <div id="emailHelp" class="form-text">Tiket QR akan dikirim ke email ini.</div>
</div>
<div class="mb-3">
  <label class="form-label" for="buyer_email_confirm">Ketik ulang email</label>
  <input class="form-control<?= $cls('buyer_email_confirm') ?>" id="buyer_email_confirm" name="buyer_email_confirm" type="email" value="<?= $this->fmt->old('buyer_email_confirm', $akun ? $akun->email : '') ?>" required autocomplete="off" inputmode="email" placeholder="Ketik ulang, jangan disalin">
  <div class="invalid-feedback">Email belum sama dengan kolom di atas.</div>
  <?= $err('buyer_email_confirm') ?>
</div>
<div class="mb-3">
  <label class="form-label" for="buyer_phone">Nomor HP</label>
  <input class="form-control<?= $cls('buyer_phone') ?>" id="buyer_phone" name="buyer_phone" type="tel" value="<?= $this->fmt->old('buyer_phone', $akun ? $akun->phone : '') ?>" required pattern="^(\+62|62|0)8[0-9]{7,12}$" autocomplete="tel" inputmode="tel" placeholder="081234567890">
  <?= $err('buyer_phone') ?>
</div>
<div class="form-check mb-1">
  <input class="form-check-input<?= $cls('agree') ?>" type="checkbox" value="1" id="agree" name="agree" required <?= $this->fmt->checked('agree', '1') ?>>
  <label class="form-check-label small" for="agree">Saya memastikan data sudah benar dan menyetujui bahwa tiket tidak dapat dikembalikan.</label>
  <?= $err('agree') ?>
</div>
