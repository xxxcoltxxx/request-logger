<?php

namespace Tests;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Mockery;
use RequestLogger\Transports\GelfUdpTransport;

class RequestLoggerTest extends TestCase
{
    protected $uri = '__request_logger';

    protected $response = ['items' => [1, 3]];

    protected $actualPayload;

    protected function setUp()
    {
        parent::setUp();

        Route::put($this->uri, function () {
            return response()->json($this->response, 201, ['x-response-header' => 'response-header-value']);
        });

        $transport = Mockery::mock(GelfUdpTransport::class)->makePartial();
        $transport->shouldReceive('send')->once()->withArgs(function (array $args) {
            $this->actualPayload = $args;

            return true;
        });
        $this->app->instance(GelfUdpTransport::class, $transport);
    }

    public function test_route_works()
    {
        $response = $this->putJson($this->uri);
        $this->assertEquals($this->response, $response->json());
        $response->assertHeader('x-response-header', 'response-header-value');
    }

    public function test_method()
    {
        $this->putJson($this->uri)->assertSuccessful();
        $this->assertEquals('PUT', $this->actualPayload['request_method']);
    }

    public function test_query_params()
    {
        $this->putJson($this->uri . '?query_param=query_value')->assertSuccessful();
        $this->assertEquals(
            ['query_param' => 'query_value'],
            $this->actualPayload['request_params']
        );
    }

    public function test_body()
    {
        $this->putJson($this->uri, ['body_param' => 'body_value'])->assertSuccessful();
        $this->assertEquals(
            ['body_param' => 'body_value'],
            $this->actualPayload['request_params']
        );
    }

    public function test_headers()
    {
        $this->putJson($this->uri, [], ['x-custom-header' => 'header-value'])->assertSuccessful();

        $headers = $this->actualPayload['request_headers'];
        $this->assertArrayHasKey('x-custom-header', $headers);
        $this->assertEquals(['header-value'], $headers['x-custom-header']);
    }

    public function test_uploaded_file()
    {
        $file = UploadedFile::fake()->create('image.jpg', 100);

        $this->putJson($this->uri, ['file' => $file])->assertSuccessful();

        $this->assertEquals(
            ['file' => ['name' => 'image.jpg', 'bytes' => 100 * 1024, 'mime' => 'image/jpeg']],
            $this->actualPayload['request_files']
        );
    }

    public function test_response_content()
    {
        $this->putJson($this->uri)->assertSuccessful();
        $this->assertEquals(json_encode($this->response), $this->actualPayload['response_content']);
    }

    public function test_response_status()
    {
        $this->putJson($this->uri)->assertSuccessful();
        $this->assertEquals(201, $this->actualPayload['response_status']);
    }

    public function test_response_header()
    {
        $this->putJson($this->uri)->assertSuccessful();

        $headers = $this->actualPayload['response_headers'];
        $this->assertArrayHasKey('x-response-header', $headers);
        $this->assertEquals(['response-header-value'], $headers['x-response-header']);
    }

    public function test_auth_id()
    {
        Auth::shouldReceive('id')->andReturn(10);
        $this->putJson($this->uri)->assertSuccessful();
        $this->assertEquals(10, $this->actualPayload['auth_id']);
    }

    public function test_host()
    {
        Config::set('app.url', 'https://host.com');

        $this->putJson($this->uri)->assertSuccessful();

        $this->assertEquals('https://host.com', $this->actualPayload['host']);
    }

    public function test_ip()
    {
        $ip = '94.11.22.33';
        $this->call('PUT', $this->uri, [], [], [], ['REMOTE_ADDR' => $ip])->assertSuccessful();

        $this->assertEquals($ip, $this->actualPayload['ip']);
    }

    public function test_url()
    {
        $this->putJson($this->uri)->assertSuccessful();

        $this->assertEquals($this->uri, $this->actualPayload['url']);
    }

    public function test_exception()
    {
        $exception = new Exception('Custom exception message', 400);
        Route::put($this->uri, function () use ($exception) {
            request_logger()->setException($exception);
            throw $exception;
        });

        $this->putJson($this->uri);
        $actualException = $this->actualPayload['exception'];
        $this->assertEquals($exception->getFile(), $actualException['file']);
        $this->assertEquals($exception->getLine(), $actualException['line']);
        $this->assertEquals($exception->getMessage(), $actualException['message']);
        $this->assertEquals($exception->getCode(), $actualException['code']);
        $this->assertEquals($exception->getTraceAsString(), $actualException['trace']);
    }

    public function test_duration()
    {
        Carbon::setTestNow(Carbon::parse('2018-09-10 10:00:04.100'));
        Route::put($this->uri, function () {
            Carbon::setTestNow(Carbon::parse('2018-09-10 10:00:04.200'));
        });

        $this->putJson($this->uri)->assertSuccessful();
        $this->assertEquals(0.1, $this->actualPayload['duration']);
    }

    public function test_messages()
    {
        Route::put($this->uri, function () {
            request_logger()->addMessage('Message 1');
            request_logger()->addMessage(['nickname' => 'John Doe']);
        });

        $this->putJson($this->uri)->assertSuccessful();
        $this->assertEquals([
            'Message 1', ['nickname' => 'John Doe'],
        ], $this->actualPayload['messages']);
    }
}
