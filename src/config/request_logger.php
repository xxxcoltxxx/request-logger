<?php

return [
    // Enable request logger for all requests without adds middleware
    'all_routes' => true,

    // Default transport
    'default' => 'graylog',

    /*
     * Allowed transports with its configuration.
     * Drivers: 'graylog'
     */
    'transports' => [

        // The graylog transport
        'graylog' => [
            // The Short message for graylog
            'short_message' => 'requests',

            // Limit content size (in bytes). Set false to disable. Graylog has limitations on input messages
            'content_limit' => 30000,

            // The IP address of the log server
            'host' => '127.0.0.1',

            // The UDP port of the log server
            'port' => '12201',

            // Then driver for send requests log to log server
            'driver' => RequestLogger\Transports\GelfUdpTransport::class,
        ],
    ],
];
