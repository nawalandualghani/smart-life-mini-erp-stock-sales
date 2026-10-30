<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $current_user;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->load->model('User_model');
        $id = $this->session->userdata('user_id');
        $this->current_user = $id ? $this->User_model->find($id) : NULL;
        if (!$this->current_user || !(int) $this->current_user['is_active']) {
            $this->session->unset_userdata('user_id');
            redirect('auth/login');
            exit;
        }
        if ($this->current_user['role'] === 'warehouse_user' && !$this->current_user['warehouse_id']) {
            show_error('لم يتم تعيين مستودع لهذا المستخدم.', 403);
            exit;
        }
        $this->load->vars(array('current_user' => $this->current_user));
    }

    protected function require_admin()
    {
        if ($this->current_user['role'] !== 'admin') {
            show_error('هذه العملية متاحة للمدير فقط.', 403);
            exit;
        }
    }
}
