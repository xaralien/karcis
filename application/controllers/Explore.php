<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Explore extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Event_model', 'Category_model'));
    }

    public function index()
    {
        $filter = array(
            'q'        => trim((string) $this->input->get('q', TRUE)),
            'kategori' => (string) $this->input->get('kategori', TRUE),
            'kota'     => (string) $this->input->get('kota', TRUE),
            'sort'     => (string) $this->input->get('sort', TRUE),
        );
        $per_page = $this->view_mode === 'mobile' ? 8 : 12;
        $page     = max(1, (int) $this->input->get('page'));
        $total    = $this->Event_model->count_search($filter);

        $this->render('explore/index', array(
            'title'      => 'Jelajahi Event',
            'events'     => $this->Event_model->search($filter, $per_page, ($page - 1) * $per_page),
            'total'      => $total,
            'filter'     => $filter,
            'categories' => $this->Category_model->all_with_count(),
            'cities'     => $this->Event_model->cities(),
            'pages'      => $this->fmt->pages($total, $per_page, $page, $filter),
            'active_nav' => 'explore',
        ));
    }
}
