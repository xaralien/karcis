<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars('actions',
  '<a class="btn btn-light-k btn-sm" href="' . $this->fmt->url('admin/events') . '"><i class="bi bi-arrow-left me-1"></i>Semua event</a>' .
  '<a class="btn btn-outline-ink btn-sm" href="' . $this->fmt->url('event/detail/' . $event->slug) . '" target="_blank" rel="noopener"><i class="bi bi-eye me-1"></i>Lihat halaman</a>');
$tabs = array(
  'info'      => array('Info event', NULL),
  'jadwal'    => array('Jadwal', count($schedules)),
  'tiket'     => array('Kategori tiket', count($ticket_types)),
  'fasilitas' => array('Fasilitas', count($facilities)),
  'tamu'      => array('Bintang tamu', count($guests)),
  'galeri'    => array('Galeri', count($gallery)),
);
if ( ! isset($tabs[$tab])) $tab = 'info';
$warn = array();
if ( ! $schedules) $warn[] = 'jadwal';
if ( ! $ticket_types) $warn[] = 'kategori tiket';
?>
<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
  <img class="thumb" style="width:64px;height:48px" src="<?= html_escape($this->fmt->img($event->thumbnail)) ?>" alt="">
  <div>
    <div class="fw-bold fs-5 lh-sm"><?= html_escape($event->title) ?></div>
    <span class="badge-st <?= $event->status ?>"><?= array('published' => 'Tayang', 'draft' => 'Draft', 'ended' => 'Selesai')[$event->status] ?></span>
  </div>
</div>
<?php if ($warn): ?><div class="alert alert-warning small"><i class="bi bi-info-circle me-1"></i>Event belum bisa dijual. Lengkapi <?= implode(' dan ', $warn) ?>, lalu ubah status menjadi Tayang.</div><?php endif; ?>
<?php if ($this->fmt->has_errors($field_errors)): ?><div class="alert alert-danger">Periksa kembali kolom yang ditandai merah.</div><?php endif; ?>

<ul class="nav nav-tabs-k mb-3" role="tablist">
  <?php foreach ($tabs as $k => $t): ?>
  <li class="nav-item" role="presentation">
    <button class="nav-link <?= $tab === $k ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-<?= $k ?>" type="button" role="tab"><?= $t[0] ?><?php if ($t[1] !== NULL): ?><span class="count"><?= $t[1] ?></span><?php endif; ?></button>
  </li>
  <?php endforeach; ?>
</ul>

