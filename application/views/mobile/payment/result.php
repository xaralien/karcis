<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('shared/steps', array('step' => $order->status === 'paid' ? 4 : 3)); ?>
<div class="m-page"><div class="m-card py-4"><?php $this->load->view('shared/payment_status'); ?></div></div>
