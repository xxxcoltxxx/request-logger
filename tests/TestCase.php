<?php

namespace Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;
use RequestLogger\RequestLoggerServiceProvider;

class TestCase extends Orchestra
{
    protected $uri = '__request_logger';

    protected $response = ['items' => [1, 3]];

    protected function setUp()
    {
        parent::setUp();
        $this->setConfig('default', null);
        $this->setConfig('all_routes', true);

        Route::put($this->uri, function () {
            return response()->json($this->response, 201, [
                'x-response-header' => 'response-header-value',
            ]);
        });
    }

    protected function setConfig(string $path, $value)
    {
        Config::set("request_logger.{$path}", $value);
    }

    protected function getPackageProviders($app)
    {
        return [RequestLoggerServiceProvider::class];
    }
}
