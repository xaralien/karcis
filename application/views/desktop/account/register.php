<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:460px">
  <h1 style="font-size:2.2rem">Daftar akun</h1>
  <p class="text-muted-k">Pembelian sebelumnya dengan email yang sama otomatis masuk ke riwayatmu.</p>
  <div class="auth-card mt-4"><?php $this->load->view('shared/auth_register_form'); ?></div>
</div>
