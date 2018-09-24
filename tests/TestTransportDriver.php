<?php

namespace Tests;

use RequestLogger\Transports\RequestLoggerTransport;

class TestTransportDriver implements RequestLoggerTransport
{
    public $config;
    public $sent = false;

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function send(array $payload)
    {
        $this->sent = true;
    }
}
