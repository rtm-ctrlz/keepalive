<?php

declare(strict_types=1);

require_once __DIR__ . '/ssl_files_and_constants.php';

$host = '127.0.0.1';
$port = 9898;

$errno  = null;
$errstr = null;

$server = stream_socket_server(
    'ssl://' . $host . ':' . $port,
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
    echo "$errstr ($errno)", PHP_EOL;
} else {
    error_log('Waiting for new connection...');
    while (true) {
        while ($conn = @stream_socket_accept($server, 5)) {
            error_log('New connection');
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
