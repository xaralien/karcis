/* Karcis — interaksi bersama desktop & mobile */
(function () {
  'use strict';

  var rupiah = function (n) { return 'Rp' + Number(n).toLocaleString('id-ID'); };

  /* ---------- Pemilih tiket + hitung biaya ---------- */
  var form = document.querySelector('[data-ticket-form]');
  if (form) {
    var max       = parseInt(form.dataset.max, 10);
    var feeTicket = parseInt(form.dataset.serviceFee, 10);
    var feeTrx    = parseInt(form.dataset.transactionFee, 10);
    var inputs    = Array.prototype.slice.call(form.querySelectorAll('[data-qty]'));

    var totalQty = function () { return inputs.reduce(function (s, i) { return s + (parseInt(i.value, 10) || 0); }, 0); };

    var refresh = function () {
      var qty = totalQty(), subtotal = 0;
      inputs.forEach(function (i) { subtotal += (parseInt(i.value, 10) || 0) * parseInt(i.dataset.price, 10); });
      var service = qty * feeTicket, trx = qty > 0 ? feeTrx : 0, total = subtotal + service + trx;

      inputs.forEach(function (i) {
        var wrap = i.closest('.stepper'), v = parseInt(i.value, 10) || 0, stock = parseInt(i.dataset.stock, 10);
        wrap.querySelector('[data-minus]').disabled = v <= 0;
        wrap.querySelector('[data-plus]').disabled  = qty >= max || v >= stock;
      });

      document.querySelectorAll('[data-out="qty"]').forEach(function (el) { el.textContent = qty; });
      document.querySelectorAll('[data-out="subtotal"]').forEach(function (el) { el.textContent = rupiah(subtotal); });
      document.querySelectorAll('[data-out="service"]').forEach(function (el) { el.textContent = rupiah(service); });
      document.querySelectorAll('[data-out="service-label"]').forEach(function (el) { el.textContent = qty + ' × ' + rupiah(feeTicket); });
      document.querySelectorAll('[data-out="trx"]').forEach(function (el) { el.textContent = rupiah(trx); });
      document.querySelectorAll('[data-out="total"]').forEach(function (el) { el.textContent = rupiah(total); });
      document.querySelectorAll('[data-out="limit"]').forEach(function (el) {
        el.textContent = qty >= max ? 'Batas ' + max + ' tiket per transaksi tercapai.' : 'Bisa pilih ' + (max - qty) + ' tiket lagi.';
        el.classList.toggle('text-danger', qty >= max);
      });
      document.querySelectorAll('[data-submit-ticket]').forEach(function (b) { b.disabled = qty < 1; });
    };

    form.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-plus],[data-minus]');
      if (!btn) return;
      var input = btn.closest('.stepper').querySelector('[data-qty]');
      var v = parseInt(input.value, 10) || 0;
      if (btn.hasAttribute('data-plus') && totalQty() < max) v++;
      if (btn.hasAttribute('data-minus') && v > 0) v--;
      input.value = v;
      refresh();
    });
    inputs.forEach(function (i) {
      i.addEventListener('input', function () {
        var v = Math.max(0, parseInt(i.value, 10) || 0);
        var others = totalQty() - (parseInt(i.value, 10) || 0);
        i.value = Math.min(v, max - others, parseInt(i.dataset.stock, 10));
        refresh();
      });
    });
    refresh();
  }

  /* ---------- Checkout: pastikan email benar ---------- */
  var co = document.querySelector('[data-checkout-form]');
  if (co) {
    var email = co.querySelector('#buyer_email');
    var confirmEl = co.querySelector('#buyer_email_confirm');
    var hint = co.querySelector('[data-email-hint]');
    var typos = { 'gmial.com':'gmail.com','gmai.com':'gmail.com','gmail.co':'gmail.com','gamil.com':'gmail.com','gnail.com':'gmail.com','gmail.con':'gmail.com','yahoo.co':'yahoo.com','yaho.com':'yahoo.com','yahoo.con':'yahoo.com','hotmial.com':'hotmail.com','outlok.com':'outlook.com','outlook.co':'outlook.com' };
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    var checkEmail = function () {
      var v = email.value.trim().toLowerCase(), parts = v.split('@');
      hint.innerHTML = '';
      if (parts.length === 2 && typos[parts[1]]) {
        var s = parts[0] + '@' + typos[parts[1]];
        hint.innerHTML = 'Maksudmu <button type="button" class="btn btn-link p-0 align-baseline fw-bold" data-fix="' + s + '">' + s + '</button>?';
      }
    };
    var checkMatch = function () {
      var ok = confirmEl.value === '' || confirmEl.value.trim().toLowerCase() === email.value.trim().toLowerCase();
      confirmEl.classList.toggle('is-invalid', !ok);
    };
    email.addEventListener('blur', checkEmail);
    email.addEventListener('input', checkMatch);
    confirmEl.addEventListener('input', checkMatch);
    confirmEl.addEventListener('paste', function (e) { e.preventDefault(); });
    hint.addEventListener('click', function (e) {
      var b = e.target.closest('[data-fix]'); if (!b) return;
      email.value = b.dataset.fix; hint.innerHTML = ''; checkMatch();
    });

    var modalEl = document.getElementById('confirmEmailModal');
    var modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    var confirmed = false;

    co.addEventListener('submit', function (e) {
      if (confirmed) return;
      var v = email.value.trim().toLowerCase();
      if (!co.checkValidity() || !re.test(v) || v !== confirmEl.value.trim().toLowerCase()) {
        e.preventDefault();
        co.classList.add('was-validated');
        checkMatch();
        return;
      }
      if (modal) {
        e.preventDefault();
        modalEl.querySelector('[data-email-preview]').textContent = v;
        modal.show();
      }
    });
    if (modalEl) {
      modalEl.querySelector('[data-confirm-pay]').addEventListener('click', function () {
        confirmed = true;
        this.disabled = true;
        this.textContent = 'Menghubungkan ke pembayaran…';
        co.submit();
      });
    }
  }
})();
