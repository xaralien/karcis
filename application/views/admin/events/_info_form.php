<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* $event, $categories, $field_errors */
$fe = isset($field_errors) ? $field_errors : array();
$err = function ($f) use ($fe) { return $this->fmt->err($f, $fe); };
$v   = function ($f) use ($event) { return $this->fmt->old($f, $event->$f); };
$action = $event->id ? 'admin/events/edit/' . $event->id : 'admin/events/create';
?>
<form method="post" action="<?= $this->fmt->url($action) ?>" enctype="multipart/form-data"><?= $this->fmt->csrf() ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-k">
      <div class="hd"><h5>Informasi event</h5></div>
      <div class="bd">
        <div class="mb-3">
          <label class="form-label" for="title">Judul event</label>
          <input class="form-control" id="title" name="title" value="<?= $v('title') ?>" required maxlength="160">
          <?= $err('title') ?>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="slug">Slug URL</label>
            <div class="input-group"><span class="input-group-text small">/event/</span><input class="form-control" id="slug" name="slug" value="<?= $v('slug') ?>" placeholder="otomatis dari judul" pattern="[a-z0-9_-]*"></div>
            <div class="hint">Huruf kecil, angka, dan tanda hubung. Kosongkan untuk dibuat otomatis.</div>
            <?= $err('slug') ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="category_id">Kategori</label>
            <select class="form-select" id="category_id" name="category_id" required>
              <option value="">Pilih kategori</option>
              <?php foreach ($categories as $c): ?><option value="<?= $c->id ?>" <?= (string) $v('category_id') === (string) $c->id ? 'selected' : '' ?>><?= html_escape($c->name) ?></option><?php endforeach; ?>
            </select>
            <?= $err('category_id') ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="event_type">Tipe event</label>
            <input class="form-control" id="event_type" name="event_type" value="<?= $v('event_type') ?>" required placeholder="Konser Musik, Festival Budaya, Workshop…" list="typeList">
            <datalist id="typeList"><option>Konser Musik</option><option>Festival</option><option>Stand Up Comedy</option><option>Workshop</option><option>Seminar</option><option>Fun Run</option><option>Pameran Seni</option></datalist>
            <?= $err('event_type') ?>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="organizer">Penyelenggara</label>
            <input class="form-control" id="organizer" name="organizer" value="<?= $v('organizer') ?>" required>
            <?= $err('organizer') ?>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label" for="short_desc">Ringkasan singkat</label>
          <input class="form-control" id="short_desc" name="short_desc" value="<?= $v('short_desc') ?>" required maxlength="255">
          <div class="hint">Satu kalimat, tampil di kartu event.</div>
          <?= $err('short_desc') ?>
        </div>
        <div class="mt-3">
          <label class="form-label" for="description">Deskripsi lengkap</label>
          <textarea class="form-control" id="description" name="description" rows="8" required><?= $v('description') ?></textarea>
          <div class="hint">Pisahkan paragraf dengan baris kosong.</div>
          <?= $err('description') ?>
        </div>
      </div>
    </div>

    <div class="card-k mt-3">
      <div class="hd"><h5>Lokasi</h5></div>
      <div class="bd row g-3">
        <div class="col-md-6"><label class="form-label" for="venue">Nama venue</label><input class="form-control" id="venue" name="venue" value="<?= $v('venue') ?>" required><?= $err('venue') ?></div>
        <div class="col-md-6"><label class="form-label" for="city">Kota</label><input class="form-control" id="city" name="city" value="<?= $v('city') ?>" required><?= $err('city') ?></div>
        <div class="col-12"><label class="form-label" for="address">Alamat</label><input class="form-control" id="address" name="address" value="<?= $v('address') ?>" required><?= $err('address') ?></div>
        <div class="col-12"><label class="form-label" for="maps_url">Link Google Maps <span class="text-muted-k fw-normal">(opsional)</span></label><input class="form-control" id="maps_url" name="maps_url" type="url" value="<?= $v('maps_url') ?>" placeholder="https://maps.google.com/…"><?= $err('maps_url') ?></div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card-k">
      <div class="hd"><h5>Publikasi</h5></div>
      <div class="bd">
        <label class="form-label" for="status">Status</label>
        <select class="form-select" id="status" name="status">
          <?php foreach (array('draft' => 'Draft (tersembunyi)', 'published' => 'Tayang', 'ended' => 'Selesai') as $k => $lbl): ?>
            <option value="<?= $k ?>" <?= $v('status') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ( ! $event->id): ?><div class="hint">Event baru disimpan sebagai draft. Tayangkan setelah jadwal dan kategori tiket diisi.</div><?php endif; ?>
        <div class="mt-3">
          <label class="form-label" for="start_date">Tanggal mulai</label>
          <input class="form-control" id="start_date" name="start_date" type="date" value="<?= $v('start_date') ?>" required>
          <div class="hint">Otomatis mengikuti jadwal paling awal.</div>
        </div>
        <div class="form-check form-switch mt-3">
          <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" <?= $this->fmt->checked('is_featured', '1', (bool) $event->is_featured) ?>>
          <label class="form-check-label" for="is_featured">Tampilkan di carousel beranda</label>
        </div>
        <button class="btn btn-primary w-100 mt-4"><?= $event->id ? 'Simpan perubahan' : 'Simpan & lanjut isi jadwal' ?></button>
      </div>
    </div>

    <div class="card-k mt-3">
      <div class="hd"><h5>Gambar</h5></div>
      <div class="bd">
        <label class="form-label" for="banner">Banner <span class="hint">(1600×700 px, maks 3MB)</span></label>
        <div class="hint mb-2">Selalu tampil utuh tanpa dipotong di carousel dan halaman detail. Gambar dengan rasio lain tetap utuh, hanya diberi latar gelap di sisinya.</div>
        <img id="pvBanner" class="img-preview mb-2" style="aspect-ratio:16/7;object-fit:contain;background:#120C29" src="<?= html_escape($this->fmt->img($event->banner)) ?>" alt="">
        <input class="form-control form-control-sm" type="file" id="banner" name="banner" accept="image/jpeg,image/png,image/webp" data-preview="#pvBanner" <?= $event->id ? '' : 'required' ?>>
        <?= $err('banner') ?>
        <label class="form-label mt-3" for="thumbnail">Thumbnail <span class="hint">(800×600)</span></label>
        <img id="pvThumb" class="img-preview mb-2" style="aspect-ratio:4/3" src="<?= html_escape($this->fmt->img($event->thumbnail)) ?>" alt="">
        <input class="form-control form-control-sm" type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp" data-preview="#pvThumb" <?= $event->id ? '' : 'required' ?>>
        <?= $err('thumbnail') ?>
      </div>
    </div>
  </div>
</div>
</form>
