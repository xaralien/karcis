<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card-k">
      <div class="hd"><h5>Daftar akun</h5></div>
      <div class="table-responsive">
        <table class="table table-k">
          <thead><tr><th>Nama</th><th>Peran</th><th>Login terakhir</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><strong><?= html_escape($u->name) ?></strong><?= (int) $u->id === (int) $admin->id ? ' <span class="small text-muted-k">(kamu)</span>' : '' ?><div class="small text-muted-k"><?= html_escape($u->email) ?></div></td>
              <td><span class="badge-st <?= $u->role === 'admin' ? 'published' : 'draft' ?>"><?= $u->role === 'admin' ? 'Admin' : 'Petugas check-in' ?></span></td>
              <td class="small"><?= $u->last_login_at ? date('d/m/Y H.i', strtotime($u->last_login_at)) : 'Belum pernah' ?></td>
              <td class="text-end">
                <?php if ((int) $u->id !== (int) $admin->id): ?>
                <form method="post" action="<?= $this->fmt->url('admin/users/delete/' . $u->id) ?>" data-confirm="Hapus akun ' . $u->email . '?"><?= $this->fmt->csrf() ?><button class="btn btn-icon btn-danger-soft" title="Hapus"><i class="bi bi-trash"></i></button></form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card-k">
      <div class="hd"><h5>Tambah akun</h5></div>
      <div class="bd">
        <form method="post" action="<?= $this->fmt->url('admin/users/store') ?>"><?= $this->fmt->csrf() ?>
          <div class="mb-3"><label class="form-label" for="uName">Nama</label><input class="form-control" id="uName" name="name" required></div>
          <div class="mb-3"><label class="form-label" for="uEmail">Email</label><input class="form-control" id="uEmail" name="email" type="email" required></div>
          <div class="mb-3"><label class="form-label" for="uPass">Password</label><input class="form-control" id="uPass" name="password" type="password" minlength="8" required><div class="hint">Minimal 8 karakter.</div></div>
          <div class="mb-3">
            <label class="form-label" for="uRole">Peran</label>
            <select class="form-select" id="uRole" name="role">
              <option value="staff">Petugas check-in (hanya pemindai QR)</option>
              <option value="admin">Admin (akses penuh)</option>
            </select>
          </div>
          <button class="btn btn-primary w-100">Tambah akun</button>
        </form>
      </div>
    </div>
  </div>
</div>
