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
        $logger = request_logger();
        $logger->setRequest($request);
        $start = now();

        $response = $next($request);

        $finish = now();
        $logger->setDuration(
            ($finish->timestamp + $finish->micro / 1000000) - ($start->timestamp + $start->micro / 1000000)
        );
        $logger->setResponse($response);
        resolve('request-logger')->send();

        return $response;
    }
}
