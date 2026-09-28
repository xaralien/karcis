<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars(array('back_url' => $this->fmt->url('account/login'), 'top_title' => 'Daftar akun'));
?>
<div class="m-page">
  <h1>Daftar akun</h1>
  <p class="small text-muted-k">Pembelian sebelumnya dengan email yang sama otomatis masuk ke riwayat.</p>
  <div class="m-card mt-3"><?php $this->load->view('shared/auth_register_form'); ?></div>
</div>
