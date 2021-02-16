<?php

declare(strict_types=1);

define('EX_SSL_CA_CERT_CN', 'TCPKeepAliveCA');
define('EX_SSL_CA_CERT_PATH', __DIR__ . '/ca.pem');
define('EX_SSL_CA_KEY_PATH', __DIR__ . '/ca.key');
define('EX_SSL_CA_KEY_PASS', 'mypassword');


if (!file_exists(EX_SSL_CA_CERT_PATH)) {
    $privkey = openssl_pkey_new(
        [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]
    );

    $csr = openssl_csr_new(
        [
            "commonName" => EX_SSL_CA_CERT_CN,
        ],
        $privkey,
        ['digest_alg' => 'sha256']
    );
    if (!is_resource($csr)) {
        throw new RuntimeException('Failed to create csr');
    }

    $x509 = openssl_csr_sign($csr, null, $privkey, $days = 365, ['digest_alg' => 'sha256']);
    if (!is_resource($x509)) {
        throw new RuntimeException('Failed to sign csr');
    }

    openssl_x509_export($x509, $certout);
    file_put_contents(EX_SSL_CA_CERT_PATH, $certout);

    openssl_pkey_export($privkey, $pkeyout, EX_SSL_CA_KEY_PASS);
    file_put_contents(EX_SSL_CA_KEY_PATH, $pkeyout);
}
