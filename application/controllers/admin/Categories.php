<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends Admin_Controller
{
    public function index()
    {
        $this->render('categories/index', array('title' => 'Kategori', 'categories' => $this->Admin_model->categories(), 'nav' => 'categories'));
    }

    public function save($id = 0)
    {
        if ($this->input->method() !== 'post') $this->go('admin/categories');
        $name = trim((string) $this->input->post('name', TRUE));
        $icon = trim((string) $this->input->post('icon', TRUE)) ?: 'bi-ticket-perforated';

        if (mb_strlen($name) < 2) {
            $this->session->set_flashdata('error', 'Nama kategori minimal 2 karakter.');
            $this->go('admin/categories');
        }
        $slug = $this->fmt->slugify($name);
        $dupe = $this->db->where('slug', $slug)->where('id !=', (int) $id)->count_all_results('categories');
        if ($dupe) {
            $this->session->set_flashdata('error', "Kategori \"{$name}\" sudah ada.");
            $this->go('admin/categories');
        }

        $row = array('name' => $name, 'slug' => $slug, 'icon' => preg_replace('/[^a-z0-9-]/', '', $icon));
        if ($id) $this->db->where('id', (int) $id)->update('categories', $row);
        else     $this->db->insert('categories', $row);

        $this->session->set_flashdata('success', $id ? 'Kategori diperbarui.' : 'Kategori ditambahkan.');
        $this->go('admin/categories');
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') $this->go('admin/categories');
        if ($this->db->where('category_id', (int) $id)->count_all_results('events') > 0) {
            $this->session->set_flashdata('error', 'Kategori masih dipakai event. Pindahkan event ke kategori lain dulu.');
        } else {
            $this->db->where('id', (int) $id)->delete('categories');
            $this->session->set_flashdata('success', 'Kategori dihapus.');
        }
        $this->go('admin/categories');
    }
}
