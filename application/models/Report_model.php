<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Sumber data untuk laporan: pembeli, audit penjualan, dan kehadiran */
class Report_model extends CI_Model
{
    protected function scope(array $f, $alias = 'o')
    {
        if ( ! empty($f['event_id'])) $this->db->where($alias . '.event_id', (int) $f['event_id']);
        if ( ! empty($f['from']))     $this->db->where($alias . '.created_at >=', $f['from'] . ' 00:00:00');
        if ( ! empty($f['to']))       $this->db->where($alias . '.created_at <=', $f['to'] . ' 23:59:59');
    }

    /* ============ 1. Data pembeli (remarketing) ============ */
    /** Satu baris per pembeli, digabung berdasarkan email */
    public function buyers(array $f)
    {
        $this->db->select("o.buyer_email, MAX(o.buyer_name) AS buyer_name, MAX(o.buyer_phone) AS buyer_phone,
                MAX(u.id) IS NOT NULL AS has_account,
                COUNT(*) AS orders, SUM(o.ticket_qty) AS tickets, SUM(o.total) AS spent,
                MIN(o.paid_at) AS first_order, MAX(o.paid_at) AS last_order,
                GROUP_CONCAT(DISTINCT e.title ORDER BY e.title SEPARATOR ' | ') AS events,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS categories,
                GROUP_CONCAT(DISTINCT e.city ORDER BY e.city SEPARATOR ', ') AS cities,
                (SELECT COUNT(*) FROM tickets t JOIN orders o2 ON o2.id = t.order_id
                    WHERE o2.buyer_email = o.buyer_email AND t.is_checked_in = 1) AS attended", FALSE)
            ->from('orders o')
            ->join('events e', 'e.id = o.event_id')
            ->join('categories c', 'c.id = e.category_id', 'left')
            ->join('users u', 'u.email = o.buyer_email', 'left')
            ->where('o.status', 'paid');
        $this->scope($f);
        return $this->db->group_by('o.buyer_email')->order_by('spent', 'DESC')->get()->result();
    }

    /* ============ 2. Audit penjualan ============ */
    public function audit_orders(array $f)
    {
        $this->db->select('o.*, e.title AS event_title')->from('orders o')->join('events e', 'e.id = o.event_id');
        if ( ! empty($f['status'])) $this->db->where('o.status', $f['status']);
        $this->scope($f);
        return $this->db->order_by('o.id')->get()->result();
    }

    public function audit_summary(array $f)
    {
        $this->db->select("COUNT(*) AS orders,
                SUM(status = 'paid') AS paid, SUM(status = 'pending') AS pending,
                SUM(status = 'failed') AS failed, SUM(status = 'expired') AS expired,
                COALESCE(SUM(CASE WHEN status='paid' THEN ticket_qty END),0) AS tickets,
                COALESCE(SUM(CASE WHEN status='paid' THEN subtotal END),0) AS ticket_revenue,
                COALESCE(SUM(CASE WHEN status='paid' THEN service_fee END),0) AS service_fee,
                COALESCE(SUM(CASE WHEN status='paid' THEN transaction_fee END),0) AS transaction_fee,
                COALESCE(SUM(CASE WHEN status='paid' THEN total END),0) AS gross", FALSE)
            ->from('orders o');
        $this->scope($f);
        return $this->db->get()->row();
    }

    /** Rekap per kategori tiket, untuk mencocokkan jumlah terjual dengan uang masuk */
    public function audit_per_type(array $f)
    {
        $this->db->select("e.title AS event_title, tt.name AS ticket_name, tt.price, tt.quota, tt.sold,
                COALESCE(SUM(oi.qty),0) AS qty_paid, COALESCE(SUM(oi.qty * oi.price),0) AS revenue", FALSE)
            ->from('ticket_types tt')
            ->join('events e', 'e.id = tt.event_id')
            ->join('order_items oi', 'oi.ticket_type_id = tt.id', 'left')
            ->join('orders o', "o.id = oi.order_id AND o.status = 'paid'", 'left');
        if ( ! empty($f['event_id'])) $this->db->where('tt.event_id', (int) $f['event_id']);
        return $this->db->group_by('tt.id')->order_by('e.title, tt.sort_order')->get()->result();
    }

    /* ============ 3. Data kehadiran ============ */
    public function attendance(array $f)
    {
        $this->db->select('t.ticket_code, t.is_checked_in, t.checked_in_at, tt.name AS ticket_name,
                o.order_code, o.buyer_name, o.buyer_email, o.buyer_phone, e.title AS event_title, a.name AS petugas')
            ->from('tickets t')
            ->join('ticket_types tt', 'tt.id = t.ticket_type_id')
            ->join('orders o', 'o.id = t.order_id')
            ->join('events e', 'e.id = t.event_id')
            ->join('admins a', 'a.id = t.checked_in_by', 'left');
        if ( ! empty($f['event_id'])) $this->db->where('t.event_id', (int) $f['event_id']);
        if (isset($f['hadir']) && $f['hadir'] !== '') $this->db->where('t.is_checked_in', (int) $f['hadir']);
        return $this->db->order_by('t.is_checked_in DESC, t.checked_in_at ASC, t.ticket_code')->get()->result();
    }

    public function attendance_summary(array $f)
    {
        $this->db->select("COUNT(*) AS total, SUM(t.is_checked_in) AS hadir,
                COUNT(*) - SUM(t.is_checked_in) AS belum,
                MIN(t.checked_in_at) AS masuk_pertama, MAX(t.checked_in_at) AS masuk_terakhir", FALSE)
            ->from('tickets t');
        if ( ! empty($f['event_id'])) $this->db->where('t.event_id', (int) $f['event_id']);
        return $this->db->get()->row();
    }

    /** Kehadiran per kategori tiket, untuk laporan akhir ke promotor */
    public function attendance_per_type(array $f)
    {
        $this->db->select("tt.name AS ticket_name, COUNT(*) AS total, SUM(t.is_checked_in) AS hadir", FALSE)
            ->from('tickets t')->join('ticket_types tt', 'tt.id = t.ticket_type_id');
        if ( ! empty($f['event_id'])) $this->db->where('t.event_id', (int) $f['event_id']);
        return $this->db->group_by('tt.id')->order_by('tt.sort_order')->get()->result();
    }

    /** Grafik jam kedatangan */
    public function attendance_by_hour(array $f)
    {
        $this->db->select("DATE_FORMAT(t.checked_in_at, '%Y-%m-%d %H:00') AS jam, COUNT(*) AS n", FALSE)
            ->from('tickets t')->where('t.is_checked_in', 1);
        if ( ! empty($f['event_id'])) $this->db->where('t.event_id', (int) $f['event_id']);
        return $this->db->group_by('jam')->order_by('jam')->get()->result();
    }
}
