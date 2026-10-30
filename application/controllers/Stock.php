<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends MY_Controller
{
    public function manage()
    {
        $this->require_admin();
        $this->load->model('Stock_model');
        $this->load->helper(array('url', 'form'));
        $posting = $this->input->method() === 'post';
        $input = $posting ? $this->input->post() : $this->input->get();
        $input = is_array($input) ? $input : array();
        $warehouse_id = $this->positive_integer(isset($input['warehouse_id']) ? $input['warehouse_id'] : NULL, 0);
        $product_id = $this->positive_integer(isset($input['product_id']) ? $input['product_id'] : NULL, 0);
        $warehouses = $this->Stock_model->get_warehouses();
        $products = $this->Stock_model->get_products();
        $selected_warehouse = NULL;
        $selected_product = NULL;
        foreach ($warehouses as $warehouse) {
            if ((int) $warehouse['id'] === $warehouse_id) { $selected_warehouse = $warehouse; }
        }
        foreach ($products as $product) {
            if ((int) $product['id'] === $product_id) { $selected_product = $product; }
        }
        $ready = $selected_product && $selected_warehouse;
        $stock = $ready ? $this->Stock_model->find($warehouse_id, $product_id) : NULL;
        $values = array('quantity' => $stock ? $stock['quantity'] : '0', 'alert_quantity' => $stock ? $stock['alert_quantity'] : '0');
        $errors = array();
        if ($posting) {
            if (!$ready) { $errors[] = 'اختر منتجًا ومستودعًا موجودين.'; }
            foreach ($values as $key => $unused) {
                $values[$key] = isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
                if (!preg_match('/^[0-9]{1,10}$/D', $values[$key]) || (float) $values[$key] > 4294967295) {
                    $errors[] = ($key === 'quantity' ? 'الكمية' : 'حد التنبيه') . ': أدخل عددًا صحيحًا من 0 إلى 4294967295.';
                }
            }
            if (empty($errors)) {
                if ($this->Stock_model->save_quantity($warehouse_id, $product_id, $values['quantity'], $values['alert_quantity'])) {
                    redirect('stock?warehouse_id=' . $warehouse_id . '&saved=1', 'location', 303);
                }
                $errors[] = 'تعذر حفظ المخزون. حاول مرة أخرى.';
            }
        } elseif (($warehouse_id && !$selected_warehouse) || ($product_id && !$selected_product)) {
            $errors[] = 'المنتج أو المستودع المحدد غير موجود. اختر من القائمة.';
        }
        $this->load->view('stock/manage', array(
            'warehouses' => $warehouses, 'products' => $products,
            'warehouse_id' => $warehouse_id, 'product_id' => $product_id,
            'selected_product' => $selected_product, 'selected_warehouse' => $selected_warehouse,
            'ready' => $ready, 'existing' => (bool) $stock, 'values' => $values, 'errors' => $errors,
        ));
    }

    public function index()
    {
        $this->load->model('Stock_model');
        $this->load->helper('url');
        $search = $this->input->get('q');
        $search = is_string($search) ? trim($search) : '';
        $warehouse_id = $this->positive_integer($this->input->get('warehouse_id'), 0);
        if ($this->current_user['role'] === 'warehouse_user') {
            $assigned = (int) $this->current_user['warehouse_id'];
            if ($warehouse_id && $warehouse_id !== $assigned) {
                show_error('لا يمكنك الوصول إلى هذا المستودع.', 403);
                return;
            }
            $warehouse_id = $assigned;
        }
        $low_only = $this->input->get('low_only') === '1';
        $page = $this->positive_integer($this->input->get('page'), 1);
        $per_page = 15;
        $total = $this->Stock_model->count_filtered($search, $warehouse_id, $low_only);
        $pages = max(1, (int) ceil($total / $per_page));
        $page = min($page, $pages);

        $data = array(
            'rows' => $this->Stock_model->get_page($search, $warehouse_id, $low_only, $per_page, ($page - 1) * $per_page),
            'warehouses' => $this->Stock_model->get_warehouses($this->current_user['role'] === 'admin' ? NULL : $warehouse_id),
            'search' => $search,
            'warehouse_id' => $warehouse_id,
            'low_only' => $low_only,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'per_page' => $per_page,
            'saved' => $this->input->get('saved') === '1',
            'listing_url' => site_url('stock'),
            'filters' => array('q' => $search, 'warehouse_id' => $warehouse_id, 'low_only' => $low_only ? '1' : '0'),
        );
        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'html' => $this->load->view('stock/results', $data, TRUE),
                'total' => $total,
                'url' => site_url('stock') . '?' . http_build_query(array_merge($data['filters'], array('page' => $page))),
            ), JSON_UNESCAPED_UNICODE));
            return;
        }
        $this->load->view('stock/index', $data);
    }

    public function export_low_stock()
    {
        $this->load->model('Stock_model');
        $search = $this->input->get('q');
        $search = is_string($search) ? trim($search) : '';
        $warehouse_id = $this->positive_integer($this->input->get('warehouse_id'), 0);
        if ($this->current_user['role'] === 'warehouse_user') {
            $assigned = (int) $this->current_user['warehouse_id'];
            if ($warehouse_id && $warehouse_id !== $assigned) {
                show_error('لا يمكنك الوصول إلى هذا المستودع.', 403);
                return;
            }
            $warehouse_id = $assigned;
        }
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, array('كود المنتج', 'اسم المنتج', 'المستودع', 'الكمية الحالية', 'حد التنبيه', 'مقدار النقص', 'حالة المنتج'));
        for ($offset = 0; ; $offset += 500) {
            // Always export low stock, regardless of the checkbox or page parameter.
            $rows = $this->Stock_model->get_page($search, $warehouse_id, TRUE, 500, $offset);
            foreach ($rows as $row) {
                $values = array($row['code'], $row['name'], $row['warehouse_name'], $row['quantity'], $row['alert_quantity'],
                    max(0, (int) $row['alert_quantity'] - (int) $row['quantity']), (int) $row['is_active'] === 1 ? 'مفعّل' : 'معطّل');
                $values = array_map(function ($value) {
                    $value = (string) $value;
                    return preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) ? "'" . $value : $value;
                }, $values);
                fputcsv($stream, $values);
            }
            if (count($rows) < 500) break;
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        $this->output->set_content_type('text/csv', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="low-stock.csv"')
            ->set_header('Cache-Control: no-store')->set_header('X-Content-Type-Options: nosniff')
            ->set_output($content);
    }

    private function positive_integer($value, $default)
    {
        if (!is_string($value)) {
            return $default;
        }
        $parsed = filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        return $parsed === FALSE ? $default : $parsed;
    }
}
