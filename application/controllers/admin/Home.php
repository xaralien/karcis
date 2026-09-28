<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Admin_Controller
{
    public function index()
    {
        $this->render('dashboard', array(
            'title'   => 'Dashboard',
            'stats'   => $this->Admin_model->stats(),
            'daily'   => $this->Admin_model->daily_sales(14),
            'events'  => $this->Admin_model->sales_per_event(),
            'orders'  => $this->Admin_model->orders(array(), 8),
            'nav'     => 'dashboard',
        ));
    }
}
