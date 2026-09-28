<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars(array('back_url' => $this->fmt->url('event/detail/' . $event->slug), 'top_title' => 'Pilih tiket', 'body_class' => 'has-buybar'));
?>
<?php $this->load->view('shared/steps', array('step' => 1)); ?>
<div class="m-block mt-0">
  <div class="d-flex gap-3 align-items-center">
    <img src="<?= html_escape($this->fmt->img($event->thumbnail)) ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:10px">
    <div>
      <div class="fw-bold lh-sm"><?= html_escape($event->title) ?></div>
      <div class="small text-muted-k"><?= $this->fmt->tgl($event->start_date, FALSE) ?> · <?= html_escape($event->city) ?></div>
    </div>
  </div>
</div>
<div class="m-block">
  <h5 class="mb-1">Pilih kategori tiket</h5>
  <p class="small text-muted-k mb-0">Maksimal <?= $this->fmt->max_tickets() ?> tiket per transaksi.</p>
  <?php $this->load->view('mobile/partials/ticket_box', array('sticky_bar' => TRUE)); ?>
</div>
<div class="m-buybar">
  <div>
    <div class="lbl"><span data-out="qty">0</span> tiket · total</div>
    <div class="amt" data-out="total">Rp0</div>
  </div>
  <button type="submit" form="ticketForm" class="btn btn-sun" data-submit-ticket disabled>Lihat rincian</button>
</div>
