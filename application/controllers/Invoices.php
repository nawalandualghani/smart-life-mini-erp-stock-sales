<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoices extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Invoice_model');
        $this->load->model('Stock_model');
    }

    private function scope()
    {
        return $this->current_user['role'] === 'admin' ? NULL : (int) $this->current_user['warehouse_id'];
    }

    private function integer($value)
    {
        return is_string($value) ? filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 4294967295))) : FALSE;
    }

    private function warehouse($value)
    {
        $id = $this->integer($value);
        if (!$id) { throw new RuntimeException('اختر مستودعًا صحيحًا.'); }
        if ($this->scope() !== NULL && $id !== $this->scope()) { throw new RuntimeException('لا يمكنك الوصول إلى هذا المستودع.'); }
        $warehouses = $this->Stock_model->get_warehouses($id);
        if (!$warehouses) { throw new RuntimeException('المستودع غير موجود.'); }
        return $id;
    }

    public function search_products()
    {
        try {
            $warehouse = $this->warehouse($this->input->get('warehouse_id'));
            $search = $this->input->get('q');
            $rows = $this->Invoice_model->search_products($warehouse, is_string($search) ? trim($search) : '');
            $this->output->set_content_type('application/json')->set_output(json_encode(array('products' => $rows), JSON_UNESCAPED_UNICODE));
        } catch (RuntimeException $error) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array('error' => $error->getMessage()), JSON_UNESCAPED_UNICODE));
        }
    }

    public function create()
    {
        $errors = array();
        $tokens = $this->session->userdata('invoice_forms');
        $tokens = is_array($tokens) ? $tokens : array();
        $state = array('customer_id' => '', 'warehouse_id' => $this->scope() === NULL ? '' : (string) $this->scope(), 'discount_percent' => '0', 'items' => array());
        if ($this->input->method() === 'post') {
            // Capture editable state before any validation can return an error.
            foreach (array('customer_id', 'warehouse_id', 'discount_percent') as $key) {
                $value = $this->input->post($key);
                $state[$key] = is_string($value) ? trim($value) : '';
            }
            $raw = $this->input->post('items');
            if (is_array($raw)) {
                foreach (array_slice($raw, 0, 100) as $line) {
                    $id = is_array($line) && isset($line['product_id']) ? $this->integer($line['product_id']) : FALSE;
                    if (!$id) { continue; }
                    $quantity = isset($line['quantity']) && is_string($line['quantity']) ? trim($line['quantity']) : '';
                    $state['items'][] = array('id' => $id, 'quantity' => $quantity);
                }
            }
            try {
                $token = $this->input->post('form_token');
                if (!is_string($token) || !array_key_exists($token, $tokens)) { throw new RuntimeException('انتهت صلاحية النموذج. أعد المحاولة من هذه الصفحة.'); }
                if ($tokens[$token]) { redirect('invoices/show/' . $tokens[$token], 'location', 303); return; }
                $warehouse = $this->warehouse($state['warehouse_id']);
                $customer = $this->integer($state['customer_id']);
                if (!$customer) { throw new RuntimeException('اختر عميلًا صحيحًا.'); }
                if (!preg_match('/^[0-9]{1,3}(\.[0-9]{1,2})?$/D', $state['discount_percent'])) { throw new RuntimeException('الخصم يجب أن يكون نسبة من 0 إلى 100 بمنزلتين عشريتين كحد أقصى.'); }
                $discount = Invoice_model::cents($state['discount_percent']);
                if ($discount > 10000) { throw new RuntimeException('الخصم لا يتجاوز 100%.'); }
                if (!is_array($raw) || !$raw || count($raw) > 100) { throw new RuntimeException('أضف من منتج واحد إلى 100 منتج.'); }
                $items = array();
                foreach ($raw as $line) {
                    $id = is_array($line) && isset($line['product_id']) ? $this->integer($line['product_id']) : FALSE;
                    $quantity = is_array($line) && isset($line['quantity']) ? $this->integer($line['quantity']) : FALSE;
                    if (!$id || !$quantity) { throw new RuntimeException('كل سطر يحتاج منتجًا وكمية صحيحة موجبة.'); }
                    $items[$id] = (isset($items[$id]) ? $items[$id] : 0) + $quantity;
                    if ($items[$id] > 4294967295) { throw new RuntimeException('الكمية تتجاوز الحد المسموح.'); }
                }
                $result = $this->Invoice_model->save_sale($customer, $warehouse, (int) $this->current_user['id'], $items, $discount);
                if ($result['error']) { throw new RuntimeException($result['error']); }
                $tokens[$token] = $result['id'];
                $this->session->set_userdata('invoice_forms', $tokens);
                redirect('invoices/show/' . $result['id'], 'location', 303);
                return;
            } catch (RuntimeException $error) { $errors[] = $error->getMessage(); }
        }
        // Rebuild retained lines using trusted names and prices, not browser values.
        $retained = array();
        // Never read stock from a different warehouse while rebuilding an error response.
        $stock_warehouse = $this->scope() !== NULL ? $this->scope() : $this->integer($state['warehouse_id']);
        foreach ($state['items'] as $item) {
            $product = $this->db->where('id', $item['id'])->get('products')->row_array();
            if ($product) {
                $stock = $stock_warehouse ? $this->Stock_model->find($stock_warehouse, $item['id']) : NULL;
                $product['available_quantity'] = $stock ? (int) $stock['quantity'] : 0;
                $product['quantity'] = $item['quantity']; $retained[] = $product;
            }
        }
        $state['items'] = $retained;
        $token = bin2hex(random_bytes(20));
        $tokens[$token] = 0;
        $this->session->set_userdata('invoice_forms', array_slice($tokens, -20, NULL, TRUE));
        $this->load->view('invoices/create', array('state' => $state, 'errors' => $errors, 'form_token' => $token,
            'customers' => $this->Invoice_model->customers(), 'warehouses' => $this->Stock_model->get_warehouses($this->scope())));
    }

    public function index()
    {
        $total = $this->Invoice_model->count_invoices($this->scope());
        $pages = max(1, (int) ceil($total / 20));
        $page = min($pages, $this->integer($this->input->get('page')) ?: 1);
        $this->load->view('invoices/index', array('invoices' => $this->Invoice_model->listing($this->scope(), 20, ($page - 1) * 20), 'page' => $page, 'pages' => $pages));
    }

    public function show($id = NULL)
    {
        $id = $this->integer($id);
        $invoice = $id ? $this->Invoice_model->details($id, $this->scope()) : NULL;
        if (!$invoice) { show_404(); return; }
        $this->load->view('invoices/show', array('invoice' => $invoice));
    }

    private function csv_row($stream, $values)
    {
        // Prevent user-entered names/codes being interpreted as spreadsheet formulas.
        $values = array_map(function ($value) {
            $value = (string) $value;
            return preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) ? "'" . $value : $value;
        }, $values);
        fputcsv($stream, $values);
    }

    public function export($id = NULL)
    {
        $invoice = NULL;
        if ($id !== NULL) {
            $parsed = $this->integer($id);
            $invoice = $parsed ? $this->Invoice_model->details($parsed, $this->scope()) : NULL;
            if (!$invoice) { show_404(); return; }
        }
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF"); // UTF-8 BOM for Arabic in Excel.
        if ($invoice) {
            $this->csv_row($stream, array('رقم الفاتورة', $invoice['id']));
            $this->csv_row($stream, array('العميل', $invoice['customer_name']));
            $this->csv_row($stream, array('المستودع', $invoice['warehouse_name']));
            $this->csv_row($stream, array('أنشأها', $invoice['creator_name']));
            $this->csv_row($stream, array('التاريخ', $invoice['created_at']));
            $this->csv_row($stream, array());
            $this->csv_row($stream, array('الكود', 'المنتج', 'الكمية', 'سعر الوحدة', 'إجمالي السطر'));
            foreach ($invoice['items'] as $item) {
                $this->csv_row($stream, array($item['product_code'], $item['product_name'], $item['quantity'], $item['unit_price'], $item['line_total']));
            }
            $this->csv_row($stream, array());
            $this->csv_row($stream, array('المجموع الفرعي', $invoice['subtotal']));
            $this->csv_row($stream, array('نسبة الخصم', $invoice['discount_percent']));
            $this->csv_row($stream, array('مبلغ الخصم', $invoice['discount_amount']));
            $this->csv_row($stream, array('الإجمالي النهائي', $invoice['total']));
        } else {
            $this->csv_row($stream, array('رقم الفاتورة', 'العميل', 'المستودع', 'أنشأها', 'التاريخ', 'المجموع الفرعي', 'نسبة الخصم', 'مبلغ الخصم', 'الإجمالي النهائي'));
            for ($offset = 0; ; $offset += 500) {
                $rows = $this->Invoice_model->listing($this->scope(), 500, $offset);
                foreach ($rows as $row) {
                    $this->csv_row($stream, array($row['id'], $row['customer_name'], $row['warehouse_name'], $row['creator_name'], $row['created_at'], $row['subtotal'], $row['discount_percent'], $row['discount_amount'], $row['total']));
                }
                if (count($rows) < 500) break;
            }
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        $filename = $invoice ? 'invoice-' . (int) $invoice['id'] . '.csv' : 'invoices.csv';
        $this->output->set_content_type('text/csv', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_header('Cache-Control: no-store')
            ->set_header('X-Content-Type-Options: nosniff')->set_output($content);
    }
}
