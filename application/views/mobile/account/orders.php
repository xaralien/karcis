<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars(array('back_url' => $this->fmt->url('account'), 'top_title' => 'Riwayat tiket'));
?>
<div class="m-page">
  <p class="small text-muted-k"><?= $total ?> pesanan atas nama <?= html_escape($user->email) ?>.</p>
  <?php $this->load->view('shared/order_history'); ?>
  <nav class="mt-4 d-flex justify-content-center"><?php $this->load->view('shared/pagination', array('pages' => $pages, 'page_base' => $this->fmt->url('account/orders'))); ?></nav>
</div>
