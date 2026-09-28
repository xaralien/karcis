<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Event_model');
    }

    /** Admin yang sedang login boleh melihat event draft/selesai sebagai pratinjau */
    protected function is_admin()
    {
        return (bool) $this->session->userdata('admin_id');
    }

    /** /event/detail/{slug} */
    public function detail($slug = '')
    {
        $event = $this->Event_model->get_by_slug($slug, $this->is_admin());
        if ( ! $event) show_404();

        $this->render('event/detail', array(
            'title'        => $event->title,
            'event'        => $event,
            'schedules'    => $this->Event_model->schedules($event->id),
            'ticket_types' => $this->Event_model->ticket_types($event->id),
            'facilities'   => $this->Event_model->facilities($event->id),
            'guests'       => $this->Event_model->guests($event->id),
            'gallery'      => $this->Event_model->gallery($event->id),
            'preview'      => $event->status !== 'published',
            'active_nav'   => 'explore',
        ));
    }

    /** Langkah 1 pembelian: /event/tickets/{slug} */
    public function tickets($slug = '')
    {
        $event = $this->Event_model->get_by_slug($slug, $this->is_admin());
        if ( ! $event) show_404();
        if ($event->status !== 'published') {
            $this->session->set_flashdata('error', 'Event ini belum tayang, jadi tiketnya belum bisa dibeli. Ubah status menjadi Tayang di admin.');
            $this->go('event/detail/' . $event->slug);
        }

        // Pertahankan pilihan sebelumnya bila pembeli kembali dari halaman rincian
        $cart = $this->session->userdata('cart');
        $selected = ($cart && (int) $cart['event_id'] === (int) $event->id) ? $cart['qty'] : array();

        $this->render('event/tickets', array(
            'title'        => 'Pilih Tiket ' . $event->title,
            'event'        => $event,
            'schedules'    => $this->Event_model->schedules($event->id),
            'ticket_types' => $this->Event_model->ticket_types($event->id),
            'selected'     => $selected,
            'active_nav'   => 'explore',
        ));
    }
}
