<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends Base_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admin_model');
        $this->load->library('form_validation');
    }

    public function login()
    {
        if ($this->session->userdata('admin_id')) $this->go('admin');

        $data = array('title' => 'Masuk Admin', 'app_name' => $this->config->item('app_name'), 'error' => NULL);

        if ($this->input->method() === 'post') {
            $attempts = (array) $this->session->userdata('login_attempts');
            $attempts = array_filter($attempts, function ($t) {
                return $t > time() - 600;
            });

            if (count($attempts) >= 5) {
                $data['error'] = 'Terlalu banyak percobaan gagal. Coba lagi dalam 10 menit.';
            } else {
                $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
                $this->form_validation->set_rules('password', 'Password', 'required');

                if ($this->form_validation->run()) {
                    $admin = $this->Admin_model->find_admin_by_email($this->input->post('email'));
                    if ($admin && password_verify($this->input->post('password'), $admin->password)) {
                        $this->session->sess_regenerate(TRUE);
                        $this->session->set_userdata('admin_id', $admin->id);
                        $this->session->unset_userdata('login_attempts');
                        $this->db->where('id', $admin->id)->update('admins', array('last_login_at' => date('Y-m-d H:i:s')));

                        $to = $this->session->userdata('admin_redirect');
                        $this->session->unset_userdata('admin_redirect');
                        $this->go($admin->role === 'staff' ? 'admin/checkin' : ($to ?: 'admin'));
                    }
                    $attempts[] = time();
                    $this->session->set_userdata('login_attempts', $attempts);
                    $data['error'] = 'Email atau password salah.';
                }
            }
        }
        $this->load->view('admin/auth/login', $data);
    }

    public function logout()
    {
        $this->session->unset_userdata(array('admin_id', 'admin_redirect'));
        $this->session->sess_regenerate(TRUE);
        $this->go('admin/auth/login');
    }
}
