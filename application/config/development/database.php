<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require dirname(__DIR__) . '/database.php';

// Local connection only. This file is excluded from Git.
$db['default']['hostname'] = '127.0.0.1';
$db['default']['port'] = 3306;
$db['default']['username'] = 'root';
$db['default']['password'] = '';
$db['default']['database'] = 'smart_life_test';
