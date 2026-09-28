<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal fade" id="confirmEmailModal" tabindex="-1" aria-labelledby="confirmEmailTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:var(--r-lg)">
      <div class="modal-body p-4">
        <h5 id="confirmEmailTitle" class="mb-2">Email ini sudah benar?</h5>
        <p class="text-muted-k small mb-3">Tiket QR hanya dikirim ke alamat berikut. Email yang salah membuat tiket tidak sampai.</p>
        <div class="email-check mb-3"><div class="email-big" data-email-preview></div></div>
        <div class="d-grid gap-2">
          <button type="button" class="btn btn-sun btn-lg" data-confirm-pay>Ya, lanjut bayar</button>
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">Ubah email</button>
        </div>
      </div>
    </div>
  </div>
</div>
