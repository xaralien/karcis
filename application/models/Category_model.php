<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model
{
    public function all_with_count()
    {
        return $this->db->select("c.*, COUNT(e.id) AS total")
            ->from('categories c')
            ->join('events e', "e.category_id = c.id AND e.status = 'published'", 'left')
            ->group_by('c.id')->order_by('c.id')->get()->result();
    }
}