<div class="tab-content">
  <!-- INFO -->
  <div class="tab-pane <?= $tab === 'info' ? 'show active' : '' ?>" id="tab-info" role="tabpanel">
    <?php $this->load->view('admin/events/_info_form'); ?>
    <div class="card-k mt-3" style="border-color:#F3C5C8">
      <div class="bd d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div><strong>Hapus event</strong><div class="small text-muted-k"><?= $has_orders ? 'Event ini sudah punya pesanan, jadi tidak bisa dihapus. Gunakan status Draft atau Selesai.' : 'Semua jadwal, tiket, fasilitas, bintang tamu, dan galeri ikut terhapus.' ?></div></div>
        <form method="post" action="<?= $this->fmt->url('admin/events/delete/' . $event->id) ?>" data-confirm="Hapus event ini secara permanen?"><?= $this->fmt->csrf() ?>
          <button class="btn btn-danger-soft btn-sm" <?= $has_orders ? 'disabled' : '' ?>><i class="bi bi-trash me-1"></i>Hapus event</button>
        </form>
      </div>
    </div>
  </div>

  <!-- JADWAL -->
  <div class="tab-pane <?= $tab === 'jadwal' ? 'show active' : '' ?>" id="tab-jadwal" role="tabpanel">
    <div class="card-k">
      <div class="hd"><h5>Jadwal pelaksanaan</h5><span class="hint">Tampil di halaman event: hari, tanggal, jam, lokasi</span></div>
      <div class="table-responsive">
        <table class="table table-k">
          <thead><tr><th>Label</th><th>Tanggal</th><th>Mulai</th><th>Selesai</th><th>Lokasi / area</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($schedules as $s): $fid = 'fs' . $s->id; ?>
            <tr>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="label" value="<?= html_escape($s->label) ?>" required style="min-width:110px"></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" type="date" name="event_date" value="<?= $s->event_date ?>" required></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" type="time" name="start_time" value="<?= substr($s->start_time, 0, 5) ?>" required></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" type="time" name="end_time" value="<?= substr($s->end_time, 0, 5) ?>" required></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="venue" value="<?= html_escape($s->venue) ?>" style="min-width:180px"></td>
              <td class="text-end text-nowrap">
                <form method="post" action="<?= $this->fmt->url('admin/events/schedule_save/' . $event->id . '/' . $s->id) ?>" id="<?= $fid ?>" class="d-inline"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-light-k" title="Simpan"><i class="bi bi-check-lg"></i></button></form>
                <form method="post" action="<?= $this->fmt->url('admin/events/schedule_delete/' . $event->id . '/' . $s->id) ?>" class="d-inline" data-confirm="Hapus jadwal ini?"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-danger-soft" title="Hapus"><i class="bi bi-trash"></i></button></form>
              </td>
            </tr>
          <?php endforeach; ?>
            <tr style="background:#FBFAFE">
              <td><input form="fsNew" class="form-control form-control-sm" name="label" placeholder="Hari <?= count($schedules) + 1 ?>" required></td>
              <td><input form="fsNew" class="form-control form-control-sm" type="date" name="event_date" required></td>
              <td><input form="fsNew" class="form-control form-control-sm" type="time" name="start_time" required></td>
              <td><input form="fsNew" class="form-control form-control-sm" type="time" name="end_time" required></td>
              <td><input form="fsNew" class="form-control form-control-sm" name="venue" placeholder="<?= html_escape($event->venue) ?>"></td>
              <td class="text-end"><form method="post" action="<?= $this->fmt->url('admin/events/schedule_save/' . $event->id) ?>" id="fsNew"><?= $this->fmt->csrf() ?><button class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-lg me-1"></i>Tambah</button></form></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- KATEGORI TIKET -->
  <div class="tab-pane <?= $tab === 'tiket' ? 'show active' : '' ?>" id="tab-tiket" role="tabpanel">
    <div class="card-k">
      <div class="hd"><h5>Kategori tiket</h5><span class="hint">Pembeli bisa menggabungkan beberapa kategori, maks <?= (int) $this->config->item('max_tickets_per_order') ?> tiket per transaksi</span></div>
      <div class="table-responsive">
        <table class="table table-k">
          <thead><tr><th>Nama</th><th>Keterangan</th><th>Harga (Rp)</th><th>Kuota</th><th>Terjual</th><th>Urutan</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($ticket_types as $t): $fid = 'ft' . $t->id; ?>
            <tr>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="name" value="<?= html_escape($t->name) ?>" required style="min-width:140px"></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="description" value="<?= html_escape($t->description) ?>" style="min-width:180px"></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" type="number" name="price" value="<?= (int) $t->price ?>" min="0" step="500" inputmode="numeric" required style="width:120px"></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" type="number" name="quota" value="<?= (int) $t->quota ?>" min="<?= max(1, (int) $t->sold) ?>" required style="width:90px"></td>
              <td class="text-nowrap"><strong><?= (int) $t->sold ?></strong><?= $t->sold >= $t->quota ? ' <span class="badge-st failed">Habis</span>' : '' ?></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" type="number" name="sort_order" value="<?= (int) $t->sort_order ?>" style="width:70px"></td>
              <td class="text-end text-nowrap">
                <form method="post" action="<?= $this->fmt->url('admin/events/ticket_save/' . $event->id . '/' . $t->id) ?>" id="<?= $fid ?>" class="d-inline"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-light-k" title="Simpan"><i class="bi bi-check-lg"></i></button></form>
                <form method="post" action="<?= $this->fmt->url('admin/events/ticket_delete/' . $event->id . '/' . $t->id) ?>" class="d-inline" data-confirm="Hapus kategori tiket ini?"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-danger-soft" title="Hapus"><i class="bi bi-trash"></i></button></form>
              </td>
            </tr>
          <?php endforeach; ?>
            <tr style="background:#FBFAFE">
              <td><input form="ftNew" class="form-control form-control-sm" name="name" placeholder="Reguler, VIP…" required></td>
              <td><input form="ftNew" class="form-control form-control-sm" name="description" placeholder="Area berdiri"></td>
              <td><input form="ftNew" class="form-control form-control-sm" type="number" name="price" value="100000" min="0" step="500" inputmode="numeric" required style="width:120px"></td>
              <td><input form="ftNew" class="form-control form-control-sm" type="number" name="quota" placeholder="100" min="1" required style="width:90px"></td>
              <td class="text-muted-k">0</td>
              <td><input form="ftNew" class="form-control form-control-sm" type="number" name="sort_order" value="<?= count($ticket_types) + 1 ?>" style="width:70px"></td>
              <td class="text-end"><form method="post" action="<?= $this->fmt->url('admin/events/ticket_save/' . $event->id) ?>" id="ftNew"><?= $this->fmt->csrf() ?><button class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-lg me-1"></i>Tambah</button></form></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="bd pt-0 hint">Kategori yang sudah pernah dipesan tidak bisa dihapus. Untuk menghentikan penjualan, samakan kuota dengan jumlah terjual.</div>
    </div>
  </div>

  <!-- FASILITAS -->
  <div class="tab-pane <?= $tab === 'fasilitas' ? 'show active' : '' ?>" id="tab-fasilitas" role="tabpanel">
    <div class="card-k">
      <div class="hd"><h5>Fasilitas</h5><a class="hint" href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Daftar nama ikon</a></div>
      <div class="table-responsive">
        <table class="table table-k">
          <thead><tr><th style="width:60px">Ikon</th><th>Nama fasilitas</th><th>Kode ikon</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($facilities as $f): $fid = 'ff' . $f->id; ?>
            <tr>
              <td><i class="bi <?= html_escape($f->icon) ?> fs-4" style="color:var(--plum)" data-icon-preview></i></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="name" value="<?= html_escape($f->name) ?>" required></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="icon" value="<?= html_escape($f->icon) ?>" list="iconList" style="max-width:200px"></td>
              <td class="text-end text-nowrap">
                <form method="post" action="<?= $this->fmt->url('admin/events/facility_save/' . $event->id . '/' . $f->id) ?>" id="<?= $fid ?>" class="d-inline"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-light-k" title="Simpan"><i class="bi bi-check-lg"></i></button></form>
                <form method="post" action="<?= $this->fmt->url('admin/events/facility_delete/' . $event->id . '/' . $f->id) ?>" class="d-inline" data-confirm="Hapus fasilitas ini?"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-danger-soft" title="Hapus"><i class="bi bi-trash"></i></button></form>
              </td>
            </tr>
          <?php endforeach; ?>
            <tr style="background:#FBFAFE">
              <td><i class="bi bi-plus-circle fs-4 text-muted-k" data-icon-preview></i></td>
              <td><input form="ffNew" class="form-control form-control-sm" name="name" placeholder="Area parkir" required></td>
              <td><input form="ffNew" class="form-control form-control-sm" name="icon" placeholder="bi-p-circle" list="iconList" style="max-width:200px"></td>
              <td class="text-end"><form method="post" action="<?= $this->fmt->url('admin/events/facility_save/' . $event->id) ?>" id="ffNew"><?= $this->fmt->csrf() ?><button class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-lg me-1"></i>Tambah</button></form></td>
            </tr>
          </tbody>
        </table>
        <datalist id="iconList">
          <?php foreach (array('bi-cup-hot','bi-cup-straw','bi-egg-fried','bi-basket','bi-shop','bi-moon-stars','bi-droplet','bi-bag-check','bi-heart-pulse','bi-p-circle','bi-car-front','bi-wifi','bi-universal-access','bi-snow','bi-person-hearts','bi-award','bi-grid-3x3','bi-camera','bi-lightning-charge','bi-shield-check','bi-bus-front','bi-music-note-beamed','bi-ticket-perforated','bi-people','bi-clock','bi-geo-alt','bi-suitcase','bi-water','bi-tree','bi-sun','bi-umbrella') as $ic): ?><option value="<?= $ic ?>"><?php endforeach; ?>
        </datalist>
      </div>
    </div>
  </div>

  <!-- BINTANG TAMU -->
  <div class="tab-pane <?= $tab === 'tamu' ? 'show active' : '' ?>" id="tab-tamu" role="tabpanel">
    <div class="card-k">
      <div class="hd"><h5>Bintang tamu</h5><span class="hint">Foto persegi, maks 3MB</span></div>
      <div class="table-responsive">
        <table class="table table-k">
          <thead><tr><th style="width:60px">Foto</th><th>Nama</th><th>Peran</th><th>Ganti foto</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($guests as $g): $fid = 'fg' . $g->id; ?>
            <tr>
              <td><img class="guest-av" src="<?= html_escape($this->fmt->img($g->photo)) ?>" alt=""></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="name" value="<?= html_escape($g->name) ?>" required></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" name="role" value="<?= html_escape($g->role) ?>" required></td>
              <td><input form="<?= $fid ?>" class="form-control form-control-sm" type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="max-width:220px"></td>
              <td class="text-end text-nowrap">
                <form method="post" action="<?= $this->fmt->url('admin/events/guest_save/' . $event->id . '/' . $g->id) ?>" enctype="multipart/form-data" id="<?= $fid ?>" class="d-inline"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-light-k" title="Simpan"><i class="bi bi-check-lg"></i></button></form>
                <form method="post" action="<?= $this->fmt->url('admin/events/guest_delete/' . $event->id . '/' . $g->id) ?>" class="d-inline" data-confirm="Hapus bintang tamu ini?"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-danger-soft" title="Hapus"><i class="bi bi-trash"></i></button></form>
              </td>
            </tr>
          <?php endforeach; ?>
            <tr style="background:#FBFAFE">
              <td><i class="bi bi-person-plus fs-4 text-muted-k"></i></td>
              <td><input form="fgNew" class="form-control form-control-sm" name="name" placeholder="Nama artis" required></td>
              <td><input form="fgNew" class="form-control form-control-sm" name="role" placeholder="Penyanyi, komika…" required></td>
              <td><input form="fgNew" class="form-control form-control-sm" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required style="max-width:220px"></td>
              <td class="text-end"><form method="post" action="<?= $this->fmt->url('admin/events/guest_save/' . $event->id) ?>" enctype="multipart/form-data" id="fgNew"><?= $this->fmt->csrf() ?><button class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-lg me-1"></i>Tambah</button></form></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- GALERI -->
  <div class="tab-pane <?= $tab === 'galeri' ? 'show active' : '' ?>" id="tab-galeri" role="tabpanel">
    <div class="card-k">
      <div class="hd"><h5>Galeri</h5></div>
      <div class="bd">
        <form method="post" action="<?= $this->fmt->url('admin/events/gallery_upload/' . $event->id) ?>" enctype="multipart/form-data" class="row g-2 align-items-end mb-4"><?= $this->fmt->csrf() ?>
          <div class="col-md-5"><label class="form-label small" for="gImage">Gambar</label><input class="form-control form-control-sm" id="gImage" type="file" name="image" accept="image/jpeg,image/png,image/webp" required></div>
          <div class="col-md-5"><label class="form-label small" for="gCaption">Keterangan <span class="text-muted-k fw-normal">(opsional)</span></label><input class="form-control form-control-sm" id="gCaption" name="caption" maxlength="160"></div>
          <div class="col-md-2"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-upload me-1"></i>Unggah</button></div>
        </form>
        <?php if ($gallery): ?>
        <div class="row g-3">
          <?php foreach ($gallery as $g): ?>
          <div class="col-6 col-md-4 col-xl-3">
            <div class="gal-item">
              <img src="<?= html_escape($this->fmt->img($g->image)) ?>" alt="">
              <form method="post" action="<?= $this->fmt->url('admin/events/gallery_delete/' . $event->id . '/' . $g->id) ?>" data-confirm="Hapus gambar ini?"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-danger-soft" title="Hapus"><i class="bi bi-trash"></i></button></form>
              <?php if ($g->caption): ?><div class="cap"><?= html_escape($g->caption) ?></div><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?><div class="empty-k"><i class="bi bi-images"></i>Belum ada gambar galeri.</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
