<?php

namespace RequestLogger;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestFormatter
{
    public function __invoke(Request $request, Response $response)
    {
        /** @var RequestDataProvider $logger */
        $logger = resolve(RequestDataProvider::class);
        $exception = $logger->getException();

        return [
            'request' => [
                'method'  => $request->method(),
                'params'  => $this->hidePasswords($request->input()),
                'headers' => $request->headers->all(),
            ],

            'response' => [
                'content' => $response->getContent(),
                'status'  => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ],

            'auth_id' => auth()->id(),

            'host' => ($request->isSecure() ? 'https://' : 'http://') . $request->getHttpHost(),
            'ip'   => $request->ip(),
            'url'  => $request->path(),

            'exception' => $exception ? [
                'trace'   => $exception->getTraceAsString(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
            ] : null,

            'duration' => number_format($logger->getDuration(), 3, '.', ''),
            'messages' => $logger->getMessages(),
        ];
    }

    protected function hidePasswords($params)
    {
        if (! is_array($params)) {
            return $params;
        }

        return collect($params)->map(function ($param, $key) {
            if (is_array($key)) {
                return $this->hidePasswords($param);
            }

            if (is_string($key) && strpos($key, 'password') !== false) {
                return str_repeat('*', mb_strlen($param));
            }

            return $param;
        })->toArray();
    }
}
