<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Pagination tanpa library: $pages dari $this->fmt->pages(), $page_base = URL dasar */
if ($pages['last'] <= 1) return;
$base = isset($page_base) ? $page_base : $this->fmt->url($this->uri->uri_string());
?>
<ul class="pagination mb-0">
  <?php if ($pages['prev']): ?><li class="page-item"><a class="page-link" href="<?= $base . $pages['prev'] ?>">Sebelumnya</a></li><?php endif; ?>
  <?php foreach ($pages['links'] as $l): ?>
    <?php if ($l['active']): ?>
      <li class="page-item active"><span class="page-link"><?= $l['no'] ?></span></li>
    <?php else: ?>
      <li class="page-item"><a class="page-link" href="<?= $base . $l['url'] ?>"><?= $l['no'] ?></a></li>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if ($pages['next']): ?><li class="page-item"><a class="page-link" href="<?= $base . $pages['next'] ?>">Berikutnya</a></li><?php endif; ?>
</ul>
