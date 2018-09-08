<?php

return [
    'all_routes' => true,
    'short_message' => 'requests',
    'default' => 'graylog',
    'transports' => [
        'graylog' => [
            'host' => '192.168.99.100',
            'port' => '12201',
            'driver' => RequestLogger\Transports\GelfUdpTransport::class,
        ],
    ],
];
