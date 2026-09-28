<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:760px">
  <?php $this->load->view('shared/steps', array('step' => $order->status === 'paid' ? 4 : 3)); ?>
  <div class="mx-auto mt-4" style="max-width:560px">
  <div class="panel p-5"><?php $this->load->view('shared/payment_status'); ?></div>
  </div>
</div>
