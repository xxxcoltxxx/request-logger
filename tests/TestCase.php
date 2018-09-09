<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RequestLogger\RequestLoggerServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [RequestLoggerServiceProvider::class];
    }
}
