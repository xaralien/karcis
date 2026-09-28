<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Query khusus panel admin (tanpa filter status published) */
class Admin_model extends CI_Model
{
    /* ================= Akun admin ================= */
    public function find_admin($id)
    {
        return $this->db->where('id', (int) $id)->get('admins')->row();
    }

    public function find_admin_by_email($email)
    {
        return $this->db->where('email', strtolower(trim($email)))->get('admins')->row();
    }

    public function admins()
    {
        return $this->db->order_by('id')->get('admins')->result();
    }

    /* ================= Dashboard ================= */
    public function stats()
    {
        $paid = $this->db->select('COUNT(*) AS orders, COALESCE(SUM(total),0) AS revenue, COALESCE(SUM(subtotal),0) AS ticket_revenue,
                COALESCE(SUM(service_fee + transaction_fee),0) AS fee_revenue, COALESCE(SUM(ticket_qty),0) AS tickets', FALSE)
            ->where('status', 'paid')->get('orders')->row();

        return array(
            'revenue'        => (int) $paid->revenue,
            'ticket_revenue' => (int) $paid->ticket_revenue,
            'fee_revenue'    => (int) $paid->fee_revenue,
            'paid_orders'    => (int) $paid->orders,
            'tickets_sold'   => (int) $paid->tickets,
            'pending_orders' => $this->db->where('status', 'pending')->where('expired_at >', date('Y-m-d H:i:s'))->count_all_results('orders'),
            'checked_in'     => $this->db->where('is_checked_in', 1)->count_all_results('tickets'),
            'active_events'  => $this->db->where('status', 'published')->where('start_date >=', date('Y-m-d'))->count_all_results('events'),
        );
    }

    public function daily_sales($days = 14)
    {
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $rows = $this->db->select('DATE(paid_at) AS d, SUM(total) AS revenue, SUM(ticket_qty) AS tickets', FALSE)
            ->where('status', 'paid')->where('paid_at >=', $from . ' 00:00:00')
            ->group_by('DATE(paid_at)')->get('orders')->result();
        $map = array();
        foreach ($rows as $r) $map[$r->d] = $r;

        $out = array();
        for ($i = 0; $i < $days; $i++) {
            $d = date('Y-m-d', strtotime($from . " +$i days"));
            $out[] = array('date' => $d, 'revenue' => isset($map[$d]) ? (int) $map[$d]->revenue : 0, 'tickets' => isset($map[$d]) ? (int) $map[$d]->tickets : 0);
        }
        return $out;
    }

    public function sales_per_event()
    {
        return $this->db->select("e.id, e.title, e.start_date, e.status,
                (SELECT COALESCE(SUM(quota),0) FROM ticket_types tt WHERE tt.event_id = e.id) AS quota,
                (SELECT COALESCE(SUM(sold),0) FROM ticket_types tt WHERE tt.event_id = e.id) AS sold,
                (SELECT COALESCE(SUM(subtotal),0) FROM orders o WHERE o.event_id = e.id AND o.status = 'paid') AS revenue,
                (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.is_checked_in = 1) AS checked_in", FALSE)
            ->from('events e')->order_by('e.start_date', 'DESC')->get()->result();
    }

    /* ================= Event ================= */
    public function events(array $f = array())
    {
        $this->db->select("e.*, c.name AS category_name,
                (SELECT COUNT(*) FROM event_schedules s WHERE s.event_id = e.id) AS total_days,
                (SELECT COALESCE(SUM(quota),0) FROM ticket_types tt WHERE tt.event_id = e.id) AS quota,
                (SELECT COALESCE(SUM(sold),0) FROM ticket_types tt WHERE tt.event_id = e.id) AS sold", FALSE)
            ->from('events e')->join('categories c', 'c.id = e.category_id', 'left');
        if ( ! empty($f['q']))      $this->db->like('e.title', $f['q']);
        if ( ! empty($f['status'])) $this->db->where('e.status', $f['status']);
        return $this->db->order_by('e.start_date', 'DESC')->get()->result();
    }

    public function event($id)
    {
        return $this->db->where('id', (int) $id)->get('events')->row();
    }

    public function slug_exists($slug, $except_id = 0)
    {
        return $this->db->where('slug', $slug)->where('id !=', (int) $except_id)->count_all_results('events') > 0;
    }

    public function unique_slug($text, $except_id = 0)
    {
        $base = $this->fmt->slugify($text) ?: 'event';
        $slug = $base; $i = 2;
        while ($this->slug_exists($slug, $except_id)) $slug = $base . '-' . $i++;
        return $slug;
    }

    public function event_has_orders($id)
    {
        return $this->db->where('event_id', (int) $id)->count_all_results('orders') > 0;
    }

    /** start_date event mengikuti tanggal jadwal paling awal */
    public function sync_start_date($event_id)
    {
        $row = $this->db->select_min('event_date', 'd')->where('event_id', $event_id)->get('event_schedules')->row();
        if ($row && $row->d) {
            $this->db->where('id', $event_id)->update('events', array('start_date' => $row->d));
        }
    }

    public function children($table, $event_id, $order = 'id')
    {
        return $this->db->where('event_id', (int) $event_id)->order_by($order)->get($table)->result();
    }

    public function child($table, $id)
    {
        return $this->db->where('id', (int) $id)->get($table)->row();
    }

    public function ticket_type_in_use($id)
    {
        return $this->db->where('ticket_type_id', (int) $id)->count_all_results('order_items') > 0;
    }

    /* ================= Kategori ================= */
    public function categories()
    {
        return $this->db->select('c.*, (SELECT COUNT(*) FROM events e WHERE e.category_id = c.id) AS total', FALSE)
            ->from('categories c')->order_by('c.name')->get()->result();
    }

    /* ================= Pesanan ================= */
    /** Ringkasan untuk daftar pesanan: jumlah tiket terbit dan yang sudah dipindai */
    public function order_scan_summary(array $f)
    {
        $this->db->select("COUNT(t.id) AS issued, COALESCE(SUM(t.is_checked_in),0) AS scanned", FALSE)
            ->from('orders o')->join('events e', 'e.id = o.event_id')->join('tickets t', 't.order_id = o.id');
        $this->order_filter_only($f);
        return $this->db->get()->row();
    }

    /** Filter yang sama dengan daftar pesanan, tanpa FROM tambahan */
    protected function order_filter_only(array $f)
    {
        if ( ! empty($f['status']))   $this->db->where('o.status', $f['status']);
        if ( ! empty($f['event_id'])) $this->db->where('o.event_id', (int) $f['event_id']);
        if ( ! empty($f['from']))     $this->db->where('o.created_at >=', $f['from'] . ' 00:00:00');
        if ( ! empty($f['to']))       $this->db->where('o.created_at <=', $f['to'] . ' 23:59:59');
        if ( ! empty($f['q'])) {
            $this->db->group_start()->like('o.order_code', $f['q'])->or_like('o.buyer_email', $f['q'])
                ->or_like('o.buyer_name', $f['q'])->or_like('o.buyer_phone', $f['q'])->group_end();
        }
    }

    protected function order_filter(array $f)
    {
        $this->db->from('orders o')->join('events e', 'e.id = o.event_id');
        if ( ! empty($f['status']))   $this->db->where('o.status', $f['status']);
        if ( ! empty($f['event_id'])) $this->db->where('o.event_id', (int) $f['event_id']);
        if ( ! empty($f['from']))     $this->db->where('o.created_at >=', $f['from'] . ' 00:00:00');
        if ( ! empty($f['to']))       $this->db->where('o.created_at <=', $f['to'] . ' 23:59:59');
        if ( ! empty($f['q'])) {
            $this->db->group_start()->like('o.order_code', $f['q'])->or_like('o.buyer_email', $f['q'])
                ->or_like('o.buyer_name', $f['q'])->or_like('o.buyer_phone', $f['q'])->group_end();
        }
    }

    public function orders(array $f, $limit = 25, $offset = 0)
    {
        $this->db->select("o.*, e.title AS event_title,
                (SELECT COUNT(*) FROM tickets t WHERE t.order_id = o.id) AS tickets_issued,
                (SELECT COUNT(*) FROM tickets t WHERE t.order_id = o.id AND t.is_checked_in = 1) AS tickets_scanned", FALSE);
        $this->order_filter($f);
        $this->db->order_by('o.id', 'DESC');
        if ($limit) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_orders(array $f)
    {
        $this->order_filter($f);
        return $this->db->count_all_results();
    }

}
