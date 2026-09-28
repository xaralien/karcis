<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:840px">
  <a class="small fw-semibold" href="<?= $this->fmt->url('account') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali ke akun</a>
  <h1 class="mt-2 mb-4" style="font-size:2.4rem">Data akun</h1>
  <div class="row g-4">
    <div class="col-md-6"><div class="panel h-100"><h5 class="mb-3">Data diri</h5><?php $this->load->view('shared/account_profile_form'); ?></div></div>
    <div class="col-md-6"><div class="panel h-100"><h5 class="mb-3">Ganti password</h5><?php $this->load->view('shared/account_password_form'); ?></div></div>
  </div>
  <div class="panel mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div><strong>Keluar dari akun</strong><div class="small text-muted-k">Tiket tetap tersimpan dan bisa dibuka lagi setelah masuk.</div></div>
    <a class="btn btn-outline-ink" href="<?= $this->fmt->url('account/logout') ?>">Keluar</a>
  </div>
</div>
