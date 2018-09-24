<?php

return [
    // Enable request logger for all requests without adds middleware
    'all_routes' => true,

    // Default transport
    'default' => env('REQUEST_LOGGER_TRANSPORT', 'graylog'),

    // The class for format log message
    'formatter' => RequestLogger\RequestFormatter::class,

    /*
     * Allowed transports with its configuration.
     * Drivers: 'graylog', 'log', 'null'
     */
    'transports' => [

        // The graylog transport
        'graylog' => [
            // The Short message for graylog
            'short_message' => env('GRAYLOG_SHORT_MESSAGE', 'requests'),

            // Limit content size (in bytes). Set false to disable. Graylog has limitations on input messages
            'content_limit' => env('GRAYLOG_CONTENT_LIMIT', 30000),

            // The IP address of the log server
            'host' => env('GRAYLOG_HOST', '127.0.0.1'),

            // The UDP port of the log server
            'port' => env('GRAYLOG_PORT', '12201'),

            // The driver for send requests log to log server
            'driver' => RequestLogger\Transports\GelfUdpTransport::class,
        ],

        // The log transport
        'log' => [
            // The channel name
            'channel' => env('REQUEST_LOGGER_CHANNEL', 'stack'),
            'driver' => RequestLogger\Transports\LogTransport::class,
        ],

        // The null transport
        'null' => [
            'driver' => RequestLogger\Transports\NullTransport::class,
        ],
    ],
];
