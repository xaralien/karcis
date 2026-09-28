<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="scan-sticky d-lg-none"><div class="scan-result mb-3" role="status" aria-live="assertive">
      <div class="big" data-r="title"></div>
      <div class="mt-1" data-r="msg"></div>
      <div class="scan-code" data-r="code"></div>
      <div class="small mt-2" data-r="info" style="opacity:.92"></div>
    </div></div>

    <div class="card-k">
      <div class="hd">
        <h5>Pindai QR tiket</h5>
        <form method="get" action="<?= $this->fmt->url('admin/checkin') ?>">
          <select class="form-select form-select-sm" name="event_id" onchange="this.form.submit()" aria-label="Event yang dijaga" style="max-width:260px">
            <option value="0">Semua event</option>
            <?php foreach ($events as $e): ?><option value="<?= $e->id ?>" <?= $event_id == $e->id ? 'selected' : '' ?>><?= html_escape($e->title) ?></option><?php endforeach; ?>
          </select>
        </form>
      </div>
      <div class="bd">
        <?php if ( ! $event_id): ?><div class="alert alert-warning small py-2">Pilih event yang sedang dijaga agar tiket event lain otomatis ditolak.</div><?php endif; ?>
        <div class="scan-box"><div id="reader"></div><div class="idle" id="scanIdle"><div><i class="bi bi-camera fs-1 d-block mb-2"></i>Kamera belum aktif</div></div></div>
        <div class="d-flex gap-2 justify-content-center mt-3">
          <button class="btn btn-primary" id="btnStart" type="button"><i class="bi bi-camera-video me-1"></i>Mulai pindai</button>
          <button class="btn btn-light-k d-none" id="btnStop" type="button">Hentikan</button>
        </div>
        <p class="hint text-center mt-2 mb-0">Kamera butuh HTTPS (atau localhost) dan izin browser.</p>

        <form id="manualForm" class="d-flex gap-2 mt-4">
          <input id="manualCode" placeholder="Atau ketik kode tiket, mis. TKT-7C2C5B8554" autocomplete="off" class="form-control uppercase">
          <button class="btn btn-outline-ink text-nowrap">Cek tiket</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
<div class="d-none d-lg-block"><div class="scan-result mb-3" role="status" aria-live="assertive">
      <div class="big" data-r="title"></div>
      <div class="mt-1" data-r="msg"></div>
      <div class="scan-code" data-r="code"></div>
      <div class="small mt-2" data-r="info" style="opacity:.92"></div>
    </div></div>
    <div class="card-k">
      <div class="hd"><h5>Check-in terakhir</h5><a class="small fw-semibold" href="<?= $this->fmt->url('admin/checkin?event_id=' . $event_id) ?>">Muat ulang</a></div>
      <?php if ($recent): ?>
      <ul class="list-group list-group-flush small" id="recentList">
        <?php foreach ($recent as $r): ?>
        <li class="list-group-item d-flex justify-content-between"><span><strong><?= html_escape($r->ticket_code) ?></strong><br><span class="text-muted-k"><?= html_escape($r->buyer_name) ?> · <?= html_escape($r->ticket_name) ?></span></span><span class="text-nowrap"><?= date('H.i', strtotime($r->checked_in_at)) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?><ul class="list-group list-group-flush small" id="recentList"></ul><div class="empty-k" id="recentEmpty"><i class="bi bi-door-open"></i>Belum ada yang check-in.</div><?php endif; ?>
    </div>
  </div>
