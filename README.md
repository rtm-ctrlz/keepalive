[![phpstan](https://github.com/rtm-ctrlz/keepalive/actions/workflows/php.yml/badge.svg)](https://github.com/rtm-ctrlz/keepalive/actions/workflows/php.yml)
[![php_codesniffer](https://github.com/rtm-ctrlz/keepalive/actions/workflows/php.yml/badge.svg)](https://github.com/rtm-ctrlz/keepalive/actions/workflows/php.yml)

# Keepalive

This is a helper for setting proper TCP-Keepalive options and values.

# Reason

Common way to enable TCP-Keepalive on a socket looks like this:

```php
socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
```

And yes, it enables keepalive, but what are keepalive parameters? Example above will use system-default values!

Most systems (OSes) have the following defaults:

| Option | Default value | Description |
|-|:-:|-|
| SO_KEEPALIVE | 0 | TCP Keep-Alive is disabled |
| TCP_KEEPIDLE | 7200 | Start keepalive probes after this period (2 hours) |
| TCP_KEEPINTVL | 75 |  Interval between keepalive probes (75 seconds) |
| TCP_KEEPCNT | 8 | Number of keepalive probes before death |

So, with default values, first TCP-Keepalive packet will be sent only in 2 hours after last packet, then wait more up to
10 minutes (`75s * 8`) before dropping dead connection.

Is this really your case?

Not for me, I'd like drop connection within 1 minute or less!

# Install

```shell
composer require rtm-ctrlz/keepalive
```

# Usage

> Below you can find some examples (socket/stream/ssl-stream) for "client-side", but same operations could be done for "server-side".

In examples below we use following parameters:

- idle time: 45s
- probes interval: 5s
- number of probes: 3

So we'd get maximum waiting time of 1 minute.

### Raw sockets

```php
// create socket
$socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
// enable tcp-keepalive
\RtmCtrlz\Keepalive\Keepalive::enable($socket, 45, 5, 3);
```

### Streams (tcp)

```php
// create stream
$stream = \stream_socket_client('tcp://127.0.0.1:80);
// get socket
$socket = \socket_import_stream($stream);
// enable tcp-keepalive
\RtmCtrlz\Keepalive\Keepalive::enable($socket, 45, 5, 3);
```

### Streams (ssl)

This will be a bit harder, because `socket_import_stream` can not import socket.

But we can do a trick:

- create tcp-connection ([stream_socket_client()](https://www.php.net/manual/en/function.stream-socket-client.php))
- import socket ([socket_import_stream()](https://www.php.net/manual/en/function.socket-import-stream.php))
- enable tcp-keepalive
- enable
  encryption ([stream_socket_enable_crypto()](https://www.php.net/manual/en/function.stream-socket-enable-crypto.php))

```php
// create stream
// NOTE: stream has "tcp" proto
$stream = \stream_socket_client(
    'tcp://127.0.0.1:443',         // target
    $errno,                        // error number
    $errstr,                       // error description
    1.1,                           // timeout
    STREAM_CLIENT_CONNECT,         // flags
    stream_context_create(         // context
        [
            'ssl' => [
                // ... ssl options
            ],
        ]
    )
);

// get socket
$socket = \socket_import_stream($stream);
// enable tcp-keepalive
\RtmCtrlz\Keepalive\Keepalive::enable($socket, 45, 5, 3);
// enable encryption
\stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
```

### More examples

See [examples](./examples/) directory for more examples.

# Gotchas

### Option numbers

Unfortunately PHP (and ext-sockets) doesn't have `TCP_KEEPIDLE`, `TCP_KEEPINTVL` and `TCP_KEEPCNT` constants.

#### Found values
| Option | Linux | Darwin | BSD |
|-|:-:|:-:|:-:|
|TCP_KEEPIDLE| `4` | `16` | `256` |
|TCP_KEEPINTVL| `5` | `257` | `512` |
|TCP_KEEPCNT| `6` | `258` |  `1024` |

Linux: Linux [tcp.h](https://github.com/torvalds/linux/blob/master/include/uapi/linux/tcp.h)
Darwin: Darwin XNU [tcp.h](https://github.com/apple/darwin-xnu/blob/master/bsd/netinet/tcp.h)
BSD: FreeBSD [tcp.h](https://github.com/freebsd/freebsd/blob/master/sys/netinet/tcp.h)

#### Windows support

Well, Windows should have support for TCP-Keepalive options, but:

- I have no Windows machine to run tests
- I didn't look for `TCP_KEEP*` values on Windows

Feel free to make a PR ;)

# Tested with

- [phpstan/phpstan](https://github.com/phpstan/phpstan)
- [squizlabs/PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
