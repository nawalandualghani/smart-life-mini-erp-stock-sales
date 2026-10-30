<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function apply_filters($search, $category_id)
    {
        if ($search !== '') {
            $this->db->group_start()
                ->like('products.name', $search)
                ->or_like('products.code', $search)
                ->group_end();
        }
        if ($category_id > 0) {
            $this->db->where('products.category_id', $category_id);
        }
    }

    public function count_filtered($search, $category_id)
    {
        $this->apply_filters($search, $category_id);
        return $this->db->count_all_results('products');
    }

    public function get_categories()
    {
        return $this->db->order_by('name')->get('categories')->result_array();
    }

    public function category_exists($id)
    {
        return $this->db->where('id', $id)->count_all_results('categories') > 0;
    }

    public function code_exists($code, $exclude_id = NULL)
    {
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->where('code', $code)->count_all_results('products') > 0;
    }

    public function find($id)
    {
        return $this->db->where('id', $id)->get('products')->row_array();
    }

    public function update($id, $data)
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $saved = $this->db->where('id', $id)->update('products', $data);
        $error = $this->db->error();
        $this->db->db_debug = $debug;
        return $saved ? 'saved' : ((int) $error['code'] === 1062 ? 'duplicate' : 'failed');
    }

    public function create($data)
    {
        // The unique database index also handles simultaneous submissions.
        $debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $saved = $this->db->insert('products', $data);
        $error = $this->db->error();
        $this->db->db_debug = $debug;
        return $saved ? 'saved' : ((int) $error['code'] === 1062 ? 'duplicate' : 'failed');
    }

    public function get_page($search, $category_id, $limit, $offset)
    {
        $this->apply_filters($search, $category_id);
        return $this->db
            ->select('products.id, products.code, products.name, products.selling_price, products.is_active, categories.name AS category_name')
            ->from('products')
            ->join('categories', 'categories.id = products.category_id')
            ->order_by('products.id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }
}
