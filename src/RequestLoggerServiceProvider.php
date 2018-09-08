<?php

namespace RequestLogger;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use RequestLogger\Middleware\RequestLogMiddleware;

class RequestLoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/request_logger.php' => config_path('request_logger.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/request_logger.php', 'request_logger'
        );

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('request_logger', RequestLogMiddleware::class);

        if (config('request_logger.all_routes')) {
            /** @var Kernel $kernel */
            $kernel = $this->app[Kernel::class];
            $kernel->pushMiddleware(RequestLogMiddleware::class);
        }
    }

    public function register()
    {
        $this->app->singleton(RequestDataProvider::class);

        $this->app->singleton('request-logger', function () {
            return resolve(RequestLoggerManager::class);
        });
    }
}
