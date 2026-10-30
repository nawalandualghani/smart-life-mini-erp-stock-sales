<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function find($id)
    {
        return $this->db->select('id, name, username, role, warehouse_id, is_active')
            ->where('id', $id)->get('users')->row_array();
    }

    public function authenticate($username, $password)
    {
        $user = $this->db->where('username', $username)->get('users')->row_array();
        // A dummy hash keeps password verification work for unknown usernames.
        $hash = $user ? $user['password_hash'] : '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify($password, $hash);
        if (!$valid || !$user || !(int) $user['is_active']) { return NULL; }
        if ($user['role'] === 'warehouse_user' && !$user['warehouse_id']) { return NULL; }
        return $user;
    }
}
