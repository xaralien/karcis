<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Order_model', 'Event_model', 'Ticket_model'));
    }

    /** /ticket/show/{kode_pesanan}/{token} — link ini juga dikirim ke email */
    public function show($code = '', $token = '')
    {
        $order = $this->authorize($code, $token);

        $this->render('ticket/show', array(
            'title'     => 'Tiket ' . $order->order_code,
            'order'     => $order,
            'event'     => $this->Event_model->get_any($order->event_id),
            'schedules' => $this->Event_model->schedules($order->event_id),
            'tickets'   => $order->status === 'paid' ? $this->Order_model->tickets($order->id) : array(),
            'items'     => $this->Order_model->items($order->id),
            'print_url' => $this->fmt->url('ticket/printout/' . $order->order_code . '/' . $order->access_token),
        ));
    }

    /**
     * Halaman cetak, satu tiket per halaman A4.
     * /ticket/printout/{kode}/{token}            -> semua tiket
     * /ticket/printout/{kode}/{token}?id=TKT-xxx -> satu tiket
     */
    public function printout($code = '', $token = '')
    {
        $order = $this->authorize($code, $token);
        if ($order->status !== 'paid') $this->go('ticket/show/' . $order->order_code . '/' . $order->access_token);

        $all     = $this->Order_model->tickets($order->id);
        $tickets = $all;
        $only    = strtoupper(trim((string) $this->input->get('id', TRUE)));
        if ($only) {
            $tickets = array_values(array_filter($all, function ($t) use ($only) { return $t->ticket_code === $only; }));
            if ( ! $tickets) show_404();
        }

        $this->load->view('shared/ticket_print', array(
            'app_name'  => $this->config->item('app_name'),
            'order'     => $order,
            'event'     => $this->Event_model->get_any($order->event_id),
            'schedules' => $this->Event_model->schedules($order->event_id),
            'tickets'   => $tickets,
            'total'     => count($all),
            'all_codes' => array_map(function ($t) { return $t->ticket_code; }, $all),
            'print_url' => $this->fmt->url('ticket/printout/' . $order->order_code . '/' . $order->access_token),
            'back_url'  => $this->fmt->url('ticket/show/' . $order->order_code . '/' . $order->access_token),
            'auto'      => (bool) $this->input->get('auto'),
        ));
    }

    protected function authorize($code, $token)
    {
        $order = $this->Order_model->get_by_code($code);
        if ( ! $order || ! hash_equals($order->access_token, (string) $token)) show_404();

        if ($order->status === 'paid' && ! $this->Order_model->tickets($order->id)) {
            $this->Ticket_model->repair($order->id);
        }
        return $order;
    }
}
