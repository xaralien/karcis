<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Pita pratinjau untuk admin: event draft / selesai tidak terlihat publik */
if (empty($preview)) return;
$label = array('draft' => 'Draft', 'ended' => 'Selesai')[$event->status];
?>
<div class="preview-bar" role="status">
  <i class="bi bi-eye-slash"></i>
  <span><strong>Pratinjau admin · <?= $label ?>.</strong> Halaman ini belum terlihat pengunjung dan tiketnya belum bisa dibeli.</span>
  <a href="<?= $this->fmt->url('admin/events/edit/' . $event->id) ?>">Edit event</a>
</div>
