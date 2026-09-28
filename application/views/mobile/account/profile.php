<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars(array('back_url' => $this->fmt->url('account'), 'top_title' => 'Data akun'));
?>
<div class="m-page">
  <div class="m-card"><h5 class="mb-3">Data diri</h5><?php $this->load->view('shared/account_profile_form'); ?></div>
  <div class="m-card"><h5 class="mb-3">Ganti password</h5><?php $this->load->view('shared/account_password_form'); ?></div>
  <a class="btn btn-outline-ink w-100 mt-3" href="<?= $this->fmt->url('account/logout') ?>">Keluar dari akun</a>
</div>
