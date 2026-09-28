<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pembelian tanpa login.
 * /event/tickets/{slug} -> POST /checkout -> /checkout (rincian + data) -> POST /checkout/process -> Duitku
 */
class Checkout extends MY_Controller
{
    protected $field_errors = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Event_model', 'Order_model'));
        $this->load->library('form_validation');
    }

    public function index()
    {
        // Datang dari halaman pilih tiket
        if ($this->input->method() === 'post') {
            $event_id = (int) $this->input->post('event_id');
            $cart     = $this->build_cart($event_id, (array) $this->input->post('qty'), $error);

            if ( ! $cart) {
                $event = $this->Event_model->get($event_id);
                $this->session->set_flashdata('error', $error);
                $this->go($event ? 'event/tickets/' . $event->slug : 'explore');
            }
            $this->session->set_userdata('cart', array('event_id' => $event_id, 'qty' => $cart['qty_map']));
            $this->go('checkout');
        }

        $saved = $this->session->userdata('cart');
        if ( ! $saved) $this->go('explore');

        $cart = $this->build_cart($saved['event_id'], $saved['qty'], $error);
        if ( ! $cart) $this->bounce($saved['event_id'], $error, TRUE);

        $this->render('checkout/index', array(
            'title'     => 'Rincian Tiket',
            'cart'      => $cart,
            'schedules' => $this->Event_model->schedules($cart['event']->id),
        ));
    }

    public function process()
    {
        if ($this->input->method() !== 'post') $this->go('checkout');

        $saved = $this->session->userdata('cart');
        if ( ! $saved) $this->go('explore');

        $cart = $this->build_cart($saved['event_id'], $saved['qty'], $error);
        if ( ! $cart) $this->bounce($saved['event_id'], $error);

        $this->form_validation->set_rules('buyer_name', 'Nama lengkap', 'required|trim|min_length[3]|max_length[100]');
        $this->form_validation->set_rules('buyer_email', 'Email', 'required|trim|strtolower|max_length[150]|valid_email|callback_check_email');
        $this->form_validation->set_rules('buyer_email_confirm', 'Konfirmasi email', 'required|trim|strtolower|matches[buyer_email]');
        $this->form_validation->set_rules('buyer_phone', 'Nomor HP', 'required|trim|callback_check_phone');
        $this->form_validation->set_rules('agree', 'Persetujuan', 'required');
        $this->form_validation->set_message('matches', 'Email dan konfirmasi email tidak sama. Periksa kembali ketikanmu.');
        $this->form_validation->set_message('required', '{field} wajib diisi.');

        if ($this->form_validation->run() === FALSE) {
            return $this->render('checkout/index', array(
                'title' => 'Rincian Tiket', 'cart' => $cart,
                'schedules' => $this->Event_model->schedules($cart['event']->id),
            ));
        }

        $order = array(
            'order_code'      => $this->Order_model->generate_code(),
            'user_id'         => $this->user ? $this->user->id : NULL,
            'access_token'    => bin2hex(random_bytes(20)),
            'event_id'        => $cart['event']->id,
            'buyer_name'      => $this->input->post('buyer_name', TRUE),
            'buyer_email'     => strtolower(trim($this->input->post('buyer_email'))),
            'buyer_phone'     => $this->input->post('buyer_phone', TRUE),
            'ticket_qty'      => $cart['fees']['qty'],
            'subtotal'        => $cart['fees']['subtotal'],
            'service_fee'     => $cart['fees']['service_fee'],
            'transaction_fee' => $cart['fees']['transaction_fee'],
            'total'           => $cart['fees']['total'],
            'status'          => 'pending',
            'expired_at'      => date('Y-m-d H:i:s', time() + 60 * (int) $this->config->item('order_expiry_minutes')),
        );

        $items = array();
        foreach ($cart['lines'] as $l) {
            $items[] = array('ticket_type_id' => $l['id'], 'ticket_name' => $l['name'], 'price' => $l['price'], 'qty' => $l['qty']);
        }

        $order_id = $this->Order_model->create($order, $items);
        if ( ! $order_id) {
            $this->session->set_flashdata('error', 'Pesanan belum tersimpan. Coba lagi dalam beberapa saat.');
            $this->go('checkout');
        }

        $this->load->library('duitku');
        $order['event_title'] = $cart['event']->title;
        $inv = $this->duitku->create_invoice($order, $items);

        if ( ! $inv['success']) {
            $this->Order_model->update($order_id, array('status' => 'failed'));
            $this->session->set_flashdata('error', 'Pembayaran belum bisa dibuat: ' . $inv['message']);
            $this->go('checkout');
        }

        $this->Order_model->update($order_id, array('payment_url' => $inv['payment_url'], 'duitku_reference' => $inv['reference']));
        $this->session->unset_userdata('cart');
        $this->session->set_userdata('last_order', $order['order_code']);
        $this->go($inv['payment_url']);
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
    protected function bounce($event_id, $error, $clear = FALSE)
    {
        if ($clear) $this->session->unset_userdata('cart');
        $this->session->set_flashdata('error', $error);
        $event = $this->Event_model->get($event_id);
        $this->go($event ? 'event/tickets/' . $event->slug : 'explore');
    }

    /** Hitung ulang harga & stok di server; jangan percaya angka dari browser */
    protected function build_cart($event_id, array $qty, &$error = NULL)
    {
        $event = $this->Event_model->get($event_id);
        if ( ! $event) { $error = 'Event tidak ditemukan.'; return FALSE; }

        $max   = $this->fmt->max_tickets();
        $lines = array(); $qty_map = array(); $total_qty = 0;

        foreach ($this->Event_model->ticket_types($event->id) as $t) {
            $q = isset($qty[$t->id]) ? max(0, (int) $qty[$t->id]) : 0;
            if ($q === 0) continue;

            $available = (int) $t->quota - (int) $t->sold - $this->Order_model->reserved_qty($t->id);
            if ($q > $available) {
                $error = $available > 0 ? "Sisa tiket {$t->name} tinggal {$available}." : "Tiket {$t->name} sudah habis.";
                return FALSE;
            }
            $lines[] = array('id' => (int) $t->id, 'name' => $t->name, 'price' => (int) $t->price, 'qty' => $q);
            $qty_map[$t->id] = $q;
            $total_qty += $q;
        }

        if ($total_qty < 1)    { $error = 'Pilih minimal 1 tiket.'; return FALSE; }
        if ($total_qty > $max) { $error = "Maksimal {$max} tiket per transaksi."; return FALSE; }

        return array('event' => $event, 'lines' => $lines, 'qty_map' => $qty_map, 'fees' => $this->fmt->fees($lines));
    }
}
