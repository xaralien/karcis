<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Akun admin & petugas check-in */
class Users extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->render('users/index', array('title' => 'Akun Admin', 'users' => $this->Admin_model->admins(), 'nav' => 'users'));
    }

    public function store()
    {
        if ($this->input->method() !== 'post') $this->go('admin/users');
        $this->form_validation->set_rules('name', 'Nama', 'required|trim|min_length[2]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|strtolower|valid_email|is_unique[admins.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
        $this->form_validation->set_rules('role', 'Peran', 'required|in_list[admin,staff]');
        $this->form_validation->set_message('is_unique', 'Email sudah terdaftar.');

        if ( ! $this->form_validation->run()) {
            $this->session->set_flashdata('error', strip_tags($this->form_validation->error_string(' ', ' ')));
        } else {
            $this->db->insert('admins', array(
                'name' => $this->input->post('name', TRUE), 'email' => $this->input->post('email'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT), 'role' => $this->input->post('role'),
            ));
            $this->session->set_flashdata('success', 'Akun ditambahkan.');
        }
        $this->go('admin/users');
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') $this->go('admin/users');
        if ((int) $id === (int) $this->admin->id) {
            $this->session->set_flashdata('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        } else {
            $this->db->where('id', (int) $id)->delete('admins');
            $this->session->set_flashdata('success', 'Akun dihapus.');
        }
        $this->go('admin/users');
    }
}
