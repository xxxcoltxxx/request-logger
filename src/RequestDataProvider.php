<?php

namespace RequestLogger;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestDataProvider
{
    protected $messages = [];

    protected $duration = 0;

    protected $exception;

    /** @var Request */
    protected $request;

    /** @var null|Response */
    protected $response;

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

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
