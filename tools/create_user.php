<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$username = isset($argv[1]) ? $argv[1] : '';
$role = isset($argv[2]) ? $argv[2] : '';
$warehouse = isset($argv[3]) ? $argv[3] : NULL;
if (!preg_match('/^[a-zA-Z0-9_]{3,80}$/D', $username) || !in_array($role, array('admin', 'warehouse_user'), TRUE)) {
    fwrite(STDERR, "Usage: php tools/create_user.php USERNAME admin|warehouse_user [WAREHOUSE_ID]\n");
    exit(1);
}
if ($role === 'warehouse_user' && (!ctype_digit((string) $warehouse) || (int) $warehouse < 1)) {
    fwrite(STDERR, "A valid warehouse ID is required.\n"); exit(1);
}
chdir(dirname(__DIR__));
$_SERVER['argv'] = array('index.php', 'welcome');
ob_start(); require 'index.php'; ob_end_clean();
$app =& get_instance(); $app->load->database();
if ($app->db->where('username', $username)->count_all_results('users')) {
    fwrite(STDERR, "Username already exists; no changes made.\n"); exit(1);
}
if ($role === 'warehouse_user' && !$app->db->where('id', $warehouse)->count_all_results('warehouses')) {
    fwrite(STDERR, "Warehouse not found.\n"); exit(1);
}
$password = bin2hex(random_bytes(10));
$ok = $app->db->insert('users', array(
    'username' => $username, 'name' => $username, 'role' => $role,
    'warehouse_id' => $role === 'admin' ? NULL : (int) $warehouse,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
));
if (!$ok) { fwrite(STDERR, "Could not create user.\n"); exit(1); }
echo "User created: ", $username, "\nRole: ", $role, "\nGenerated password: ", $password, "\n";
