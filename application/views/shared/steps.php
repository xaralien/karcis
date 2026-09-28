<?php defined('BASEPATH') OR exit('No direct script access allowed'); /* $step: 1 pilih tiket, 2 rincian & data, 3 bayar */ ?>
<ol class="steps" aria-label="Langkah pembelian">
  <?php foreach (array(1 => 'Pilih tiket', 2 => 'Rincian & data pembeli', 3 => 'Bayar') as $n => $label): ?>
  <li class="<?= $n < $step ? 'done' : ($n === $step ? 'current' : '') ?>" <?= $n === $step ? 'aria-current="step"' : '' ?>><span><?= $label ?></span></li>
  <?php endforeach; ?>
</ol>
