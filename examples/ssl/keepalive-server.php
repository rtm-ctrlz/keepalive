<?php

declare(strict_types=1);

use RtmCtrlz\KeepAlive\Keepalive;

require_once __DIR__ . '/ssl_files_and_constants.php';
require __DIR__ . '/../../src/Keepalive.php';

$host = '127.0.0.1';
$port = 9898;

$server = stream_socket_server(
    'tcp://' . $host . ':' . $port,
    $errno,
    $errstr,
    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
    stream_context_create(
        [
            'ssl' => [
                // @phpstan-ignore-next-line
                'local_cert' => EX_SSL_CA_CERT_PATH,
                // @phpstan-ignore-next-line
                'local_pk'   => EX_SSL_CA_KEY_PATH,
                // @phpstan-ignore-next-line
                'passphrase' => EX_SSL_CA_KEY_PASS,
            ],
        ]
    )
);
if (!$server) {
    throw new Error('Failed to create listening stream: ' . $errstr . ' (' . $errno . ')');
} else {
    error_log('Waiting for new connections...');
    while (true) {
        while ($conn = @stream_socket_accept($server, 5)) {
            error_log('New connection');

            $socket = socket_import_stream($conn);
            if (!is_resource($socket)) {
                throw new RuntimeException('Failed to import socket');
            }
            Keepalive::enable($socket, 2, 1, 2);
            if (!stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_SERVER)) {
                throw new Error('Failed to accept ssl connection');
            }

            while (!feof($conn)) {
                $data = fgets($conn);
                error_log(' => ' . $data);
            }
            fclose($conn);
            fclose($server);
            break 2;
        }
    }
}