</div>
<?php ob_start(); ?>
<script src="<?= $this->fmt->base('assets/vendor/js/html5-qrcode.min.js') ?>"></script>
<script>
(function () {
  var url = <?= json_encode($this->fmt->url('admin/checkin/verify')) ?>;
  var csrfName = <?= json_encode($this->security->get_csrf_token_name()) ?>;
  var csrfHash = <?= json_encode($this->security->get_csrf_hash()) ?>;
  var eventId = <?= (int) $event_id ?>;
  var titles = { valid: 'Boleh masuk', used: 'Sudah dipakai', wrong_event: 'Event berbeda', unpaid: 'Belum lunas', not_found: 'Tidak valid' };
  var boxes = document.querySelectorAll('.scan-result');
  var busy = false, last = '', lastAt = 0, scanner = null;

  var paint = function (status, title, msg, code, info) {
    boxes.forEach(function (b) {
      b.className = 'scan-result mb-3 show ' + status;
      b.querySelector('[data-r=title]').textContent = title;
      b.querySelector('[data-r=msg]').textContent = msg || '';
      var codeEl = b.querySelector('[data-r=code]');
      codeEl.textContent = code || '';
      codeEl.style.display = code ? '' : 'none';
      b.querySelector('[data-r=info]').innerHTML = info || '';
    });
    if (window.innerWidth < 992) window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  var beep = function (ok) {
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)(), o = ctx.createOscillator(), g = ctx.createGain();
      o.frequency.value = ok ? 880 : 220; o.connect(g); g.connect(ctx.destination); g.gain.value = .15;
      o.start(); o.stop(ctx.currentTime + (ok ? .15 : .45));
    } catch (e) {}
    if (navigator.vibrate) navigator.vibrate(ok ? 80 : [80, 60, 80]);
  };

  var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; };

  var verify = function (code) {
    code = (code || '').trim();
    if (!code || busy) return;
    if (code === last && Date.now() - lastAt < 4000) return; // abaikan pembacaan ganda
    busy = true; last = code; lastAt = Date.now();

    var body = new URLSearchParams();
    body.append('code', code); body.append('event_id', eventId); body.append(csrfName, csrfHash);

    fetch(url, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        paint(d.status, titles[d.status] || 'Gagal', d.message, d.code || '',
          d.order_code ? '<div><strong>' + esc(d.ticket_name) + '</strong> · ' + esc(d.buyer) + '</div><div>Pesanan ' + esc(d.order_code) + ' · ' + esc(d.event) + '</div>' : '');
        beep(d.status === 'valid');
        if (d.status === 'valid') {
          var li = document.createElement('li');
          li.className = 'list-group-item d-flex justify-content-between';
          li.innerHTML = '<span><strong>' + esc(d.code) + '</strong><br><span class="text-muted-k">' + esc(d.buyer) + ' · ' + esc(d.ticket_name) + '</span></span><span>baru saja</span>';
          var list = document.getElementById('recentList'); list.insertBefore(li, list.firstChild);
          var em = document.getElementById('recentEmpty'); if (em) em.remove();
        }
      })
      .catch(function () {
        paint('not_found', 'Koneksi gagal', 'Periksa jaringan lalu pindai ulang. Jika sesi habis, muat ulang halaman.', '', '');
      })
      .finally(function () { setTimeout(function () { busy = false; }, 1200); });
  };

  document.getElementById('manualForm').addEventListener('submit', function (e) {
    e.preventDefault(); last = '';
    var inp = document.getElementById('manualCode'); verify(inp.value.toUpperCase()); inp.value = '';
  });

  var start = document.getElementById('btnStart'), stop = document.getElementById('btnStop');
  start.addEventListener('click', function () {
    if (!window.Html5Qrcode) { alert('Library pemindai gagal dimuat. Gunakan input kode manual.'); return; }
    scanner = scanner || new Html5Qrcode('reader');
    scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 240, height: 240 } }, verify, function () {})
      .then(function () { document.getElementById('scanIdle').style.display = 'none'; start.classList.add('d-none'); stop.classList.remove('d-none'); })
      .catch(function (err) { alert('Kamera tidak bisa dibuka: ' + err); });
  });
  stop.addEventListener('click', function () {
    if (scanner) scanner.stop().then(function () { document.getElementById('scanIdle').style.display = ''; stop.classList.add('d-none'); start.classList.remove('d-none'); });
  });
})();
</script>
<?php $this->load->vars('scripts', ob_get_clean()); ?>
