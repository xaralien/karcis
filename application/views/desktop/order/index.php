<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:560px">
  <h1 style="font-size:2.4rem">Cek pesanan</h1>
  <p class="text-muted-k">Masukkan kode pesanan dari email dan alamat email yang dipakai saat membeli.</p>
  <div class="panel mt-4"><?php $this->load->view('shared/order_lookup'); ?></div>
</div>
