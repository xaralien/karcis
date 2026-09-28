<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card-k" style="max-width:860px">
  <div class="hd"><h5>Kategori event</h5><a class="hint" href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Daftar nama ikon</a></div>
  <div class="table-responsive">
    <table class="table table-k">
      <thead><tr><th style="width:60px">Ikon</th><th>Nama</th><th>Kode ikon</th><th class="num">Event</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($categories as $c): $fid = 'fc' . $c->id; ?>
        <tr>
          <td><i class="bi <?= html_escape($c->icon) ?> fs-4" style="color:var(--plum)"></i></td>
          <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="name" value="<?= html_escape($c->name) ?>" required></td>
          <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="icon" value="<?= html_escape($c->icon) ?>"></td>
          <td class="num"><?= (int) $c->total ?></td>
          <td class="text-end text-nowrap">
            <form method="post" action="<?= $this->fmt->url('admin/categories/save/' . $c->id) ?>" id="<?= $fid ?>" class="d-inline"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-light-k" title="Simpan"><i class="bi bi-check-lg"></i></button></form>
            <form method="post" action="<?= $this->fmt->url('admin/categories/delete/' . $c->id) ?>" class="d-inline" data-confirm="Hapus kategori ini?"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-danger-soft" title="Hapus" <?= $c->total ? 'disabled' : '' ?>><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
        <tr style="background:#FBFAFE">
          <td><i class="bi bi-plus-circle fs-4 text-muted-k"></i></td>
          <td><input form="fcNew" class="form-control form-control-sm" name="name" placeholder="Seminar" required></td>
          <td><input form="fcNew" class="form-control form-control-sm" name="icon" placeholder="bi-easel"></td>
          <td></td>
          <td class="text-end"><form method="post" action="<?= $this->fmt->url('admin/categories/save') ?>" id="fcNew"><?= $this->fmt->csrf() ?><button class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-lg me-1"></i>Tambah</button></form></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
