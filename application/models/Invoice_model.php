<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_model extends CI_Model
{
    const MAX_CENTS = 99999999999999;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public static function cents($value)
    {
        $parts = explode('.', (string) $value);
        return (int) $parts[0] * 100 + (int) str_pad(isset($parts[1]) ? $parts[1] : '', 2, '0');
    }

    public static function money($cents)
    {
        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function customers()
    {
        return $this->db->order_by('name')->get('customers')->result_array();
    }

    public function search_products($warehouse, $search)
    {
        $this->db->select('products.id, products.code, products.name, products.selling_price, stock.quantity')
            ->from('products')->join('warehouse_stock AS stock', 'stock.product_id = products.id')
            ->where('stock.warehouse_id', $warehouse)->where('products.is_active', 1);
        if ($search !== '') {
            $this->db->group_start()->like('products.name', $search)->or_like('products.code', $search)->group_end();
        }
        return $this->db->order_by('products.name')->limit(20)->get()->result_array();
    }

    private function query_or_fail($sql, $params = array())
    {
        $result = $this->db->query($sql, $params);
        if ($result === FALSE) { throw new RuntimeException('تعذر حفظ الفاتورة. حاول مرة أخرى.'); }
        return $result;
    }

    // Called with validated integer IDs, quantities and discount basis points.
    public function save_sale($customer, $warehouse, $user, $items, $discount_bp)
    {
        $debug = $this->db->db_debug;
        $this->db->db_debug = FALSE;
        $this->db->trans_begin();
        try {
            if (!$this->query_or_fail('SELECT id FROM customers WHERE id = ?', array($customer))->row_array()) {
                throw new RuntimeException('العميل المحدد غير موجود.');
            }
            ksort($items, SORT_NUMERIC);
            $lines = array();
            $subtotal = 0;
            foreach ($items as $product_id => $quantity) {
                // Lock products first, then stock, consistently in product ID order.
                $product = $this->query_or_fail('SELECT id, code, name, selling_price, is_active FROM products WHERE id = ? FOR UPDATE', array($product_id))->row_array();
                $stock = $this->query_or_fail('SELECT quantity FROM warehouse_stock WHERE warehouse_id = ? AND product_id = ? FOR UPDATE', array($warehouse, $product_id))->row_array();
                if (!$product || !(int) $product['is_active']) { throw new RuntimeException('يوجد منتج غير متاح أو معطّل.'); }
                if (!$stock || (int) $stock['quantity'] < $quantity) { throw new RuntimeException('الكمية غير كافية للمنتج: ' . $product['name']); }
                $price = self::cents($product['selling_price']);
                if ($price && $quantity > intdiv(self::MAX_CENTS - $subtotal, $price)) {
                    throw new RuntimeException('إجمالي الفاتورة يتجاوز الحد المسموح.');
                }
                $line_total = $price * $quantity;
                $subtotal += $line_total;
                $lines[] = array('product_id' => $product_id, 'product_code' => $product['code'], 'product_name' => $product['name'],
                    'quantity' => $quantity, 'unit_price' => $product['selling_price'], 'line_total' => self::money($line_total));
            }
            $discount = intdiv($subtotal * $discount_bp + 5000, 10000);
            if (!$this->db->insert('invoices', array('customer_id' => $customer, 'warehouse_id' => $warehouse,
                'created_by' => $user, 'subtotal' => self::money($subtotal), 'discount_percent' => self::money($discount_bp),
                'discount_amount' => self::money($discount), 'total' => self::money($subtotal - $discount)))) {
                throw new RuntimeException('تعذر حفظ الفاتورة. حاول مرة أخرى.');
            }
            $id = $this->db->insert_id();
            foreach ($lines as $line) {
                $line['invoice_id'] = $id;
                if (!$this->db->insert('invoice_items', $line)) { throw new RuntimeException('تعذر حفظ سطور الفاتورة.'); }
                $this->query_or_fail('UPDATE warehouse_stock SET quantity = quantity - ? WHERE warehouse_id = ? AND product_id = ? AND quantity >= ?',
                    array($line['quantity'], $warehouse, $line['product_id'], $line['quantity']));
                if ($this->db->affected_rows() !== 1) { throw new RuntimeException('تغيّر المخزون؛ حاول مرة أخرى.'); }
            }
            if (!$this->db->trans_status() || !$this->db->trans_commit()) { throw new RuntimeException('تعذر إتمام الحفظ. حاول مرة أخرى.'); }
            return array('id' => $id, 'error' => '');
        } catch (Throwable $error) {
            $this->db->trans_rollback();
            return array('id' => NULL, 'error' => $error instanceof RuntimeException ? $error->getMessage() : 'تعذر حفظ الفاتورة. حاول مرة أخرى.');
        } finally {
            $this->db->db_debug = $debug;
        }
    }

    public function listing($warehouse, $limit, $offset)
    {
        if ($warehouse !== NULL) { $this->db->where('invoices.warehouse_id', $warehouse); }
        return $this->db->select('invoices.*, customers.name AS customer_name, warehouses.name AS warehouse_name, users.name AS creator_name')
            ->from('invoices')->join('customers', 'customers.id = invoices.customer_id')
            ->join('warehouses', 'warehouses.id = invoices.warehouse_id')->join('users', 'users.id = invoices.created_by')
            ->order_by('invoices.id', 'DESC')->limit($limit, $offset)->get()->result_array();
    }

    public function count_invoices($warehouse)
    {
        if ($warehouse !== NULL) { $this->db->where('warehouse_id', $warehouse); }
        return $this->db->count_all_results('invoices');
    }

    public function details($id, $warehouse)
    {
        if ($warehouse !== NULL) { $this->db->where('invoices.warehouse_id', $warehouse); }
        $invoice = $this->db->select('invoices.*, customers.name AS customer_name, warehouses.name AS warehouse_name, users.name AS creator_name')
            ->from('invoices')->join('customers', 'customers.id = invoices.customer_id')->join('warehouses', 'warehouses.id = invoices.warehouse_id')
            ->join('users', 'users.id = invoices.created_by')->where('invoices.id', $id)->get()->row_array();
        if (!$invoice) { return NULL; }
        $invoice['items'] = $this->db->where('invoice_id', $id)->order_by('id')->get('invoice_items')->result_array();
        return $invoice;
    }
}
