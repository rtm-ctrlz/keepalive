<?php

declare(strict_types=1);

require_once __DIR__ . '/ssl_files_and_constants.php';

$host = '127.0.0.1';
$port = 9898;

$errno  = null;
$errstr = null;

$stream = stream_socket_client(
    'ssl://' . $host . ':' . $port,
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
    throw new Error('Failed to connect to ssl://' . $host . ':' . $port . ': ' . $errstr . ' (' . $errno . ')');
}

while (!feof($stream)) {
    $data = fgets($stream);
    if ($data === false || $data === '') {
        break;
    }
    $data = trim($data);
    error_log(' => ' . $data);
}
