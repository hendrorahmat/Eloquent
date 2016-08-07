<?php
session_start();

$GLOBALS['config'] = [
    'mysql' => [
        'host'      => '127.0.0.1',
        'username'  => 'root',
        'password'  => 'alhamdulillah',
        'db'        => 'penjualanmotor'
    ],
    'remember' => [
        'cookie_name' => 'hash',
        'cookie_expiry' => 604800
    ],
    'session'=> [
        'session_name' => 'user'
    ]
];
require_once 'vendor/autoload.php';