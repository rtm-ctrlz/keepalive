<?php

declare(strict_types=1);

use RtmCtrlz\KeepAlive\Keepalive;

require_once __DIR__ . '/ssl_files_and_constants.php';
require __DIR__ . '/../../src/Keepalive.php';

$host   = '127.0.0.1';
$port   = 9898;
$errno  = null;
$errstr = null;

// Look closer - we create "tcp" stream instead of ssl
$stream = stream_socket_client(
    'tcp://' . $host . ':' . $port,
    $errno,
    $errstr,
    1.1,
    STREAM_CLIENT_CONNECT,
    stream_context_create(
        [
            'ssl' => [
                // @phpstan-ignore-next-line
                'peer_name'         => EX_SSL_CA_CERT_CN,
                // @phpstan-ignore-next-line
                'cafile'            => EX_SSL_CA_CERT_PATH,
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => true,
            ],
        ]
    )
);

if ($stream === false) {
    throw new Error('Failed to connect to tcp://' . $host . ':' . $port . ': ' . $errstr . ' (' . $errno . ')');
}

// Enabling TCP Keep-Alive just like for plain tcp socket
$socket = socket_import_stream($stream);
if ($socket === false) {
    throw new RuntimeException('Failed to import socket');
}
Keepalive::enable($socket, 2, 1, 2);

// Enabling SSL-layer for existing stream
if (!stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    throw new Error('Failed to connect to ssl://' . $host . ':' . $port);
}

while (!feof($stream)) {
    $data = fgets($stream);
    if ($data === false || $data === '') {
        break;
    }
    $data = trim($data);
    error_log(' => ' . $data);
}
