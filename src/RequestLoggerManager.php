<?php

namespace RequestLogger;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;

class RequestLoggerManager
{
    protected $transport;

    public function send()
    {
        /** @var RequestFormatter $formatter */
        $formatter = resolve(RequestFormatter::class);
        $transport = static::driver(config('request_logger.default'));
        $transport($formatter());
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
