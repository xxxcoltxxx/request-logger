<?php

namespace RequestLogger\Middleware;

use RequestLogger\RequestFormatter;
use RequestLogger\RequestDataProvider;
use Closure;

class RequestLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var RequestDataProvider $logger */
        $logger = resolve(RequestDataProvider::class);
        $start = microtime(true);

        $response = $next($request);

        $formatter = new RequestFormatter();
        $logger->setDuration(microtime(true) - $start);

        resolve('request-logger')->send($formatter($request, $response));

        return $response;
    }
}
