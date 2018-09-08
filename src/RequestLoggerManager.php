<?php

namespace RequestLogger;

use InvalidArgumentException;

class RequestLoggerManager
{
    protected $transport;

    public function send(array $payload)
    {
        $transport = static::driver(config('request_logger.default'));
        $transport(config('request_logger.short_message', 'requests'), $payload);
    }

    public function driver(string $name)
    {
        $config = config("request_logger.transports.{$name}");

        if (! $config) {
            throw new InvalidArgumentException("Driver {$name} not found");
        }

        $driverClass = $config['driver'];
        unset($config['driver']);

        return new $driverClass($config);
    }
}
