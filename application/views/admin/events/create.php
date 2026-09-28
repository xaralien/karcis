<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->vars('actions', '<a class="btn btn-light-k btn-sm" href="' . $this->fmt->url('admin/events') . '"><i class="bi bi-arrow-left me-1"></i>Kembali</a>');
?>
<?php if ($this->fmt->has_errors($field_errors)): ?><div class="alert alert-danger">Periksa kembali kolom yang ditandai merah.</div><?php endif; ?>
<?php $this->load->view('admin/events/_info_form'); ?>
