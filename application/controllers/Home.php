<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Event_model', 'Category_model'));
    }

    public function index()
    {
        $this->render('home/index', array(
            'featured'   => $this->Event_model->featured(5),
            'upcoming'   => $this->Event_model->upcoming(8),
            'categories' => $this->Category_model->all_with_count(),
            'catalog'    => $this->Event_model->search(array('sort' => 'terdekat'), 8, 0),
            'active_nav' => 'home',
        ));
    }

    /** /home/mode/desktop atau /home/mode/mobile */
    public function mode($mode = 'desktop')
    {
        $this->session->set_userdata('view_mode', $mode === 'mobile' ? 'mobile' : 'desktop');
        $this->go($this->agent->referrer() ?: '');
    }
}
