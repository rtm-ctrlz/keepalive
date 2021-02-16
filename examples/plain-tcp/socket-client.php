<?php

declare(strict_types=1);

use RtmCtrlz\KeepAlive\Keepalive;

require __DIR__ . '/../../src/Keepalive.php';

$host = '127.0.0.1';
$port = 9898;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if (!is_resource($socket) || !@socket_connect($socket, $host, $port)) {
    throw new RuntimeException("Connection to tcp://{$host}:{$port} failed");
}

error_log('Connected!');

Keepalive::enable($socket, 2, 1, 2);

while (($data = socket_read($socket, 10)) !== false && $data !== '') {
    $data = trim($data);
    error_log(' => ' . $data);
}

socket_close($socket);
