<?php
defined('BASEPATH') or exit('No direct script access allowed');


/** Ganti password — bisa diakses admin & staff */
class Account extends Admin_Controller
{
    protected $allowed_roles = array('admin', 'staff');

    public function index()
    {
        $this->load->library('form_validation');
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('current', 'Password lama', 'required');
            $this->form_validation->set_rules('password', 'Password baru', 'required|min_length[8]');
            $this->form_validation->set_rules('password_confirm', 'Konfirmasi password', 'required|matches[password]');

            if ($this->form_validation->run()) {
                if (! password_verify($this->input->post('current'), $this->admin->password)) {
                    $this->session->set_flashdata('error', 'Password lama salah.');
                } else {
                    $this->db->where('id', $this->admin->id)->update('admins', array('password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT)));
                    $this->session->set_flashdata('success', 'Password berhasil diganti.');
                }
                $this->go('admin/account');
            }
        }
        $this->render('account/index', array('title' => 'Ganti Password', 'nav' => 'account'));
    }
}
