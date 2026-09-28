<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
  <div class="foot">
    <span>Dokumen dihasilkan otomatis oleh sistem <?= html_escape($meta['app_name']) ?>.</span>
    <span><?= html_escape($meta['title']) ?> · <?= html_escape($meta['dicetak']) ?></span>
  </div>
</div>
<?php if ($auto): ?><script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script><?php endif; ?>
</body>
</html>
