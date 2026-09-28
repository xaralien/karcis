<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Daftar jenis tiket tanpa pemilih. Variabel: $event, $ticket_types */
$any_available = FALSE;
foreach ($ticket_types as $t) if ((int) $t->available > 0) $any_available = TRUE;
?>
<?php foreach ($ticket_types as $t): $avail = max(0, (int) $t->available); ?>
<div class="tt-preview">
  <div><div class="fw-semibold"><?= html_escape($t->name) ?></div><div class="tt-price small"><?= $this->fmt->rupiah($t->price) ?></div></div>
  <?php if ($avail <= 0): ?><span class="soldout-badge">Habis</span>
  <?php elseif ($avail <= 20): ?><span class="soldout-badge">Sisa <?= $avail ?></span>
  <?php else: ?><span class="avail-badge">Tersedia</span><?php endif; ?>
</div>
<?php endforeach; ?>
