<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* $order, $event, $schedules, $tickets, $total, $all_codes, $back_url, $auto, $app_name */
$single    = count($tickets) === 1 && $total > 1;
$first_s   = $schedules ? $schedules[0] : NULL;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Cetak tiket <?= html_escape($single ? $tickets[0]->ticket_code : $order->order_code) ?> | <?= html_escape($app_name) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root { --ink:#1E1540; --muted:#6F6890; --line:#DCD6EC; --sun:#FFB800; --plum:#5B3FD9; --paper:#EDEAF5; }
    * { box-sizing: border-box; }
    html, body { margin: 0; }
    body { font-family: "Plus Jakarta Sans", "Segoe UI", Arial, sans-serif; color: var(--ink); background: var(--paper); -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .display { font-family: "Bricolage Grotesque", "Plus Jakarta Sans", "Segoe UI", Arial, sans-serif; letter-spacing: -0.02em; }

    /* ---------- Toolbar (layar saja) ---------- */
    .toolbar { position: sticky; top: 0; z-index: 5; background: var(--ink); color: #fff; padding: 12px 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .toolbar a, .toolbar button { font: inherit; font-weight: 700; font-size: 14px; border-radius: 999px; padding: 9px 16px; text-decoration: none; border: 0; cursor: pointer; }
    .toolbar .back { color: #fff; background: rgba(255,255,255,.12); }
    .toolbar .print { background: var(--sun); color: var(--ink); margin-left: auto; }
    .toolbar .info { font-size: 13px; color: rgba(255,255,255,.7); }
    .toolbar select { font: inherit; font-size: 14px; padding: 8px 10px; border-radius: 10px; border: 0; }
    .toolbar button:focus-visible, .toolbar a:focus-visible, .toolbar select:focus-visible { outline: 3px solid var(--sun); outline-offset: 2px; }

    /* ---------- Halaman kertas ---------- */
    .sheets { padding: 24px 12px 60px; }
    .sheet { width: 210mm; min-height: 297mm; margin: 0 auto 20px; background: #fff; padding: 14mm; box-shadow: 0 4px 24px rgba(30,21,64,.12); display: flex; flex-direction: column; }

    .ticket { border: 1.5px solid var(--line); border-radius: 5mm; overflow: hidden; display: grid; grid-template-columns: 1fr 66mm; position: relative; }
    .t-head { grid-column: 1 / -1; background: var(--ink); color: #fff; padding: 6mm 8mm; display: flex; align-items: center; justify-content: space-between; }
    .brand { display: flex; align-items: center; gap: 3mm; font-weight: 800; font-size: 20px; }
    .brand .mark { width: 9mm; height: 6.5mm; background: var(--sun); border-radius: 1.3mm; position: relative; }
    .brand .mark::before, .brand .mark::after { content:""; position:absolute; top:2mm; width:2.6mm; height:2.6mm; border-radius:50%; background: var(--ink); }
    .brand .mark::before { left:-1.3mm; } .brand .mark::after { right:-1.3mm; }
    .t-head .count { font-size: 13px; color: rgba(255,255,255,.75); text-align: right; }
    .t-head .count strong { display: block; color: var(--sun); font-size: 15px; }

    .t-main { padding: 8mm; min-width: 0; }
    .t-main h1 { font-size: 30px; line-height: 1.05; margin: 0 0 3mm; }
    .cat { display: inline-block; background: #F1ECFF; color: #4329B8; font-weight: 700; font-size: 14px; padding: 1.2mm 3.5mm; border-radius: 99px; }
    .facts { display: grid; grid-template-columns: 1fr 1fr; gap: 5mm 6mm; margin-top: 7mm; }
    .fact .k { font-size: 11.5px; color: var(--muted); font-weight: 600; margin-bottom: 1mm; }
    .fact .v { font-size: 14px; font-weight: 700; line-height: 1.35; }
    .fact .v small { display: block; font-weight: 500; color: var(--muted); font-size: 12px; }
    .fact.wide { grid-column: 1 / -1; }

    .t-stub { border-left: 2px dashed var(--line); padding: 7mm 6mm; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; }
    .t-stub::after { content: ""; position: absolute; left: -4.2mm; bottom: -4mm; width: 7mm; height: 7mm; border-radius: 50%; background: #fff; border: 1.5px solid var(--line); }
    .qr { width: 52mm; height: 52mm; image-rendering: pixelated; display: block; }
    .code { font-family: "Bricolage Grotesque", Consolas, monospace; font-weight: 800; font-size: 17px; letter-spacing: .04em; margin-top: 3mm; }
    .eid { font-size: 12px; color: var(--muted); margin-top: 1mm; }
    .used { margin-top: 3mm; font-weight: 800; color: #C8323B; border: 2px solid #C8323B; border-radius: 2mm; padding: 1mm 3mm; font-size: 13px; transform: rotate(-4deg); }

    .terms { margin-top: 9mm; font-size: 12.5px; line-height: 1.6; color: #3E3760; }
    .terms h2 { font-size: 15px; margin: 0 0 2mm; font-family: inherit; }
    .terms ol { margin: 0; padding-left: 5mm; }
    .cut { margin-top: 9mm; border-top: 1.5px dashed var(--line); padding-top: 4mm; display: flex; justify-content: space-between; font-size: 11.5px; color: var(--muted); }
    .sheet-foot { margin-top: auto; padding-top: 8mm; font-size: 11px; color: var(--muted); display: flex; justify-content: space-between; }

    @page { size: A4 portrait; margin: 0; }
    @media print {
      body { background: #fff; }
      .toolbar { display: none !important; }
      .sheets { padding: 0; }
      .sheet { margin: 0; box-shadow: none; width: 210mm; height: 297mm; min-height: 0; page-break-after: always; break-after: page; overflow: hidden; }
      .sheet:last-child { page-break-after: auto; break-after: auto; }
    }
    @media screen and (max-width: 820px) {
      .sheets { overflow-x: auto; }
      .sheet { transform-origin: top left; }
    }
  </style>
</head>
<body>
<div class="toolbar">
  <a class="back" href="<?= $back_url ?>">&larr; Kembali ke tiket</a>
  <?php if ($total > 1): ?>
    <label class="info" for="pick">Cetak</label>
    <select id="pick" onchange="location.href=this.value">
      <option value="<?= $print_url ?>" <?= ! $single ? 'selected' : '' ?>>Semua tiket (<?= $total ?> halaman)</option>
      <?php foreach ($all_codes as $i => $c): ?>
        <option value="<?= $print_url ?>?id=<?= rawurlencode($c) ?>" <?= $single && $tickets[0]->ticket_code === $c ? 'selected' : '' ?>>Tiket <?= $i + 1 ?> saja · <?= html_escape($c) ?></option>
      <?php endforeach; ?>
    </select>
  <?php endif; ?>
  <span class="info"><?= count($tickets) ?> halaman A4, 1 tiket per halaman</span>
  <button class="print" type="button" onclick="window.print()"><?= count($tickets) > 1 ? 'Cetak ' . count($tickets) . ' tiket' : 'Cetak tiket ini' ?></button>
</div>

<main class="sheets">
<?php foreach ($tickets as $t):
    $num = array_search($t->ticket_code, $all_codes) + 1; ?>
  <section class="sheet" aria-label="Tiket <?= $num ?>">
    <article class="ticket">
      <header class="t-head">
        <div class="brand display"><span class="mark" aria-hidden="true"></span><?= html_escape($app_name) ?></div>
        <div class="count"><strong>E-tiket</strong>Tiket <?= $num ?> dari <?= $total ?></div>
      </header>

      <div class="t-main">
        <h1 class="display"><?= html_escape($event->title) ?></h1>
        <span class="cat"><?= html_escape($t->ticket_name) ?></span>

        <div class="facts">
          <?php foreach ($schedules as $s): ?>
          <div class="fact">
            <div class="k"><?= html_escape($s->label) ?></div>
            <div class="v"><?= $this->fmt->tgl($s->event_date) ?><small><?= $this->fmt->jam($s->start_time) ?>–<?= $this->fmt->jam($s->end_time) ?> WIB</small></div>
          </div>
          <?php endforeach; ?>
          <div class="fact wide">
            <div class="k">Lokasi</div>
            <div class="v"><?= html_escape($event->venue) ?><small><?= html_escape($event->address) ?>, <?= html_escape($event->city) ?></small></div>
          </div>
          <div class="fact">
            <div class="k">Nama pemesan</div>
            <div class="v"><?= html_escape($order->buyer_name) ?></div>
          </div>
          <div class="fact">
            <div class="k">Kode pesanan</div>
            <div class="v"><?= html_escape($order->order_code) ?></div>
          </div>
        </div>
      </div>

      <aside class="t-stub">
        <img class="qr" src="<?= $this->fmt->base('uploads/qr/' . $t->qr_file) ?>" alt="Kode QR tiket <?= html_escape($t->ticket_code) ?>">
        <div class="code"><?= html_escape($t->ticket_code) ?></div>
        <div class="eid">ID event <?= (int) $t->event_id ?></div>
        <?php if ($t->is_checked_in): ?><div class="used">Sudah dipakai</div><?php endif; ?>
      </aside>
    </article>

    <div class="terms">
      <h2>Ketentuan masuk</h2>
      <ol>
        <li>Satu QR berlaku untuk satu orang dan hanya bisa dipindai satu kali.</li>
        <li>Tunjukkan tiket ini (cetak atau dari layar HP) kepada petugas di pintu masuk.</li>
        <li>Jangan bagikan foto QR ke media sosial. Tiket yang sudah dipindai orang lain tidak bisa dipakai lagi.</li>
        <li>Pastikan QR tidak terlipat, basah, atau tercoret agar mudah dipindai.</li>
        <li>Tiket yang sudah dibeli tidak dapat dikembalikan.</li>
      </ol>
    </div>

    <div class="cut"><span>Gunting di sini jika perlu</span><span><?= html_escape($t->ticket_code) ?></span></div>

    <footer class="sheet-foot">
      <span>Dicetak <?= date('d/m/Y H.i') ?> WIB</span>
      <span><?= html_escape(preg_replace('#^https?://#', '', rtrim($this->fmt->base(), '/'))) ?></span>
    </footer>
  </section>
<?php endforeach; ?>
</main>

<script>
(function () {
  <?php if ($auto): ?>
  // Tunggu semua gambar QR selesai dimuat sebelum membuka dialog cetak
  var imgs = Array.prototype.slice.call(document.images);
  Promise.all(imgs.map(function (im) {
    return im.complete ? Promise.resolve() : new Promise(function (r) { im.onload = im.onerror = r; });
  })).then(function () { setTimeout(function () { window.print(); }, 250); });
  <?php endif; ?>
  // Di layar sempit, perkecil pratinjau kertas agar muat
  var fit = function () {
    var w = window.innerWidth - 24, px = 210 * 96 / 25.4;
    document.querySelectorAll('.sheet').forEach(function (s) {
      if (w < px) { var k = w / px; s.style.transform = 'scale(' + k + ')'; s.style.marginBottom = (-(297 * 96 / 25.4) * (1 - k) + 20) + 'px'; }
      else { s.style.transform = ''; s.style.marginBottom = ''; }
    });
  };
  window.addEventListener('resize', fit); fit();
  window.addEventListener('beforeprint', function () { document.querySelectorAll('.sheet').forEach(function (s) { s.style.transform = ''; s.style.marginBottom = ''; }); });
  window.addEventListener('afterprint', fit);
})();
</script>
</body>
</html>
