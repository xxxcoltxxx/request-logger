<?php

namespace RequestLogger;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class RequestFormatter
{
    public function __invoke()
    {
        /** @var RequestDataProvider $provider */
        $provider = resolve(RequestDataProvider::class);
        $exception = $provider->getException();
        $request = $provider->getRequest();
        $response = $provider->getResponse();

        return [
            'request_method'  => $request->getMethod(),
            'request_params'  => $this->hidePasswords(array_merge(
                $request->query->all(),
                $request->request->all()
            )),
            'request_files'   => $this->formatFiles($request->files->all()),
            'request_headers' => $request->headers->all(),

            'response_content' => $response ? $this->limitContent($response->getContent()) : null,
            'response_status'  => $response ? $response->getStatusCode() : null,
            'response_headers' => $response ? $response->headers->all() : null,

            'auth_id' => auth()->id(),

            'host' => ($request->isSecure() ? 'https://' : 'http://') . $request->getHttpHost(),
            'ip'   => $request->getClientIp(),
            'uri'  => $request->getRequestUri(),

            'exception' => $exception ? [
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTraceAsString(),
            ] : null,

            'duration' => number_format($provider->getDuration(), 3, '.', ''),
            'messages' => $provider->getMessages(),
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

    protected function limitContent(string $content)
    {
        $limit = config('request_logger.transports.graylog.content_limit', false);

        return $limit && mb_strlen($content) > $limit ? mb_substr($content, 0, $limit) . '...' : $content;
    }
}
