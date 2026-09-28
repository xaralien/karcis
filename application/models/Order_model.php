<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model
{
    public function create(array $order, array $items)
    {
        $this->db->trans_start();
        $this->db->insert('orders', $order);
        $order_id = $this->db->insert_id();
        foreach ($items as $it) {
            $it['order_id'] = $order_id;
            $this->db->insert('order_items', $it);
        }
        $this->db->trans_complete();
        return $this->db->trans_status() ? $order_id : FALSE;
    }

    public function get($id)
    {
        return $this->db->where('id', (int) $id)->get('orders')->row();
    }

    public function get_by_code($code)
    {
        return $this->db->where('order_code', $code)->get('orders')->row();
    }

    public function items($order_id)
    {
        return $this->db->where('order_id', $order_id)->get('order_items')->result();
    }

    public function tickets($order_id)
    {
        return $this->db->select('t.*, tt.name AS ticket_name')
            ->from('tickets t')->join('ticket_types tt', 'tt.id = t.ticket_type_id')
            ->where('t.order_id', $order_id)->order_by('t.id')->get()->result();
    }

    public function update($id, array $data)
    {
        return $this->db->where('id', $id)->update('orders', $data);
    }

    /** Tandai lunas hanya jika masih pending. Return TRUE bila status berubah. */
    public function mark_paid($id, $reference, $payment_code)
    {
        $this->db->where('id', $id)->where('status', 'pending')->update('orders', array(
            'status' => 'paid', 'paid_at' => date('Y-m-d H:i:s'),
            'duitku_reference' => $reference, 'payment_code' => $payment_code,
        ));
        return $this->db->affected_rows() > 0;
    }

    public function generate_code()
    {
        do {
            $code = 'KRC' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
        } while ($this->db->where('order_code', $code)->count_all_results('orders') > 0);
        return $code;
    }

    /** Jumlah tiket yang sedang dipesan (pending, belum kedaluwarsa) per tipe tiket */
    public function reserved_qty($ticket_type_id)
    {
        $row = $this->db->select_sum('oi.qty', 'qty')
            ->from('order_items oi')->join('orders o', 'o.id = oi.order_id')
            ->where('oi.ticket_type_id', $ticket_type_id)
            ->where('o.status', 'pending')->where('o.expired_at >', date('Y-m-d H:i:s'))
            ->get()->row();
        return (int) ($row ? $row->qty : 0);
    }
}
