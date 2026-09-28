<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars(array('back_url' => $this->fmt->url(''), 'top_title' => 'Masuk'));
?>
<div class="m-page">
  <h1>Masuk</h1>
  <p class="small text-muted-k">Masuk untuk melihat riwayat tiket kapan saja.</p>
  <div class="m-card mt-3"><?php $this->load->view('shared/auth_login_form'); ?></div>
</div>
