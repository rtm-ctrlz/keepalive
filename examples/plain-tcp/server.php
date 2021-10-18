<?php

declare(strict_types=1);

use RtmCtrlz\KeepAlive\Keepalive;

require __DIR__ . '/../../src/Keepalive.php';

$host = '127.0.0.1';
$port = 9898;

$errno  = null;
$errstr = null;

$server = stream_socket_server(
    'tcp://' . $host . ':' . $port,
    $errno,
    $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
);
if (!$server) {
    echo "$errstr ($errno)", PHP_EOL;
} else {
    error_log('Waiting for new connection...');
    while (true) {
        while ($conn = @stream_socket_accept($server, 5)) {
            $socket = socket_import_stream($conn);
            if ($socket === false) {
                throw new RuntimeException('Failed to import socket');
            }
            Keepalive::enable($socket, 2, 1, 2);

            error_log('New connection');
            while (!feof($conn)) {
                $data = fgets($conn);
                error_log(' => ' . $data);
            }

            // stop server after connection is closed
            fclose($conn);
            fclose($server);
            break 2;
        }
    }
}
