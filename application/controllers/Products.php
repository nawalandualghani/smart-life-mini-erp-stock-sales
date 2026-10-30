<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
    public function create()
    {
        $this->product_form();
    }

    public function edit($id = NULL)
    {
        $this->product_form($id, TRUE);
    }

    private function product_form($id = NULL, $editing = FALSE)
    {
        $this->require_admin();
        $this->load->model('Product_model');
        $this->load->helper(array('url', 'form'));
        $values = array('code' => '', 'name' => '', 'category_id' => '', 'selling_price' => '');
        if ($editing) {
            $id = $this->positive_integer($id, 0);
            $product = $id ? $this->Product_model->find($id) : NULL;
            if (!$product) {
                show_404();
                return;
            }
            foreach ($values as $key => $unused) {
                $values[$key] = (string) $product[$key];
            }
        }
        $errors = array();

        if ($this->input->method() === 'post') {
            foreach ($values as $key => $unused) {
                $value = $this->input->post($key);
                $values[$key] = is_string($value) ? trim($value) : '';
            }
            $this->load->library('form_validation');
            $this->form_validation->set_data($values);
            $this->form_validation->set_rules('code', 'الكود', 'required|max_length[50]');
            $this->form_validation->set_rules('name', 'الاسم', 'required|max_length[180]');
            $this->form_validation->set_rules('category_id', 'التصنيف', 'required|is_natural_no_zero');
            $this->form_validation->set_rules('selling_price', 'السعر', array('required', 'regex_match[/^[0-9]{1,10}(\.[0-9]{1,2})?$/]'));
            $this->form_validation->set_message('required', 'حقل {field} مطلوب.');
            $this->form_validation->set_message('max_length', 'حقل {field} أطول من الحد المسموح ({param}).');
            $this->form_validation->set_message('is_natural_no_zero', 'اختر تصنيفًا صحيحًا.');
            $this->form_validation->set_message('regex_match', 'أدخل سعرًا غير سالب، بحد أقصى 10 أرقام صحيحة ومنزلتين عشريتين.');
            $this->form_validation->run();
            $errors = $this->form_validation->error_array();
            if (!isset($errors['category_id']) && !$this->Product_model->category_exists($values['category_id'])) {
                $errors['category_id'] = 'التصنيف المحدد غير موجود.';
            }
            if (!isset($errors['code']) && $this->Product_model->code_exists($values['code'], $editing ? $id : NULL)) {
                $errors['code'] = 'الكود مستخدم لمنتج آخر.';
            }
            if (empty($errors)) {
                $result = $editing ? $this->Product_model->update($id, $values) : $this->Product_model->create($values);
                if ($result === 'saved') {
                    redirect('products?' . ($editing ? 'updated=1' : 'created=1'), 'location', 303);
                }
                $errors['save'] = $result === 'duplicate' ? 'الكود مستخدم لمنتج آخر.' : 'تعذر حفظ المنتج. حاول مرة أخرى.';
            }
        }

        $this->load->view('products/create', array(
            'values' => $values,
            'errors' => $errors,
            'categories' => $this->Product_model->get_categories(),
            'editing' => $editing,
            'form_action' => $editing ? 'products/edit/' . $id : 'products/create',
        ));
    }

    public function set_status($id = NULL)
    {
        $this->require_admin();
        if ($this->input->method() !== 'post') {
            $this->output->set_header('Allow: POST');
            show_error('هذه العملية تقبل POST فقط.', 405);
            return;
        }
        $this->load->model('Product_model');
        $this->load->helper('url');
        $id = $this->positive_integer($id, 0);
        if (!$id || !$this->Product_model->find($id)) {
            show_404();
            return;
        }
        $status = $this->input->post('is_active');
        if ($status !== '0' && $status !== '1') {
            show_error('حالة المنتج غير صحيحة.', 400);
            return;
        }
        if ($this->Product_model->update($id, array('is_active' => (int) $status)) !== 'saved') {
            show_error('تعذر تحديث حالة المنتج. حاول مرة أخرى.', 500);
            return;
        }
        redirect('products?status_changed=1', 'location', 303);
    }

    public function index()
    {
        $this->load->model('Product_model');
        $this->load->helper(array('url', 'form'));

        $search = $this->input->get('q');
        $search = is_string($search) ? trim($search) : '';
        $category_id = $this->positive_integer($this->input->get('category_id'), 0);
        $page = $this->positive_integer($this->input->get('page'), 1);
        $per_page = 15;
        $total = $this->Product_model->count_filtered($search, $category_id);
        $pages = max(1, (int) ceil($total / $per_page));
        $page = min($page, $pages);

        $data = array(
            'products' => $this->Product_model->get_page($search, $category_id, $per_page, ($page - 1) * $per_page),
            'categories' => $this->Product_model->get_categories(),
            'search' => $search,
            'category_id' => $category_id,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'per_page' => $per_page,
            'created' => $this->input->get('created') === '1',
            'updated' => $this->input->get('updated') === '1',
            'status_changed' => $this->input->get('status_changed') === '1',
            'listing_url' => site_url('products'),
            'filters' => array('q' => $search, 'category_id' => $category_id),
        );

        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'html' => $this->load->view('products/results', $data, TRUE),
                'total' => $total,
                'url' => site_url('products') . '?' . http_build_query(array_merge($data['filters'], array('page' => $page))),
            ), JSON_UNESCAPED_UNICODE));
            return;
        }
        $this->load->view('products/index', $data);
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
