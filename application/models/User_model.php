<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Akun pembeli. Login tidak wajib untuk membeli tiket. */
class User_model extends CI_Model
{
    public function find($id)
    {
        return $this->db->where('id', (int) $id)->get('users')->row();
    }

    public function find_by_email($email)
    {
        return $this->db->where('email', strtolower(trim($email)))->get('users')->row();
    }

    public function create($name, $email, $phone, $password)
    {
        $this->db->insert('users', array(
            'name'     => $name,
            'email'    => strtolower(trim($email)),
            'phone'    => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ));
        return $this->db->insert_id();
    }

    public function update_password($id, $password)
    {
        return $this->db->where('id', (int) $id)->update('users', array('password' => password_hash($password, PASSWORD_DEFAULT)));
    }

    public function update_profile($id, array $data)
    {
        return $this->db->where('id', (int) $id)->update('users', $data);
    }

    public function touch_login($id)
    {
        $this->db->where('id', (int) $id)->update('users', array('last_login_at' => date('Y-m-d H:i:s')));
    }

    /** Pesanan yang dibuat tanpa login, tapi memakai email yang sama, ikut masuk ke akun */
    public function claim_orders($user_id, $email)
    {
        $this->db->where('buyer_email', strtolower(trim($email)))->where('user_id', NULL)
            ->update('orders', array('user_id' => (int) $user_id));
        return $this->db->affected_rows();
    }

    /** Riwayat pesanan: berdasarkan akun maupun email yang sama */
    public function orders($user_id, $email, $limit = 0, $offset = 0)
    {
        $this->db->select('o.*, e.title AS event_title, e.slug AS event_slug, e.thumbnail, e.venue, e.city, e.start_date')
            ->from('orders o')->join('events e', 'e.id = o.event_id')
            ->group_start()->where('o.user_id', (int) $user_id)->or_where('o.buyer_email', $email)->group_end()
            ->order_by('o.id', 'DESC');
        if ($limit) $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function count_orders($user_id, $email)
    {
        return $this->db->from('orders o')
            ->group_start()->where('o.user_id', (int) $user_id)->or_where('o.buyer_email', $email)->group_end()
            ->count_all_results();
    }

    public function stats($user_id, $email)
    {
        $row = $this->db->select("COUNT(*) AS orders, COALESCE(SUM(CASE WHEN status='paid' THEN ticket_qty ELSE 0 END),0) AS tickets,
                COALESCE(SUM(CASE WHEN status='paid' THEN total ELSE 0 END),0) AS spent,
                SUM(CASE WHEN status='pending' AND expired_at > NOW() THEN 1 ELSE 0 END) AS pending", FALSE)
            ->from('orders')
            ->group_start()->where('user_id', (int) $user_id)->or_where('buyer_email', $email)->group_end()
            ->get()->row();

        $upcoming = $this->db->select('COUNT(*) AS n', FALSE)->from('orders o')->join('events e', 'e.id = o.event_id')
            ->where('o.status', 'paid')->where('e.start_date >=', date('Y-m-d'))
            ->group_start()->where('o.user_id', (int) $user_id)->or_where('o.buyer_email', $email)->group_end()
            ->get()->row();

        return array(
            'orders'   => (int) $row->orders,
            'tickets'  => (int) $row->tickets,
            'spent'    => (int) $row->spent,
            'pending'  => (int) $row->pending,
            'upcoming' => (int) $upcoming->n,
        );
    }
}
