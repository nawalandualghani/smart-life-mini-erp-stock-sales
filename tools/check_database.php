<?php
// Run from the project root using PHP CLI. No web endpoint is exposed.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

chdir(dirname(__DIR__));
// Avoid passing CLI arguments to CodeIgniter's controller routing.
$_SERVER['argv'] = array('index.php', 'welcome');
ob_start();
require 'index.php';
ob_end_clean();

$app =& get_instance();
$app->load->database();
$counts = array();
foreach (array('categories', 'warehouses', 'products', 'warehouse_stock', 'customers') as $table) {
    $counts[$table] = $app->db->count_all($table);
}

echo json_encode(array(
    'database' => $app->db->database,
    'charset' => $app->db->char_set,
    'counts' => $counts,
    'products' => $app->db->select('code, name')->order_by('id')->get('products')->result_array(),
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
