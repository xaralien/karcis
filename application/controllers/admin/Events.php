<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kelola event beserta jadwal, kategori tiket, fasilitas, bintang tamu, dan galeri.
 */
class Events extends Admin_Controller
{
    /** Error tambahan di luar form_validation (slug bentrok, upload gagal) */
    protected $field_errors = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }

    public function index()
    {
        $f = array('q' => trim((string) $this->input->get('q', TRUE)), 'status' => (string) $this->input->get('status', TRUE));
        $this->render('events/index', array('title' => 'Event', 'events' => $this->Admin_model->events($f), 'filter' => $f, 'nav' => 'events'));
    }

    /* ---------------- Info utama ---------------- */
    public function create()
    {
        $event = (object) array(
            'id' => 0, 'category_id' => '', 'title' => '', 'slug' => '', 'event_type' => '', 'organizer' => '',
            'short_desc' => '', 'description' => '', 'banner' => '', 'banner_mobile' => NULL, 'thumbnail' => '', 'venue' => '',
            'address' => '', 'city' => '', 'maps_url' => '', 'start_date' => date('Y-m-d', strtotime('+30 days')),
            'is_featured' => 0, 'featured_order' => NULL, 'status' => 'draft',
        );
        if ($this->input->method() === 'post') {
            if ($id = $this->save_event(0)) {
                $this->session->set_flashdata('success', 'Event dibuat. Lanjutkan dengan menambah jadwal dan kategori tiket.');
                $this->go('admin/events/edit/' . $id . '?tab=jadwal');
            }
        }
        $this->load->vars('field_errors', $this->field_errors);
        $this->render('events/create', array('title' => 'Tambah Event', 'event' => $event, 'categories' => $this->Admin_model->categories(), 'nav' => 'events'));
    }

    public function edit($id = 0)
    {
        $event = $this->Admin_model->event($id);
        if ( ! $event) show_404();

        if ($this->input->method() === 'post') {
            if ($this->save_event($event->id)) {
                $this->session->set_flashdata('success', 'Info event disimpan.');
                $this->go('admin/events/edit/' . $event->id);
            }
        }

        $this->load->vars('field_errors', $this->field_errors);
        $this->render('events/edit', array(
            'title'        => 'Edit Event',
            'event'        => $event,
            'categories'   => $this->Admin_model->categories(),
            'schedules'    => $this->Admin_model->children('event_schedules', $event->id, 'event_date, start_time'),
            'ticket_types' => $this->Admin_model->children('ticket_types', $event->id, 'sort_order, id'),
            'facilities'   => $this->Admin_model->children('event_facilities', $event->id),
            'guests'       => $this->Admin_model->children('event_guests', $event->id),
            'gallery'      => $this->Admin_model->children('event_gallery', $event->id),
            'has_orders'   => $this->Admin_model->event_has_orders($event->id),
            'tab'          => $this->input->get('tab', TRUE) ?: 'info',
            'nav'          => 'events',
        ));
    }

    protected function save_event($id)
    {
        $rules = array(
            array('category_id', 'Kategori', 'required|integer'),
            array('title', 'Judul', 'required|trim|max_length[160]'),
            array('slug', 'Slug', 'trim|max_length[180]|alpha_dash'),
            array('event_type', 'Tipe event', 'required|trim|max_length[60]'),
            array('organizer', 'Penyelenggara', 'required|trim|max_length[120]'),
            array('short_desc', 'Ringkasan', 'required|trim|max_length[255]'),
            array('description', 'Deskripsi', 'required|trim'),
            array('venue', 'Venue', 'required|trim|max_length[160]'),
            array('address', 'Alamat', 'required|trim|max_length[255]'),
            array('city', 'Kota', 'required|trim|max_length[80]'),
            array('maps_url', 'Link peta', 'trim|max_length[255]|valid_url'),
            array('start_date', 'Tanggal mulai', 'required'),
            array('status', 'Status', 'required|in_list[draft,published,ended]'),
        );
        foreach ($rules as $r) $this->form_validation->set_rules($r[0], $r[1], $r[2]);
        $this->form_validation->set_message('required', '{field} wajib diisi.');
        if ( ! $this->form_validation->run()) return FALSE;

        $old = $id ? $this->Admin_model->event($id) : NULL;
        $slug_in = $this->input->post('slug');
        if ($slug_in && $this->Admin_model->slug_exists($slug_in, $id)) {
            $this->field_errors['slug'] = 'Slug sudah dipakai event lain.';
            return FALSE;
        }

        $row = array(
            'category_id' => (int) $this->input->post('category_id'),
            'title'       => $this->input->post('title', TRUE),
            'slug'        => $slug_in ? strtolower($slug_in) : $this->Admin_model->unique_slug($this->input->post('title'), $id),
            'event_type'  => $this->input->post('event_type', TRUE),
            'organizer'   => $this->input->post('organizer', TRUE),
            'short_desc'  => $this->input->post('short_desc', TRUE),
            'description' => $this->input->post('description', TRUE),
            'venue'       => $this->input->post('venue', TRUE),
            'address'     => $this->input->post('address', TRUE),
            'city'        => $this->input->post('city', TRUE),
            'maps_url'    => $this->input->post('maps_url', TRUE) ?: NULL,
            'start_date'  => $this->input->post('start_date'),
            'is_featured' => $this->input->post('is_featured') ? 1 : 0,
            'featured_order' => ($this->input->post('is_featured') && trim((string) $this->input->post('featured_order')) !== '')
                ? max(1, (int) $this->input->post('featured_order')) : NULL,
            'status'      => $this->input->post('status'),
        );

        foreach (array('banner', 'thumbnail') as $field) {
            $path = $this->upload_image($field);
            if ($path === FALSE) {
                $this->field_errors[$field] = ucfirst($field) . ': ' . $this->upload_error;
                return FALSE;
            }
            if ($path) {
                $row[$field] = $path;
                if ($old) $this->delete_local_image($old->$field);
            } elseif ( ! $old) {
                $this->field_errors[$field] = 'Gambar ' . $field . ' wajib diunggah.';
                return FALSE;
            }
        }

        // Banner mobile: opsional. Kosong = carousel mobile memakai banner utama.
        $bm = $this->upload_image('banner_mobile');
        if ($bm === FALSE) {
            $this->field_errors['banner_mobile'] = 'Banner mobile: ' . $this->upload_error;
            return FALSE;
        }
        if ($bm) {
            $row['banner_mobile'] = $bm;
            if ($old) $this->delete_local_image($old->banner_mobile);
        } elseif ($old && $old->banner_mobile && $this->input->post('remove_banner_mobile')) {
            $this->delete_local_image($old->banner_mobile);
            $row['banner_mobile'] = NULL;
        }

        if ($row['status'] === 'published' && $id && ! $this->db->where('event_id', $id)->count_all_results('ticket_types')) {
            $this->session->set_flashdata('error', 'Event disimpan sebagai draft karena belum punya kategori tiket.');
            $row['status'] = 'draft';
        }

        if ($id) {
            $this->db->where('id', $id)->update('events', $row);
            $this->Admin_model->sync_start_date($id);
            return $id;
        }
        if ($row['status'] === 'published') $row['status'] = 'draft'; // event baru belum punya tiket
        $this->db->insert('events', $row);
        return $this->db->insert_id();
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') $this->go('admin/events');
        $event = $this->Admin_model->event($id);
        if ( ! $event) show_404();

        if ($this->Admin_model->event_has_orders($id)) {
            $this->session->set_flashdata('error', 'Event sudah punya pesanan dan tidak bisa dihapus. Ubah statusnya menjadi Draft atau Selesai.');
            $this->go('admin/events/edit/' . $id);
        }
        $this->delete_local_image($event->banner);
        $this->delete_local_image($event->thumbnail);
        $this->delete_local_image($event->banner_mobile);
        foreach ($this->Admin_model->children('event_guests', $id) as $g) $this->delete_local_image($g->photo);
        foreach ($this->Admin_model->children('event_gallery', $id) as $g) $this->delete_local_image($g->image);
        $this->db->where('id', $id)->delete('events');

        $this->session->set_flashdata('success', 'Event "' . $event->title . '" dihapus.');
        $this->go('admin/events');
    }

    /* ---------------- Jadwal ---------------- */
    public function schedule_save($event_id, $id = 0)
    {
        $event = $this->require_event($event_id);
        $row = array(
            'event_id'   => $event->id,
            'label'      => trim((string) $this->input->post('label', TRUE)),
            'event_date' => $this->input->post('event_date'),
            'start_time' => $this->input->post('start_time'),
            'end_time'   => $this->input->post('end_time'),
            'venue'      => trim((string) $this->input->post('venue', TRUE)) ?: $event->venue,
        );
        if ( ! $row['label'] || ! strtotime($row['event_date']) || ! $row['start_time'] || ! $row['end_time']) {
            return $this->back($event->id, 'jadwal', 'Label, tanggal, jam mulai, dan jam selesai wajib diisi.');
        }
        $this->upsert('event_schedules', $id, $event->id, $row);
        $this->Admin_model->sync_start_date($event->id);
        $this->back($event->id, 'jadwal', NULL, 'Jadwal disimpan.');
    }

    public function schedule_delete($event_id, $id)
    {
        $event = $this->require_event($event_id);
        $this->db->where('id', (int) $id)->where('event_id', $event->id)->delete('event_schedules');
        $this->Admin_model->sync_start_date($event->id);
        $this->back($event->id, 'jadwal', NULL, 'Jadwal dihapus.');
    }

    /* ---------------- Kategori tiket ---------------- */
    public function ticket_save($event_id, $id = 0)
    {
        $event = $this->require_event($event_id);
        $row = array(
            'event_id'    => $event->id,
            'name'        => trim((string) $this->input->post('name', TRUE)),
            'description' => trim((string) $this->input->post('description', TRUE)),
            'price'       => (int) preg_replace('/\D/', '', (string) $this->input->post('price')),
            'quota'       => (int) $this->input->post('quota'),
            'sort_order'  => (int) $this->input->post('sort_order'),
        );
        if ( ! $row['name'] || $row['price'] < 1 || $row['quota'] < 1) {
            return $this->back($event->id, 'tiket', 'Nama, harga, dan kuota tiket wajib diisi dengan benar.');
        }
        if ($id) {
            $old = $this->Admin_model->child('ticket_types', $id);
            if ($old && $row['quota'] < (int) $old->sold) {
                return $this->back($event->id, 'tiket', "Kuota tidak boleh kurang dari tiket terjual ({$old->sold}).");
            }
        }
        $this->upsert('ticket_types', $id, $event->id, $row);
        $this->back($event->id, 'tiket', NULL, 'Kategori tiket disimpan.');
    }

    public function ticket_delete($event_id, $id)
    {
        $event = $this->require_event($event_id);
        if ($this->Admin_model->ticket_type_in_use($id)) {
            return $this->back($event->id, 'tiket', 'Kategori tiket sudah pernah dipesan dan tidak bisa dihapus. Kurangi kuotanya sama dengan jumlah terjual agar tidak bisa dibeli lagi.');
        }
        $this->db->where('id', (int) $id)->where('event_id', $event->id)->delete('ticket_types');
        $this->back($event->id, 'tiket', NULL, 'Kategori tiket dihapus.');
    }

    /* ---------------- Fasilitas ---------------- */
    public function facility_save($event_id, $id = 0)
    {
        $event = $this->require_event($event_id);
        $name = trim((string) $this->input->post('name', TRUE));
        if ( ! $name) return $this->back($event->id, 'fasilitas', 'Nama fasilitas wajib diisi.');
        $icon = preg_replace('/[^a-z0-9-]/', '', (string) $this->input->post('icon')) ?: 'bi-check2-circle';
        $this->upsert('event_facilities', $id, $event->id, array('event_id' => $event->id, 'name' => $name, 'icon' => $icon));
        $this->back($event->id, 'fasilitas', NULL, 'Fasilitas disimpan.');
    }

    public function facility_delete($event_id, $id)
    {
        $event = $this->require_event($event_id);
        $this->db->where('id', (int) $id)->where('event_id', $event->id)->delete('event_facilities');
        $this->back($event->id, 'fasilitas', NULL, 'Fasilitas dihapus.');
    }

    /* ---------------- Bintang tamu ---------------- */
    public function guest_save($event_id, $id = 0)
    {
        $event = $this->require_event($event_id);
        $row = array('event_id' => $event->id, 'name' => trim((string) $this->input->post('name', TRUE)), 'role' => trim((string) $this->input->post('role', TRUE)));
        if ( ! $row['name'] || ! $row['role']) return $this->back($event->id, 'tamu', 'Nama dan peran bintang tamu wajib diisi.');

        $old  = $id ? $this->Admin_model->child('event_guests', $id) : NULL;
        $path = $this->upload_image('photo');
        if ($path === FALSE) return $this->back($event->id, 'tamu', 'Foto: ' . $this->upload_error);
        if ($path) { $row['photo'] = $path; if ($old) $this->delete_local_image($old->photo); }
        elseif ( ! $old) return $this->back($event->id, 'tamu', 'Foto bintang tamu wajib diunggah.');

        $this->upsert('event_guests', $id, $event->id, $row);
        $this->back($event->id, 'tamu', NULL, 'Bintang tamu disimpan.');
    }

    public function guest_delete($event_id, $id)
    {
        $event = $this->require_event($event_id);
        $g = $this->Admin_model->child('event_guests', $id);
        if ($g && (int) $g->event_id === (int) $event->id) {
            $this->delete_local_image($g->photo);
            $this->db->where('id', $g->id)->delete('event_guests');
        }
        $this->back($event->id, 'tamu', NULL, 'Bintang tamu dihapus.');
    }

    /* ---------------- Galeri ---------------- */
    public function gallery_upload($event_id)
    {
        $event = $this->require_event($event_id);
        $path = $this->upload_image('image');
        if ( ! $path) return $this->back($event->id, 'galeri', $path === FALSE ? 'Gambar: ' . $this->upload_error : 'Pilih gambar dulu.');
        $this->db->insert('event_gallery', array('event_id' => $event->id, 'image' => $path, 'caption' => trim((string) $this->input->post('caption', TRUE)) ?: NULL));
        $this->back($event->id, 'galeri', NULL, 'Gambar ditambahkan ke galeri.');
    }

    public function gallery_delete($event_id, $id)
    {
        $event = $this->require_event($event_id);
        $g = $this->Admin_model->child('event_gallery', $id);
        if ($g && (int) $g->event_id === (int) $event->id) {
            $this->delete_local_image($g->image);
            $this->db->where('id', $g->id)->delete('event_gallery');
        }
        $this->back($event->id, 'galeri', NULL, 'Gambar dihapus.');
    }

    /* ---------------- Util ---------------- */
    protected function require_event($id)
    {
        if ($this->input->method() !== 'post') $this->go('admin/events/edit/' . (int) $id);
        $event = $this->Admin_model->event($id);
        if ( ! $event) show_404();
        return $event;
    }

    protected function upsert($table, $id, $event_id, array $row)
    {
        if ($id) $this->db->where('id', (int) $id)->where('event_id', (int) $event_id)->update($table, $row);
        else     $this->db->insert($table, $row);
    }

    protected function back($event_id, $tab, $error = NULL, $success = NULL)
    {
        if ($error)   $this->session->set_flashdata('error', $error);
        if ($success) $this->session->set_flashdata('success', $success);
        $this->go('admin/events/edit/' . (int) $event_id . '?tab=' . $tab);
    }
}
