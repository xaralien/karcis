<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars(array('back_url' => $this->fmt->url('event/tickets/' . $cart['event']->slug), 'top_title' => 'Rincian tiket', 'body_class' => 'has-buybar'));
?>
<?php $this->load->view('shared/steps', array('step' => 2)); ?>
<form method="post" action="<?= $this->fmt->url('checkout/process') ?>" data-checkout-form novalidate id="checkoutForm"><?= $this->fmt->csrf() ?>
<div class="m-page">
  <div class="m-card">
    <h5 class="mb-3">Rincian tiket</h5>
    <?php $this->load->view('shared/ticket_detail'); ?>
  </div>
  <div class="m-card">
    <h5 class="mb-1">Data pembeli</h5>
    <p class="small text-muted-k">Tanpa login. Tiket QR dikirim ke email yang kamu isi.</p>
    <?php $this->load->view('shared/buyer_fields'); ?>
  </div>
</div>
</form>
<div class="m-buybar">
  <div><div class="lbl">Total bayar</div><div class="amt"><?= $this->fmt->rupiah($cart['fees']['total']) ?></div></div>
  <button type="submit" form="checkoutForm" class="btn btn-sun">Bayar sekarang</button>
</div>
<?php $this->load->view('shared/confirm_email_modal'); ?>
