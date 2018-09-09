# :memo: Laravel request logger

[![Build Status](https://travis-ci.com/xxxcoltxxx/request-logger.svg?branch=master)](https://travis-ci.com/xxxcoltxxx/request-logger)
[![Latest Stable Version](https://poser.pugx.org/xxxcoltxxx/request-logger/v/stable)](https://packagist.org/packages/xxxcoltxxx/request-logger)
[![Total Downloads](https://poser.pugx.org/xxxcoltxxx/request-logger/downloads)](https://packagist.org/packages/xxxcoltxxx/request-logger)

This package allows send request and response payload to external logging system.

Supported transports out-of-box:
* Graylog server

## Installation

Install via composer

```
composer require xxxcoltxxx/request-logger
```

Publish the configuration file

```
php artisan vendor:publish --provider="RequestLogger\RequestLoggerServiceProvider"
```

Add exception reporting to your `app/Exception/Handler.php` file

```
public function report(Exception $exception)
{
    resolve(RequestDataProvider::class)->setException($exception);

    // ...
    parent::report($exception);
}
```

Fill configuration

```php
// Enable request logger for all requests without adds middleware
'all_routes' => true,

// Default transport
'default' => 'graylog',

/*
 * Allowed transports with all necessary configuration.
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
```

## Adding app messages to request log

You can add your own messages to request log payload with `request_logger` helper

```
request_logger()->addMessage('Full name: John Doe')
```

## Enable logging only for specific routes

Request logger enabled on all routes by default.
You can disable `all_routes` option in configuration file and use `request_logger` middleware on custom routes

Documentation for registering middleware can be found in [Laravel documentation](https://laravel.com/docs/5.7/middleware#registering-middleware)

```php
# config/request_logger.php

return [
    'all_routes' => false,

    # ...
];

# routes/api.php

Route::get('admin/profile', function () {
    //
})->middleware('request_logger');
``` 

## Testing

```
composer install
vendor/bin/phpunit tests
```

## Road map

1. [x] Unit tests
1. [ ] Documentation for:
   * Customization log format
   * Writing own drivers
1. [ ] Make video "How it works with graylog"
