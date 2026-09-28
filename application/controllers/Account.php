<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Akun pembeli: daftar, masuk, riwayat pesanan, dan tiket.
 * Membeli tiket tetap bisa tanpa login; pesanan lama dengan email sama otomatis
 * ikut masuk ke akun begitu akun dibuat atau login.
 */
class Account extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('User_model', 'Order_model', 'Event_model'));
        $this->load->library('form_validation');
    }

    /** Ringkasan akun */
    public function index()
    {
        $this->require_login();

        $this->render('account/index', array(
            'title'      => 'Akun Saya',
            'stats'      => $this->User_model->stats($this->user->id, $this->user->email),
            'orders'     => $this->User_model->orders($this->user->id, $this->user->email, 5),
            'active_nav' => 'account',
        ));
    }

    /** Riwayat semua pesanan & tiket */
    public function orders()
    {
        $this->require_login();

        $per_page = 10;
        $page  = max(1, (int) $this->input->get('page'));
        $total = $this->User_model->count_orders($this->user->id, $this->user->email);

        $this->render('account/orders', array(
            'title'      => 'Riwayat Tiket',
            'orders'     => $this->User_model->orders($this->user->id, $this->user->email, $per_page, ($page - 1) * $per_page),
            'total'      => $total,
            'pages'      => $this->fmt->pages($total, $per_page, $page),
            'active_nav' => 'account',
        ));
    }

    public function login()
    {
        if ($this->user) $this->go('account');

        $error = NULL;
        if ($this->input->method() === 'post') {
            $attempts = array_filter((array) $this->session->userdata('user_attempts'), function ($t) { return $t > time() - 600; });

            if (count($attempts) >= 6) {
                $error = 'Terlalu banyak percobaan gagal. Coba lagi dalam 10 menit.';
            } else {
                $this->form_validation->set_rules('email', 'Email', 'required|trim|strtolower|valid_email');
                $this->form_validation->set_rules('password', 'Password', 'required');
                $this->form_validation->set_message('required', '{field} wajib diisi.');

                if ($this->form_validation->run()) {
                    $u = $this->User_model->find_by_email($this->input->post('email'));
                    if ($u && password_verify($this->input->post('password'), $u->password)) {
                        return $this->sign_in($u);
                    }
                    $attempts[] = time();
                    $this->session->set_userdata('user_attempts', $attempts);
                    $error = 'Email atau password salah.';
                }
            }
        }

        $this->render('account/login', array('title' => 'Masuk', 'error' => $error, 'active_nav' => 'account'));
    }

    public function register()
    {
        if ($this->user) $this->go('account');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Nama lengkap', 'required|trim|min_length[3]|max_length[100]');
            $this->form_validation->set_rules('email', 'Email', 'required|trim|strtolower|valid_email|max_length[150]|is_unique[users.email]|callback_check_email');
            $this->form_validation->set_rules('phone', 'Nomor HP', 'required|trim|callback_check_phone');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
            $this->form_validation->set_rules('password_confirm', 'Ulangi password', 'required|matches[password]');
            $this->form_validation->set_message('is_unique', 'Email ini sudah punya akun. Silakan masuk.');
            $this->form_validation->set_message('matches', 'Password dan ulangi password belum sama.');
            $this->form_validation->set_message('required', '{field} wajib diisi.');

            if ($this->form_validation->run()) {
                $id = $this->User_model->create(
                    $this->input->post('name', TRUE),
                    $this->input->post('email'),
                    $this->input->post('phone', TRUE),
                    $this->input->post('password')
                );
                return $this->sign_in($this->User_model->find($id), TRUE);
            }
        }

        $this->render('account/register', array('title' => 'Daftar Akun', 'active_nav' => 'account'));
    }

    public function logout()
    {
        $this->session->unset_userdata(array('user_id', 'after_login'));
        $this->session->sess_regenerate(TRUE);
        $this->session->set_flashdata('success', 'Kamu sudah keluar.');
        $this->go('');
    }

    /** Ubah data diri & password */
    public function profile()
    {
        $this->require_login();

        if ($this->input->method() === 'post') {
            if ($this->input->post('form') === 'password') {
                $this->form_validation->set_rules('current', 'Password lama', 'required');
                $this->form_validation->set_rules('password', 'Password baru', 'required|min_length[8]');
                $this->form_validation->set_rules('password_confirm', 'Ulangi password baru', 'required|matches[password]');

                if ($this->form_validation->run()) {
                    if ( ! password_verify($this->input->post('current'), $this->user->password)) {
                        $this->session->set_flashdata('error', 'Password lama salah.');
                    } else {
                        $this->User_model->update_password($this->user->id, $this->input->post('password'));
                        $this->session->set_flashdata('success', 'Password berhasil diganti.');
                    }
                    $this->go('account/profile');
                }
            } else {
                $this->form_validation->set_rules('name', 'Nama lengkap', 'required|trim|min_length[3]|max_length[100]');
                $this->form_validation->set_rules('phone', 'Nomor HP', 'required|trim|callback_check_phone');

                if ($this->form_validation->run()) {
                    $this->User_model->update_profile($this->user->id, array(
                        'name'  => $this->input->post('name', TRUE),
                        'phone' => $this->input->post('phone', TRUE),
                    ));
                    $this->session->set_flashdata('success', 'Data akun diperbarui.');
                    $this->go('account/profile');
                }
            }
        }

        $this->render('account/profile', array('title' => 'Data Akun', 'active_nav' => 'account'));
    }

    /* ---------------- Validasi ---------------- */
    public function check_email($email)
    {
        if ($suggest = $this->fmt->email_typo($email)) {
            $this->form_validation->set_message('check_email', 'Sepertinya ada salah ketik. Maksudmu ' . html_escape($suggest) . '?');
            return FALSE;
        }
        if ( ! $this->fmt->email_valid($email)) {
            $this->form_validation->set_message('check_email', 'Domain email tidak ditemukan. Pastikan email aktif karena tiket dikirim ke sana.');
            return FALSE;
        }
        return TRUE;
    }

    public function check_phone($phone)
    {
        if (preg_match('/^(\+62|62|0)8[0-9]{7,12}$/', preg_replace('/[\s-]/', '', (string) $phone))) return TRUE;
        $this->form_validation->set_message('check_phone', 'Nomor HP harus diawali 08 atau +628, contoh 081234567890.');
        return FALSE;
    }

    /* ---------------- Util ---------------- */
    protected function sign_in($u, $new = FALSE)
    {
        $this->session->sess_regenerate(TRUE);
        $this->session->set_userdata('user_id', $u->id);
        $this->session->unset_userdata('user_attempts');
        $this->User_model->touch_login($u->id);

        $claimed = $this->User_model->claim_orders($u->id, $u->email);
        $msg = $new ? 'Akun dibuat. Selamat datang, ' . $u->name . '!' : 'Selamat datang kembali, ' . $u->name . '!';
        if ($claimed) $msg .= ' ' . $claimed . ' pesanan lama dengan email ini ikut masuk ke riwayatmu.';
        $this->session->set_flashdata('success', $msg);

        $to = $this->session->userdata('after_login');
        $this->session->unset_userdata('after_login');
        $this->go($to ?: 'account');
    }
}
