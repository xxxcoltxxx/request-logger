<?php

namespace RequestLogger;

use Exception;

class RequestDataProvider
{
    protected $messages = [];

    protected $duration = 0;

    protected $exception;

    public function addMessage($message)
    {
        $this->messages[] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function setDuration(float $duration): void
    {
        $this->duration = $duration;
    }

    public function getException(): ?Exception
    {
        return $this->exception;
    }

    public function setException(Exception $exception): void
    {
        $this->exception = $exception;
    }
}
