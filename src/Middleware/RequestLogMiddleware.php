<?php

namespace RequestLogger\Middleware;

use Closure;
use RequestLogger\RequestDataProvider;

class RequestLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var RequestDataProvider $logger */
        $logger = resolve(RequestDataProvider::class);
        $logger->setRequest($request);
        $start = microtime(true);

        $response = $next($request);

        $logger->setDuration(microtime(true) - $start);
        $logger->setResponse($response);
        resolve('request-logger')->send();

        return $response;
    }
}
