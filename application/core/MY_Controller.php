<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Fungsi dasar yang dipakai controller depan maupun admin */
class Base_Controller extends CI_Controller
{
    public $upload_error = '';

    /** Pengganti redirect() dari url helper */
    protected function go($uri = '')
    {
        $this->fmt->go($uri);
    }

    /** Upload gambar ke uploads/events/. NULL bila tidak ada file, FALSE bila gagal. */
    protected function upload_image($field)
    {
        if (empty($_FILES[$field]['name'])) return NULL;

        $dir = FCPATH . 'uploads/events/';
        if ( ! is_dir($dir)) mkdir($dir, 0755, TRUE);

        $this->load->library('upload');
        $this->upload->initialize(array(
            'upload_path'      => $dir,
            'allowed_types'    => 'jpg|jpeg|png|webp',
            'max_size'         => 3072,
            'encrypt_name'     => TRUE,
            'file_ext_tolower' => TRUE,
        ), TRUE);

        if ( ! $this->upload->do_upload($field)) {
            $raw = strip_tags($this->upload->display_errors('', ''));
            $this->upload_error = (stripos($raw, 'size') !== FALSE || stripos($raw, 'larger') !== FALSE)
                ? 'Ukuran file melebihi 3MB.'
                : (stripos($raw, 'filetype') !== FALSE ? 'Format harus JPG, PNG, atau WEBP.' : 'Upload gagal: ' . $raw);
            return FALSE;
        }
        return 'uploads/events/' . $this->upload->data('file_name');
    }

    protected function delete_local_image($path)
    {
        if ($path && strpos($path, 'uploads/events/') === 0 && is_file(FCPATH . $path)) {
            @unlink(FCPATH . $path);
        }
    }
}

/**
 * Controller halaman depan: memilih tampilan desktop atau mobile.
 * Otomatis dari User Agent, bisa dipaksa lewat /home/mode/desktop atau /home/mode/mobile.
 */
class MY_Controller extends Base_Controller
{
    public $view_mode = 'desktop';
    /** Pembeli yang sedang login, atau NULL */
    public $user = NULL;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $uid = (int) $this->session->userdata('user_id');
        $this->user = $uid ? $this->User_model->find($uid) : NULL;
        if ($uid && ! $this->user) $this->session->unset_userdata('user_id');

        $forced = $this->session->userdata('view_mode');
        $this->view_mode = in_array($forced, array('desktop', 'mobile'), TRUE)
            ? $forced
            : ($this->agent->is_mobile() ? 'mobile' : 'desktop');
    }

    /** Wajib login untuk halaman akun */
    protected function require_login()
    {
        if ( ! $this->user) {
            $this->session->set_userdata('after_login', $this->uri->uri_string());
            $this->session->set_flashdata('error', 'Masuk dulu untuk membuka halaman itu.');
            $this->go('account/login');
        }
    }

    /** Render view dari folder sesuai platform: views/{desktop|mobile}/{view}.php */
    protected function render($view, $data = array(), $layout = 'main')
    {
        $data['app_name']  = $this->config->item('app_name');
        $data['view_mode'] = $this->view_mode;
        $data['user']      = $this->user;
        $data['title']     = isset($data['title']) ? $data['title'] . ' | ' . $data['app_name'] : $data['app_name'];
        $data['content']   = $this->load->view($this->view_mode . '/' . $view, $data, TRUE);
        $this->load->view($this->view_mode . '/layouts/' . $layout, $data);
    }
}

/** Controller panel admin: semua controller di controllers/admin/ kecuali Auth */
class Admin_Controller extends Base_Controller
{
    protected $admin;
    protected $allowed_roles = array('admin');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admin_model');

        $id = (int) $this->session->userdata('admin_id');
        $this->admin = $id ? $this->Admin_model->find_admin($id) : NULL;

        if ( ! $this->admin) {
            $this->session->set_userdata('admin_redirect', $this->uri->uri_string());
            $this->go('admin/auth/login');
        }
        if ( ! in_array($this->admin->role, $this->allowed_roles, TRUE)) {
            $this->session->set_flashdata('error', 'Akunmu hanya punya akses ke halaman check-in.');
            $this->go('admin/checkin');
        }
    }

    protected function render($view, $data = array())
    {
        $data['admin']      = $this->admin;
        $data['app_name']   = $this->config->item('app_name');
        $data['page_title'] = isset($data['title']) ? $data['title'] : 'Dashboard';
        $data['title']      = $data['page_title'] . ' | Admin ' . $data['app_name'];
        $data['content']    = $this->load->view('admin/' . $view, $data, TRUE);
        $this->load->view('admin/layouts/main', $data);
    }

    protected function json($data, $status = 200)
    {
        return $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
    }
}
