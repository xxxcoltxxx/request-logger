# :memo: Laravel request logger

[![Build Status](https://travis-ci.com/xxxcoltxxx/request-logger.svg?branch=master)](https://travis-ci.com/xxxcoltxxx/request-logger)
[![Latest Stable Version](https://poser.pugx.org/xxxcoltxxx/request-logger/v/stable)](https://packagist.org/packages/xxxcoltxxx/request-logger)
[![Total Downloads](https://poser.pugx.org/xxxcoltxxx/request-logger/downloads)](https://packagist.org/packages/xxxcoltxxx/request-logger)

This package allows sending request and response payload to the external logging system.

Supported out-of-box transports:
* `graylog` transport for sending request log to [Graylog server](https://www.graylog.org)
* `log` transport for sending request log to [Laravel logging system](https://laravel.com/docs/5.7/logging)
* `null` transport for skip request log in application tests

## Installation

Install via composer

```bash
composer require xxxcoltxxx/request-logger
```

Publish the configuration file

```bash
php artisan vendor:publish --provider="RequestLogger\RequestLoggerServiceProvider"
```

Add exception reporting to your `app/Exception/Handler.php` file

```php
public function report(Exception $exception)
{
    request_logger()->setException($exception);

    // ...
    parent::report($exception);
}
```

Fill configuration

```php
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

    // ...
],
```

## Adding app messages to request log

You can add custom messages to payload with `request_logger` helper.

```php
request_logger()->addMessage('Full name: John Doe')
```

## Enable logging only for specific routes

Request logger enabled on all routes by default.
You can disable `all_routes` option in the configuration file and use `request_logger` middleware in routes configuration.

Documentation for registering middleware can be found in [Laravel documentation](https://laravel.com/docs/5.7/middleware#registering-middleware)

```php
# config/request_logger.php

return [
    'all_routes' => false,

    // ...
];

# routes/api.php

Route::get('admin/profile', function () {
    //
})->middleware('request_logger');
``` 

## Custom log format

For custom log format you can use closure in the configuration file:
```php
# config/request_logger.php

return [
    // ...

    'formatter' => function () {
        $provider  = request_logger();
        $exception = $provider->getException();
        $request   = $provider->getRequest();
        $response  = $provider->getResponse();

        return [
            'uri'           => $request->getRequestUri(),
            'has_exception' => ! is_null($exception),
        ];
    }

    // ...
```

## Writing custom drivers
For writing custom driver you must add driver configuration and implement `RequestLogger\Transports\RequestLoggerTransport` interface:

```php
# .env

REQUEST_LOGGER_TRANSPORT=custom

# config/request_logger.php

return [
    // ...

    'transports' => [
        // ...

        'custom' => [
            'driver' => Namespace\CustomTransport::class,
            'custom_option' => 'value',
        ],
    ],
];
```

## Disable request logger in application tests

Use `null` driver. You can use `REQUEST_LOGGER_TRANSPORT` variable in `phpunit.xml` configuration file.

## Testing

```bash
composer install
vendor/bin/phpunit tests
```

## Roadmap

1. [x] Unit tests
1. [x] Write Documentation for:
   * Custom log format
   * Writing custom drivers
1. [x] Add `log` driver
1. [x] Add `null` driver
1. [x] Add changelog
1. [ ] Make video "How it works with graylog"
