<?php

declare(strict_types=1);

namespace RtmCtrlz\KeepAlive;

use RuntimeException;

use function array_key_exists;
use function defined;
use function function_exists;
use function socket_set_option;

use const PHP_OS_FAMILY;
use const PHP_VERSION_ID;
use const SO_KEEPALIVE;
use const SOL_SOCKET;
use const SOL_TCP;

final class Keepalive
{
    public const TCP_OPTIONS = [
        // https://github.com/torvalds/linux/blob/master/include/uapi/linux/tcp.h
        'Linux'  => [
            'TCP_KEEPIDLE'  => 4,
            'TCP_KEEPINTVL' => 5,
            'TCP_KEEPCNT'   => 6,
        ],
        // https://github.com/apple/darwin-xnu/blob/master/bsd/netinet/tcp.h
        'Darwin' => [
            'TCP_KEEPIDLE'  => 0x10,
            'TCP_KEEPINTVL' => 0x101,
            'TCP_KEEPCNT'   => 0x102,
        ],
        // https://github.com/freebsd/freebsd/blob/master/sys/netinet/tcp.h
        'BSD'    => [
            'TCP_KEEPIDLE'  => 0x100,
            'TCP_KEEPINTVL' => 0x200,
            'TCP_KEEPCNT'   => 0x400,
        ],
    ];

    /**
     * Enabling TCP Keep-Alive on a socket and tuning parameters
     *
     * @param resource $socket
     *
     * @param int      $time
     * @param int      $interval
     * @param int      $probes
     *
     * @return true
     * @throws RuntimeException
     *
     * Parameters
     *  - time:     timeout from last data-packet (ACKs are not data-packets) in socket
     *              when system hits timeout TCP Keep-Alive mechanism starts to work
     *              unit: seconds
     *              default for most systems: 7200
     *
     *  - interval: timout between sending probes
     *              unit: seconds
     *              default for most systems: 75
     *
     *  - probes:   number of "TCP Keep-Alive" packets without an answer ("TCP Keep-Alive ACK")
     *              to consider socket as "broken pipe"
     *              unit: packets
     *              default for most systems: 9
     */
    public static function enable($socket, int $time = 7200, int $interval = 75, int $probes = 9): bool
    {
        self::checkPHP();
        // enabling Keep-Alive for a socket
        if (!socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1)) {
            throw new RuntimeException('Failed to enable TCP-Keepalive', 1);
        }

        // setting TCP Keep-Alive parameters
        foreach (
            [
                'TCP_KEEPIDLE'  => $time,
                'TCP_KEEPINTVL' => $interval,
                'TCP_KEEPCNT'   => $probes,
            ] as $option => $value
        ) {
            if (!socket_set_option($socket, SOL_TCP, self::TCP_OPTIONS[PHP_OS_FAMILY][$option], $value)) {
                throw new RuntimeException("Failed to set option '{$option}'", 2);
            }
        }

        return true;
    }

    /**
     * @static
     *
     * @param resource $socket
     *
     * @return bool
     */
    public static function disable($socket): bool
    {
        return socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 0);
    }

    /**
     * Doing checks: php version >7.2  and ext-sockets
     *
     * @throws RuntimeException
     */
    private static function checkPHP(): void
    {
        if (PHP_VERSION_ID < 70200 || !defined('PHP_OS_FAMILY')) {
            // generally php below 7.2 also supports TCP Keep-Alive
            // but OS-detection will be a bit more complicated
            throw new RuntimeException('Unsupported php version.');
        }

        if (!array_key_exists(PHP_OS_FAMILY, self::TCP_OPTIONS)) {
            throw new RuntimeException('Unsupported OS.');
        }

        // we need socket_set_option function to set socket/tcp options
        if (!function_exists('socket_set_option')) {
            throw new RuntimeException('Missing socket extension.');
        }
    }
}
