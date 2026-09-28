<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event_model extends CI_Model
{
    protected function base($any_status = FALSE)
    {
        return $this->db->select('e.*, c.name AS category_name, c.slug AS category_slug,
                (SELECT MIN(price) FROM ticket_types tt WHERE tt.event_id = e.id) AS min_price,
                (SELECT MAX(event_date) FROM event_schedules s WHERE s.event_id = e.id) AS end_date,
                (SELECT COUNT(*) FROM event_schedules s2 WHERE s2.event_id = e.id) AS total_days', FALSE)
            ->from('events e')
            ->join('categories c', 'c.id = e.category_id')
            ->where($any_status ? '1 = 1' : "e.status = 'published'", NULL, FALSE);
    }

    public function featured($limit = 5)
    {
        return $this->base()->where('e.is_featured', 1)->where('e.start_date >=', date('Y-m-d'))
            ->order_by('e.start_date', 'ASC')->limit($limit)->get()->result();
    }

    public function upcoming($limit = 6)
    {
        return $this->base()->where('e.start_date >=', date('Y-m-d'))
            ->order_by('e.start_date', 'ASC')->limit($limit)->get()->result();
    }

    public function search(array $f, $limit, $offset)
    {
        $this->apply_filter($f);
        $sort = isset($f['sort']) ? $f['sort'] : 'terdekat';
        if ($sort === 'terbaru') $this->db->order_by('e.created_at', 'DESC');
        elseif ($sort === 'az')  $this->db->order_by('e.title', 'ASC');
        else                     $this->db->order_by('e.start_date', 'ASC');
        return $this->db->limit($limit, $offset)->get()->result();
    }

    public function count_search(array $f)
    {
        $this->apply_filter($f);
        return $this->db->get()->num_rows();
    }

    protected function apply_filter(array $f)
    {
        $this->base();
        if ( ! empty($f['q'])) {
            $this->db->group_start()
                ->like('e.title', $f['q'])->or_like('e.venue', $f['q'])->or_like('e.organizer', $f['q'])
                ->group_end();
        }
        if ( ! empty($f['kategori'])) $this->db->where('c.slug', $f['kategori']);
        if ( ! empty($f['kota']))     $this->db->where('e.city', $f['kota']);
    }

    /** $any_status TRUE dipakai untuk pratinjau admin (draft & selesai ikut terlihat) */
    public function get_by_slug($slug, $any_status = FALSE)
    {
        return $this->base($any_status)->where('e.slug', $slug)->get()->row();
    }

    /** Tanpa filter status: untuk tiket, email, dan cetak */
    public function get_any($id)
    {
        return $this->db->select('e.*, c.name AS category_name')
            ->from('events e')->join('categories c', 'c.id = e.category_id', 'left')
            ->where('e.id', (int) $id)->get()->row();
    }

    public function get($id)
    {
        return $this->base()->where('e.id', (int) $id)->get()->row();
    }

    public function cities()
    {
        return $this->db->distinct()->select('city')->where('status', 'published')
            ->order_by('city')->get('events')->result();
    }

    public function schedules($event_id)
    {
        return $this->db->where('event_id', $event_id)->order_by('event_date, start_time')->get('event_schedules')->result();
    }

    public function ticket_types($event_id)
    {
        return $this->db->select('*, (quota - sold) AS available', FALSE)
            ->where('event_id', $event_id)->order_by('sort_order')->get('ticket_types')->result();
    }

    public function facilities($event_id)
    {
        return $this->db->where('event_id', $event_id)->get('event_facilities')->result();
    }

    public function guests($event_id)
    {
        return $this->db->where('event_id', $event_id)->get('event_guests')->result();
    }

    public function gallery($event_id)
    {
        return $this->db->where('event_id', $event_id)->get('event_gallery')->result();
    }
}
