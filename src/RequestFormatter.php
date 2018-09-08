<?php

namespace RequestLogger;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class RequestFormatter
{
    public function __invoke(Request $request, Response $response)
    {
        /** @var RequestDataProvider $logger */
        $logger = resolve(RequestDataProvider::class);
        $exception = $logger->getException();

        return [
            'request_method'  => $request->method(),
            'request_params'  => $this->hidePasswords($request->input()),
            'request_files'   => $this->formatFiles($request->allFiles()),
            'request_headers' => $request->headers->all(),

            'response_content' => $response->getContent(),
            'response_status'  => $response->getStatusCode(),
            'response_headers' => $response->headers->all(),

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

    /**
     * @param array|UploadedFile[] $files
     *
     * @return array
     */
    protected function formatFiles(array $files)
    {
        return array_map(function (UploadedFile $file) {
            return [
                'name'  => $file->getClientOriginalName(),
                'bytes' => $file->getSize(),
                'mime'  => $file->getClientMimeType(),
            ];
        }, $files);
    }
}
