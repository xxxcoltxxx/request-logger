<?php

namespace RequestLogger\Transports;

use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;

class GelfUdpTransport
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __invoke(array $payload)
    {
        $transport = new UdpTransport($this->config['host'], $this->config['port']);
        $publisher = new Publisher();
        $publisher->addTransport($transport);

        $message = new Message();
        $message->setShortMessage($this->config['short_message'])
            ->setLevel(LOG_INFO)
            ->setFullMessage(json_encode($payload));

        $publisher->publish($message);
    }
}
