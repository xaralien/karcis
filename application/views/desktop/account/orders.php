<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:840px">
  <a class="small fw-semibold" href="<?= $this->fmt->url('account') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali ke akun</a>
  <h1 class="mt-2 mb-1" style="font-size:2.4rem">Riwayat tiket</h1>
  <p class="text-muted-k"><?= $total ?> pesanan atas nama <?= html_escape($user->email) ?>.</p>
  <div class="mt-4"><?php $this->load->view('shared/order_history'); ?></div>
  <nav class="mt-4 d-flex justify-content-center"><?php $this->load->view('shared/pagination', array('pages' => $pages, 'page_base' => $this->fmt->url('account/orders'))); ?></nav>
</div>
