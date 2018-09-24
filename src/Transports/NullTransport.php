<?php

namespace RequestLogger\Transports;

class NullTransport implements RequestLoggerTransport
{
    public function setConfig(array $config)
    {
        // Do nothing
    }

    public function send(array $payload)
    {
        // Do nothing
    }
}
