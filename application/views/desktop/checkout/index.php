<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:1040px">
  <?php $this->load->view('shared/steps', array('step' => 2)); ?>
  <h1 class="mt-5 mb-4" style="font-size:2.4rem">Rincian tiket</h1>
  <form method="post" action="<?= $this->fmt->url('checkout/process') ?>" data-checkout-form novalidate id="checkoutForm"><?= $this->fmt->csrf() ?>
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="panel">
        <?php $this->load->view('shared/ticket_detail'); ?>
      </div>
      <div class="panel">
        <h5 class="mb-1">Data pembeli</h5>
        <p class="small text-muted-k mb-4">Tidak perlu membuat akun. Pastikan email aktif karena tiket QR dikirim ke sana.</p>
        <?php $this->load->view('shared/buyer_fields'); ?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="panel" style="position:sticky;top:20px">
        <h5>Pembayaran</h5>
        <table class="fee-table">
          <tr><td><?= $cart['fees']['qty'] ?> tiket</td><td><?= $this->fmt->rupiah($cart['fees']['subtotal']) ?></td></tr>
          <tr><td>Biaya layanan &amp; transaksi</td><td><?= $this->fmt->rupiah($cart['fees']['service_fee'] + $cart['fees']['transaction_fee']) ?></td></tr>
          <tr class="total"><td>Total bayar</td><td><?= $this->fmt->rupiah($cart['fees']['total']) ?></td></tr>
        </table>
        <button type="submit" class="btn btn-sun btn-lg w-100 mt-4">Bayar <?= $this->fmt->rupiah($cart['fees']['total']) ?></button>
        <p class="fee-note text-center mt-2 mb-0"><i class="bi bi-lock me-1"></i>Pilih VA, QRIS, atau e-wallet di halaman Duitku.</p>
      </div>
    </div>
  </div>
  </form>
</div>
<?php $this->load->view('shared/confirm_email_modal'); ?>
