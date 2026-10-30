<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_warehouses($only_id = NULL)
    {
        if ($only_id !== NULL) { $this->db->where('id', $only_id); }
        return $this->db->order_by('name')->get('warehouses')->result_array();
    }

    public function get_products()
    {
        return $this->db->select('id, code, name')->order_by('name')->get('products')->result_array();
    }

    public function find($warehouse_id, $product_id)
    {
        return $this->db->where('warehouse_id', $warehouse_id)->where('product_id', $product_id)
            ->get('warehouse_stock')->row_array();
    }

    public function save_quantity($warehouse_id, $product_id, $quantity, $alert_quantity)
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $saved = $this->db->query(
            'INSERT INTO warehouse_stock (warehouse_id, product_id, quantity, alert_quantity) VALUES (?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), alert_quantity = VALUES(alert_quantity)',
            array($warehouse_id, $product_id, $quantity, $alert_quantity)
        );
        $this->db->db_debug = $debug;
        return $saved;
    }

    private function filtered_query($search, $warehouse_id, $low_only)
    {
        $this->db->from('warehouse_stock AS stock')
            ->join('products', 'products.id = stock.product_id')
            ->join('warehouses', 'warehouses.id = stock.warehouse_id');
        if ($search !== '') {
            $this->db->group_start()->like('products.name', $search)
                ->or_like('products.code', $search)->group_end();
        }
        if ($warehouse_id > 0) {
            $this->db->where('stock.warehouse_id', $warehouse_id);
        }
        if ($low_only) {
            $this->db->where('stock.quantity <= stock.alert_quantity', NULL, FALSE);
        }
    }

    public function count_filtered($search, $warehouse_id, $low_only)
    {
        $this->filtered_query($search, $warehouse_id, $low_only);
        return $this->db->count_all_results();
    }

    public function get_page($search, $warehouse_id, $low_only, $limit, $offset)
    {
        $this->filtered_query($search, $warehouse_id, $low_only);
        return $this->db->select('stock.warehouse_id, stock.product_id, products.code, products.name, products.is_active, warehouses.name AS warehouse_name, stock.quantity, stock.alert_quantity')
            ->order_by('stock.warehouse_id')->order_by('products.id')
            ->limit($limit, $offset)->get()->result_array();
    }
}
