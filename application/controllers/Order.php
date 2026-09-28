<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Cek pesanan tanpa login: kode pesanan + email pembeli */
class Order extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Order_model', 'Event_model', 'Ticket_model'));
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data = array('title' => 'Cek Pesanan', 'order' => NULL, 'event' => NULL, 'not_found' => FALSE, 'active_nav' => 'order');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('order_code', 'Kode pesanan', 'required|trim|strtoupper');
            $this->form_validation->set_rules('email', 'Email', 'required|trim|strtolower|valid_email');

            if ($this->form_validation->run()) {
                $order = $this->Order_model->get_by_code($this->input->post('order_code'));
                if ($order && $order->buyer_email === $this->input->post('email')) {
                    $data['order'] = $order;
                    $data['event'] = $this->Event_model->get_any($order->event_id);
                } else {
                    $data['not_found'] = TRUE;
                }
            }
        }
        $this->render('order/index', $data);
    }

    public function resend()
    {
        if ($this->input->method() !== 'post') $this->go('order');

        $order = $this->Order_model->get_by_code($this->input->post('order_code', TRUE));
        $email = strtolower(trim((string) $this->input->post('email')));

        if ($order && $order->buyer_email === $email && $order->status === 'paid') {
            if ($order->email_sent_at && strtotime($order->email_sent_at) > time() - 300) {
                $this->session->set_flashdata('error', 'Email baru saja dikirim. Tunggu 5 menit sebelum kirim ulang, dan cek folder spam.');
            } else {
                $ok = $this->Ticket_model->send_email($order->id);
                $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Tiket dikirim ulang ke ' . $email . '.' : 'Email gagal terkirim. Coba lagi nanti.');
            }
        } else {
            $this->session->set_flashdata('error', 'Pesanan lunas dengan data tersebut tidak ditemukan.');
        }
        $this->go('order');
    }
}
