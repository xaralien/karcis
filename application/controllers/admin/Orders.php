<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Order_model', 'Ticket_model'));
    }

    protected function filter()
    {
        return array(
            'q'        => trim((string) $this->input->get('q', TRUE)),
            'status'   => (string) $this->input->get('status', TRUE),
            'event_id' => (int) $this->input->get('event_id'),
            'from'     => (string) $this->input->get('from', TRUE),
            'to'       => (string) $this->input->get('to', TRUE),
        );
    }

    public function index()
    {
        $f = $this->filter();
        $per_page = 25;
        $page = max(1, (int) $this->input->get('page'));
        $total = $this->Admin_model->count_orders($f);

        $this->render('orders/index', array(
            'title' => 'Pesanan', 'filter' => $f, 'total' => $total,
            'orders' => $this->Admin_model->orders($f, $per_page, ($page - 1) * $per_page),
            'events' => $this->Admin_model->events(),
            'scan' => $this->Admin_model->order_scan_summary($f), 'pages' => $this->fmt->pages($total, $per_page, $page, $f), 'nav' => 'orders',
        ));
    }

    public function export()
    {
        $f = $this->filter();
        $rows = $this->Admin_model->orders($f, 0);
        $out = fopen('php://temp', 'r+');
        fputcsv($out, array('Kode', 'Tanggal', 'Event', 'Nama', 'Email', 'HP', 'Jumlah Tiket', 'Harga Tiket', 'Biaya Layanan', 'Biaya Transaksi', 'Total', 'Status', 'Tiket Terbit', 'Tiket Sudah Scan', 'Dibayar', 'Ref Duitku'));
        foreach ($rows as $o) {
            fputcsv($out, array($o->order_code, $o->created_at, $o->event_title, $o->buyer_name, $o->buyer_email, $o->buyer_phone,
                $o->ticket_qty, $o->subtotal, $o->service_fee, $o->transaction_fee, $o->total, $o->status,
                (int) $o->tickets_issued, (int) $o->tickets_scanned, $o->paid_at, $o->duitku_reference));
        }
        rewind($out);
        $csv = "\xEF\xBB\xBF" . stream_get_contents($out);
        fclose($out);

        $this->output
            ->set_content_type('text/csv', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="pesanan-' . date('Ymd-His') . '.csv"')
            ->set_header('Content-Length: ' . strlen($csv))
            ->set_output($csv);
    }

    public function detail($code = '')
    {
        $order = $this->Order_model->get_by_code($code);
        if ( ! $order) show_404();

        $this->render('orders/detail', array(
            'title'   => 'Pesanan ' . $order->order_code,
            'order'   => $order,
            'event'   => $this->Admin_model->event($order->event_id),
            'items'   => $this->Order_model->items($order->id),
            'tickets' => $this->Order_model->tickets($order->id),
            'nav'     => 'orders',
        ));
    }

    public function resend($code = '')
    {
        $order = $this->post_order($code);
        if ($order->status !== 'paid') {
            $this->session->set_flashdata('error', 'Hanya pesanan lunas yang bisa dikirimi tiket.');
        } else {
            // Petugas bisa membetulkan alamat email yang salah ketik
            $baru = strtolower(trim((string) $this->input->post('email')));
            if ($baru !== '' && $baru !== $order->buyer_email) {
                if ( ! $this->fmt->email_valid($baru)) {
                    $this->session->set_flashdata('error', 'Alamat email baru tidak valid atau domainnya tidak ditemukan.');
                    $this->go('admin/orders/detail/' . $order->order_code);
                }
                $this->Order_model->update($order->id, array('buyer_email' => $baru));
                log_message('info', 'Email pesanan ' . $order->order_code . ' diubah admin ' . $this->admin->email . ' menjadi ' . $baru);
                $order->buyer_email = $baru;
            }
            $this->Ticket_model->issue($order->id);
            $ok = $this->Ticket_model->send_email($order->id);
            $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Tiket dikirim ulang ke ' . $order->buyer_email . '.' : 'Email gagal terkirim. Periksa pengaturan SMTP.');
        }
        $this->go('admin/orders/detail/' . $order->order_code);
    }

    public function reissue($code = '')
    {
        $order = $this->post_order($code);
        if ($this->Ticket_model->repair($order->id)) {
            $n = count($this->Order_model->tickets($order->id));
            $this->session->set_flashdata('success', "{$n} tiket QR tersedia dan email dikirim ke {$order->buyer_email}.");
        } else {
            $this->session->set_flashdata('error', 'Hanya pesanan lunas yang bisa diterbitkan tiketnya.');
        }
        $this->go('admin/orders/detail/' . $order->order_code);
    }

    /** Tanyakan status ke Duitku lalu terbitkan tiket jika ternyata sudah dibayar */
    public function sync($code = '')
    {
        $order = $this->post_order($code);
        if ($order->status !== 'pending') {
            $this->session->set_flashdata('error', 'Status pesanan sudah final: ' . $order->status . '.');
            $this->go('admin/orders/detail/' . $order->order_code);
        }
        $this->load->library('duitku');
        $st = $this->duitku->check_status($order->order_code);

        if ( ! $st || ! isset($st['statusCode'])) {
            $this->session->set_flashdata('error', 'Tidak bisa menghubungi Duitku. Coba lagi nanti.');
        } elseif ($st['statusCode'] === '00' && (int) $st['amount'] === (int) $order->total) {
            $this->Order_model->mark_paid($order->id, isset($st['reference']) ? $st['reference'] : NULL, NULL);
            $this->Ticket_model->repair($order->id);
            $this->session->set_flashdata('success', 'Pembayaran terkonfirmasi. Tiket diterbitkan dan dikirim.');
        } elseif ($st['statusCode'] === '02') {
            $this->Order_model->update($order->id, array('status' => 'failed'));
            $this->session->set_flashdata('success', 'Duitku melaporkan pembayaran gagal. Status diubah menjadi gagal.');
        } else {
            $this->session->set_flashdata('success', 'Pembayaran masih diproses di Duitku.');
        }
        $this->go('admin/orders/detail/' . $order->order_code);
    }

    public function undo_checkin($code = '', $ticket_id = 0)
    {
        $order = $this->post_order($code);
        $this->Ticket_model->undo_check_in($ticket_id, $order->id);
        $this->session->set_flashdata('success', 'Check-in tiket dibatalkan. QR bisa dipindai lagi.');
        $this->go('admin/orders/detail/' . $order->order_code);
    }

    protected function post_order($code)
    {
        if ($this->input->method() !== 'post') $this->go('admin/orders/detail/' . $code);
        $order = $this->Order_model->get_by_code($code);
        if ( ! $order) show_404();
        return $order;
    }
}
