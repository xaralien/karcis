<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Order_model', 'Event_model', 'Ticket_model'));
        $this->load->library('duitku');
    }

    /**
     * Callback server-to-server dari Duitku (POST). Daftarkan URL ini di dashboard Duitku:
     * https://domainmu/payment/callback  (sudah dikecualikan dari CSRF di config.php)
     */
    public function callback()
    {
        $post = $this->input->post();
        if ( ! $post || ! $this->duitku->verify_callback($post)) {
            log_message('error', 'Callback Duitku ditolak: ' . json_encode($post));
            return $this->output->set_status_header(400)->set_output('Bad Signature');
        }

        $order = $this->Order_model->get_by_code($post['merchantOrderId']);
        if ( ! $order) return $this->output->set_status_header(404)->set_output('Order not found');

        if ((int) $post['amount'] !== (int) $order->total) {
            log_message('error', "Nominal callback tidak cocok untuk {$order->order_code}");
            return $this->output->set_status_header(400)->set_output('Amount mismatch');
        }

        if ($post['resultCode'] === '00') {
            $this->settle($order, isset($post['reference']) ? $post['reference'] : NULL, isset($post['paymentCode']) ? $post['paymentCode'] : NULL);
        } elseif ($order->status === 'pending') {
            $this->Order_model->update($order->id, array('status' => 'failed'));
        }

        return $this->output->set_output('OK');
    }

    /** Halaman kembali dari Duitku: /payment/status?order=KRC... */
    public function status()
    {
        $code  = $this->input->get('order', TRUE) ?: $this->input->get('merchantOrderId', TRUE) ?: $this->session->userdata('last_order');
        $order = $code ? $this->Order_model->get_by_code($code) : NULL;
        if ( ! $order) $this->go('');

        // Jaga-jaga callback belum sampai: tanyakan langsung ke Duitku
        if ($order->status === 'pending') {
            $st = $this->duitku->check_status($order->order_code);
            if ($st && isset($st['statusCode']) && $st['statusCode'] === '00' && (int) $st['amount'] === (int) $order->total) {
                $this->settle($order, isset($st['reference']) ? $st['reference'] : NULL, NULL);
            } elseif ($st && isset($st['statusCode']) && $st['statusCode'] === '02') {
                $this->Order_model->update($order->id, array('status' => 'failed'));
            }
            $order = $this->Order_model->get($order->id);

            if ($order->status === 'pending' && strtotime($order->expired_at) < time()) {
                $this->Order_model->update($order->id, array('status' => 'expired'));
                $order->status = 'expired';
            }
        }

        // Lunas tapi tiket belum terbit (mis. sempat gagal saat membuat QR)
        if ($order->status === 'paid' && ! $this->Order_model->tickets($order->id)) {
            $this->Ticket_model->repair($order->id);
        }

        $this->render('payment/result', array(
            'title'      => 'Status Pembayaran',
            'order'      => $order,
            'event'      => $this->Event_model->get_any($order->event_id),
            'ticket_url' => $this->fmt->url('ticket/show/' . $order->order_code . '/' . $order->access_token),
        ));
    }

    /**
     * Kirim ulang tiket ke email pembeli, bisa sekalian memperbaiki alamat yang salah ketik.
     * Dilindungi access_token pesanan, jadi hanya pemilik tautan yang bisa memakainya.
     */
    public function resend()
    {
        if ($this->input->method() !== 'post') $this->go('');

        $code  = (string) $this->input->post('order_code', TRUE);
        $token = (string) $this->input->post('token');
        $order = $this->Order_model->get_by_code($code);
        if ( ! $order || ! hash_equals($order->access_token, $token)) show_404();

        $back = 'payment/status?order=' . rawurlencode($order->order_code);

        if ($order->status !== 'paid') {
            $this->session->set_flashdata('error', 'Tiket baru bisa dikirim setelah pembayaran lunas.');
            $this->go($back);
        }

        // Ganti alamat email bila pembeli mengisi alamat baru
        $baru = strtolower(trim((string) $this->input->post('email')));
        if ($baru !== '' && $baru !== $order->buyer_email) {
            if ($typo = $this->fmt->email_typo($baru)) {
                $this->session->set_flashdata('error', 'Sepertinya ada salah ketik. Maksudmu ' . $typo . '?');
                $this->go($back);
            }
            if ( ! $this->fmt->email_valid($baru)) {
                $this->session->set_flashdata('error', 'Alamat email itu tidak valid atau domainnya tidak ditemukan.');
                $this->go($back);
            }
            $this->Order_model->update($order->id, array('buyer_email' => $baru, 'email_sent_at' => NULL));
            $order->buyer_email  = $baru;
            $order->email_sent_at = NULL;
            log_message('info', 'Email pesanan ' . $order->order_code . ' diubah oleh pembeli menjadi ' . $baru);
        }

        // Batas kirim ulang: 1 kali per 3 menit
        if ($order->email_sent_at && strtotime($order->email_sent_at) > time() - 180) {
            $sisa = ceil((strtotime($order->email_sent_at) + 180 - time()) / 60);
            $this->session->set_flashdata('error', 'Email baru saja dikirim. Coba lagi dalam ' . $sisa . ' menit, dan cek dulu folder spam atau promosi.');
            $this->go($back);
        }

        $this->Ticket_model->issue($order->id);
        $ok = $this->Ticket_model->send_email($order->id);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok
            ? 'Tiket dikirim ulang ke ' . $order->buyer_email . '. Cek juga folder spam bila belum terlihat.'
            : 'Email gagal terkirim. Tiketmu tetap aman dan bisa dibuka lewat tombol Lihat tiket QR.');
        $this->go($back);
    }

    protected function settle($order, $reference, $payment_code)
    {
        $changed = $this->Order_model->mark_paid($order->id, $reference, $payment_code);
        $this->Ticket_model->issue($order->id);
        $fresh = $this->Order_model->get($order->id);
        if ($changed || empty($fresh->email_sent_at)) {
            $this->Ticket_model->send_email($order->id);
        }
    }
}
