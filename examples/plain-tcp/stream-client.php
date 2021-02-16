<?php

declare(strict_types=1);

use RtmCtrlz\KeepAlive\Keepalive;

require __DIR__ . '/../../src/Keepalive.php';

$host = '127.0.0.1';
$port = 9898;

$stream = @stream_socket_client('tcp://' . $host . ':' . $port);

if ($stream === false) {
    throw new RuntimeException("Connection to tcp://{$host}:{$port} failed");
}

error_log('Connected!');

$socket = socket_import_stream($stream);
if (!is_resource($socket)) {
    throw new RuntimeException('Failed to import socket');
}
Keepalive::enable($socket, 2, 1, 2);

while (!feof($stream)) {
    $data = fgets($stream);
    if ($data === false || $data === '') {
        break;
    }
    $data = trim($data);
    error_log(' => ' . $data);
}

fclose($stream);
