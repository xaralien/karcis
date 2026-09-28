<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Pemindai QR di pintu masuk. Bisa dipakai admin & staff. */
class Checkin extends Admin_Controller
{
    protected $allowed_roles = array('admin', 'staff');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ticket_model');
    }

    public function index()
    {
        $event_id = (int) $this->input->get('event_id');
        $this->render('checkin/index', array(
            'title'    => 'Check-in Tiket',
            'events'   => $this->Admin_model->events(array('status' => 'published')),
            'event_id' => $event_id,
            'recent'   => $this->Ticket_model->recent_checkins($event_id, 10),
            'nav'      => 'checkin',
        ));
    }

    /**
     * POST code = isi QR ({"ticket_id":..,"event_id":..}) atau kode tiket manual.
     * Respon JSON: status valid | used | wrong_event | unpaid | not_found
     */
    public function verify()
    {
        if ($this->input->method() !== 'post') return $this->json(array('status' => 'error'), 405);

        $raw = trim((string) $this->input->post('code'));
        $event_id = (int) $this->input->post('event_id');
        $code = $raw;
        $qr_event = NULL;

        $decoded = json_decode($raw, TRUE);
        if (is_array($decoded) && isset($decoded['ticket_id'])) {
            $code = $decoded['ticket_id'];
            $qr_event = isset($decoded['event_id']) ? (int) $decoded['event_id'] : NULL;
        }

        $t = $code ? $this->Ticket_model->by_code($code) : NULL;
        if ( ! $t) {
            return $this->json(array('status' => 'not_found', 'message' => 'Tiket tidak ditemukan.', 'code' => $code));
        }

        $info = array(
            'code' => $t->ticket_code, 'ticket_name' => $t->ticket_name, 'buyer' => $t->buyer_name,
            'order_code' => $t->order_code, 'event' => $t->event_title,
        );

        if ($qr_event !== NULL && $qr_event !== (int) $t->event_id) {
            return $this->json(array_merge($info, array('status' => 'not_found', 'message' => 'Data QR tidak cocok dengan tiket. Kemungkinan QR palsu.')));
        }
        if ($t->order_status !== 'paid') {
            return $this->json(array_merge($info, array('status' => 'unpaid', 'message' => 'Pesanan tiket ini belum lunas.')));
        }
        if ($event_id && $event_id !== (int) $t->event_id) {
            return $this->json(array_merge($info, array('status' => 'wrong_event', 'message' => 'Tiket ini untuk event lain: ' . $t->event_title . '.')));
        }
        if ($t->is_checked_in || ! $this->Ticket_model->check_in($t->id, $this->admin->id)) {
            $t = $this->Ticket_model->by_code($t->ticket_code);
            return $this->json(array_merge($info, array('status' => 'used',
                'message' => 'Tiket sudah dipakai pada ' . date('d/m/Y H.i', strtotime($t->checked_in_at)) . ($t->checked_in_by_name ? ' oleh ' . $t->checked_in_by_name : '') . '.')));
        }
        return $this->json(array_merge($info, array('status' => 'valid', 'message' => 'Tiket valid. Silakan masuk.')));
    }
}
