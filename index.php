<?php

require_once 'core/init.php';
use app\Models\Database;

$db = Database::getInstance();
$db->setTable('users');
$users= $db->select()->all();
var_dump($users);