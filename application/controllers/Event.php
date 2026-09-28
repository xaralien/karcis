<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Event extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Event_model');
    }

    /** /event/detail/{slug} */
    public function detail($slug = '')
    {
        $event = $this->Event_model->get_by_slug($slug);
        if ( ! $event) show_404();

        $this->render('event/detail', array(
            'title'        => $event->title,
            'event'        => $event,
            'schedules'    => $this->Event_model->schedules($event->id),
            'ticket_types' => $this->Event_model->ticket_types($event->id),
            'facilities'   => $this->Event_model->facilities($event->id),
            'guests'       => $this->Event_model->guests($event->id),
            'gallery'      => $this->Event_model->gallery($event->id),
            'active_nav'   => 'explore',
        ));
    }

    /** Langkah 1 pembelian: /event/tickets/{slug} */
    public function tickets($slug = '')
    {
        $event = $this->Event_model->get_by_slug($slug);
        if ( ! $event) show_404();

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
