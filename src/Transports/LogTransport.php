<?php

namespace RequestLogger\Transports;

use Illuminate\Support\Facades\Log;

class LogTransport implements RequestLoggerTransport
{
    protected $config;

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function send(array $payload)
    {
        Log::channel($this->config['channel'])->info(json_encode($payload));
    }
}
