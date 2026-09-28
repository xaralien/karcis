<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penerbitan tiket: 1 baris tiket per kuantitas, QR berisi id tiket + id event,
 * lalu dikirim ke email pembeli. (Menggantikan library Ticket_issuer.)
 */
class Ticket_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Order_model', 'Event_model'));
    }

    public function qr_payload($ticket_code, $event_id)
    {
        return json_encode(array('ticket_id' => $ticket_code, 'event_id' => (int) $event_id));
    }

    public function by_code($code)
    {
        return $this->db->select('t.*, tt.name AS ticket_name, o.order_code, o.status AS order_status, o.buyer_name, o.buyer_email,
                e.title AS event_title, a.name AS checked_in_by_name')
            ->from('tickets t')
            ->join('ticket_types tt', 'tt.id = t.ticket_type_id')
            ->join('orders o', 'o.id = t.order_id')
            ->join('events e', 'e.id = t.event_id')
            ->join('admins a', 'a.id = t.checked_in_by', 'left')
            ->where('t.ticket_code', strtoupper(trim($code)))->get()->row();
    }

    /** Aman dipanggil berkali-kali: callback Duitku bisa terkirim ulang */
    public function issue($order_id)
    {
        $order = $this->Order_model->get($order_id);
        if ( ! $order) return array();

        $existing = $this->Order_model->tickets($order_id);
        if ($existing) return $existing;

        $dir = FCPATH . 'uploads/qr/';
        if ( ! is_dir($dir)) mkdir($dir, 0755, TRUE);

        $this->db->trans_start();
        foreach ($this->Order_model->items($order_id) as $item) {
            for ($i = 0; $i < (int) $item->qty; $i++) {
                $code = $this->new_code();
                $file = $code . '.png';
                $this->write_qr_png($this->qr_payload($code, $order->event_id), $dir . $file);

                $this->db->insert('tickets', array(
                    'ticket_code'    => $code,
                    'order_id'       => $order_id,
                    'event_id'       => $order->event_id,
                    'ticket_type_id' => $item->ticket_type_id,
                    'qr_file'        => $file,
                ));
            }
            $this->db->set('sold', 'sold + ' . (int) $item->qty, FALSE)
                     ->where('id', $item->ticket_type_id)->update('ticket_types');
        }
        $this->db->trans_complete();

        return $this->Order_model->tickets($order_id);
    }

    /** Terbitkan tiket bila belum ada, lalu kirim email. Untuk pesanan lunas yang tiketnya kosong. */
    public function repair($order_id)
    {
        $order = $this->Order_model->get($order_id);
        if ( ! $order || $order->status !== 'paid') return FALSE;
        $had = (bool) $this->Order_model->tickets($order_id);
        $this->issue($order_id);
        if ( ! $had || empty($order->email_sent_at)) $this->send_email($order_id);
        return TRUE;
    }

    public function send_email($order_id)
    {
        $order   = $this->Order_model->get($order_id);
        $tickets = $this->Order_model->tickets($order_id);
        if ( ! $order || ! $tickets) return FALSE;

        $event     = $this->Event_model->get_any($order->event_id);
        $schedules = $this->Event_model->schedules($order->event_id);

        $this->load->library('email');
        $this->email->clear(TRUE);
        $this->email->from($this->config->item('mail_from'), $this->config->item('mail_from_name'));
        $this->email->to($order->buyer_email);
        $this->email->subject('Tiket kamu: ' . $event->title . ' (' . $order->order_code . ')');

        foreach ($tickets as $t) {
            $path = FCPATH . 'uploads/qr/' . $t->qr_file;
            $this->email->attach($path, 'inline', $t->ticket_code . '.png');
            $t->cid = $this->email->attachment_cid($path);
        }

        $this->email->message($this->load->view('emails/ticket', array(
            'order' => $order, 'event' => $event, 'tickets' => $tickets, 'schedules' => $schedules,
            'ticket_url' => $this->fmt->url('ticket/show/' . $order->order_code . '/' . $order->access_token),
        ), TRUE));

        if (@$this->email->send(FALSE)) {
            $this->db->where('id', $order_id)->update('orders', array('email_sent_at' => date('Y-m-d H:i:s')));
            return TRUE;
        }
        log_message('error', 'Gagal kirim email tiket: ' . $this->email->print_debugger(array('headers')));
        return FALSE;
    }

    /* ---------------- Check-in ---------------- */
    /** Atomik: hanya berhasil kalau tiket belum pernah dipakai */
    public function check_in($ticket_id, $admin_id)
    {
        $this->db->where('id', (int) $ticket_id)->where('is_checked_in', 0)
            ->update('tickets', array('is_checked_in' => 1, 'checked_in_at' => date('Y-m-d H:i:s'), 'checked_in_by' => (int) $admin_id));
        return $this->db->affected_rows() === 1;
    }

    public function undo_check_in($ticket_id, $order_id)
    {
        return $this->db->where('id', (int) $ticket_id)->where('order_id', (int) $order_id)
            ->update('tickets', array('is_checked_in' => 0, 'checked_in_at' => NULL, 'checked_in_by' => NULL));
    }

    public function recent_checkins($event_id = 0, $limit = 10)
    {
        $this->db->select('t.ticket_code, t.checked_in_at, tt.name AS ticket_name, o.buyer_name, e.title AS event_title')
            ->from('tickets t')->join('ticket_types tt', 'tt.id = t.ticket_type_id')
            ->join('orders o', 'o.id = t.order_id')->join('events e', 'e.id = t.event_id')
            ->where('t.is_checked_in', 1);
        if ($event_id) $this->db->where('t.event_id', (int) $event_id);
        return $this->db->order_by('t.checked_in_at', 'DESC')->limit($limit)->get()->result();
    }

    /* ---------------- QR ---------------- */
    /**
     * Simpan QR sebagai PNG. Pakai GD bila tersedia; kalau tidak (mis. extension=gd
     * belum aktif di XAMPP), PNG ditulis manual dengan zlib supaya tetap terbaca.
     */
    public function write_qr_png($data, $path, $scale = 8, $margin = 2)
    {
        require_once APPPATH . 'third_party/phpqrcode/phpqrcode.php';

        if (function_exists('imagecreate') && function_exists('imagepng')) {
            QRcode::png($data, $path, QR_ECLEVEL_M, $scale, $margin);
            return is_file($path);
        }

        $rows = QRcode::text($data, FALSE, QR_ECLEVEL_M, 1, 0); // '1' = hitam, '0' = putih
        $n    = count($rows);
        $size = ($n + 2 * $margin) * $scale;

        $white_line = str_repeat("\xFF", $size);
        $raw = '';
        for ($y = 0; $y < $size; $y++) {
            $my = intdiv($y, $scale) - $margin;
            if ($my < 0 || $my >= $n) { $raw .= "\x00" . $white_line; continue; }
            $line = '';
            for ($x = 0; $x < $size; $x++) {
                $mx = intdiv($x, $scale) - $margin;
                $line .= ($mx >= 0 && $mx < $n && $rows[$my][$mx] === '1') ? "\x00" : "\xFF";
            }
            $raw .= "\x00" . $line; // filter byte per baris
        }

        $chunk = function ($type, $body) {
            return pack('N', strlen($body)) . $type . $body . pack('N', crc32($type . $body));
        };
        $png = "\x89PNG\r\n\x1a\n"
             . $chunk('IHDR', pack('NNCCCCC', $size, $size, 8, 0, 0, 0, 0))
             . $chunk('IDAT', gzcompress($raw, 9))
             . $chunk('IEND', '');

        return file_put_contents($path, $png) !== FALSE;
    }

    protected function new_code()
    {
        do {
            $code = 'TKT-' . strtoupper(bin2hex(random_bytes(5)));
        } while ($this->db->where('ticket_code', $code)->count_all_results('tickets') > 0);
        return $code;
    }
}
