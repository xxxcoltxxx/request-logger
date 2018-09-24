<?php

namespace Tests;

use Illuminate\Support\Facades\Log;
use RequestLogger\RequestFormatter;

class RequestLoggerDriverTest extends TestCase
{
    public function test_custom_driver()
    {
        // Register custom driver
        $this->setConfig('default', 'custom');
        $this->setConfig('transports.custom', [
            'driver'   => TestTransportDriver::class,
            'option_1' => 10,
            'option_2' => 'ok',
        ]);

        // Register driver as singleton for access to the driver instance
        $this->app->singleton(TestTransportDriver::class);

        $this->putJson($this->uri)->assertSuccessful();

        /** @var TestTransportDriver $transport */
        $transport = resolve(TestTransportDriver::class);
        $this->assertEquals([
            'option_1' => 10,
            'option_2' => 'ok',
        ], $transport->config);
        $this->assertTrue($transport->sent);
    }

    public function test_log_driver()
    {
        $this->setConfig('default', 'log');
        $this->setConfig('transports.log.channel', 'slack');

        Log::shouldReceive('channel')->once()->withArgs(['slack'])->andReturnSelf();
        Log::shouldReceive('info')->once()->withArgs(function ($payload) {
            /** @var RequestFormatter $formatter */
            $formatter = resolve(RequestFormatter::class);
            $this->assertEquals(json_encode($formatter()), $payload);

            return true;
        });

        $this->putJson($this->uri)->assertSuccessful();
    }
}
