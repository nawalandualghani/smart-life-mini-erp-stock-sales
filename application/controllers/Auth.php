<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->load->model('User_model');
    }

    public function login()
    {
        $error = '';
        $username = '';
        if ($this->input->method() === 'post') {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $username = is_string($username) ? trim($username) : '';
            $user = is_string($password) && strlen($password) <= 72 && strlen($username) <= 80
                ? $this->User_model->authenticate($username, $password) : NULL;
            if ($user) {
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata('user_id', (int) $user['id']);
                redirect('products', 'location', 303);
                return;
            }
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة، أو الحساب غير متاح.';
        }
        $this->load->view('auth/login', array('error' => $error, 'username' => $username));
    }

    public function logout()
    {
        if ($this->input->method() !== 'post') {
            $this->output->set_header('Allow: POST');
            show_error('هذه العملية تقبل POST فقط.', 405);
            return;
        }
        $this->session->sess_destroy();
        redirect('auth/login', 'location', 303);
    }
}
