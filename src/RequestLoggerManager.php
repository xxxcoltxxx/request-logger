<?php

namespace RequestLogger;

use InvalidArgumentException;
use RequestLogger\Transports\RequestLoggerTransport;

class RequestLoggerManager
{
    protected $transport;

    public function send()
    {
        /** @var RequestFormatter $formatter */
        $formatterValue = config('request_logger.formatter');
        $formatter = is_string($formatterValue) ? resolve($formatterValue) : $formatterValue;
        $transport = static::driver(config('request_logger.default') ?? 'null');
        $transport->send($formatter());
    }

    public function driver(string $name): RequestLoggerTransport
    {
        $config = config("request_logger.transports.{$name}");

        if (! $config) {
            throw new InvalidArgumentException("Driver {$name} not found");
        }

        $driverClass = $config['driver'];
        unset($config['driver']);

        $driver = resolve($driverClass);
        $driver->setConfig($config);

        return $driver;
    }
}
