<?php

namespace RequestLogger\Transports;

interface RequestLoggerTransport
{
    public function setConfig(array $config);

    public function send(array $payload);
}
