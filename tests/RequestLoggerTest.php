<?php

namespace Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Mockery;
use RequestLogger\Transports\NullTransport;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequestLoggerTest extends TestCase
{
    protected $actualPayload;

    protected function setUp()
    {
        parent::setUp();

        $transport = Mockery::mock(NullTransport::class)->makePartial();
        $transport->shouldReceive('send')->once()->withArgs(function (array $args) {
            $this->actualPayload = $args;

            return true;
        });
        $this->app->instance(NullTransport::class, $transport);
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
        $this->putJson("{$this->uri}?query_param=query_value")->assertSuccessful();
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

    public function test_uri()
    {
        $this->putJson($this->uri)->assertSuccessful();
        $this->assertEquals("/{$this->uri}", $this->actualPayload['uri']);
    }

    public function test_exception()
    {
        $exception = new HttpException(Response::HTTP_BAD_REQUEST, 'Test exception message');
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

    public function test_response_content_limit()
    {
        $this->setConfig('transports.graylog.content_limit', 10000);
        Route::put($this->uri, function () {
            return str_repeat('t', 50000);
        });

        $this->put($this->uri)->assertSuccessful();
        $this->assertEquals(str_repeat('t', 10000) . '...', $this->actualPayload['response_content']);
    }

    public function test_hide_passwords()
    {
        $this->putJson($this->uri, ['login' => 'j.doe', 'password' => str_random(8)])->assertSuccessful();
        $request_params = $this->actualPayload['request_params'];
        $this->assertEquals(str_repeat('*', 8), $request_params['password']);
        $this->assertEquals('j.doe', $request_params['login']);
    }

    public function test_custom_log_format()
    {
        // Set custom formatter closure
        $this->setConfig('formatter', function () {
            $provider = request_logger();
            $exception = $provider->getException();
            $request = $provider->getRequest();
            $response = $provider->getResponse();

            return [
                'exception_message' => $exception->getMessage(),
                'request_uri'       => $request->getRequestUri(),
                'response_status'   => $response->getStatusCode(),
            ];
        });

        // Register route that throw exception and put it to logger
        $exception = new HttpException(Response::HTTP_BAD_REQUEST, 'Test exception message');
        Route::put($this->uri, function () use ($exception) {
            request_logger()->setException($exception);
            throw $exception;
        });

        $this->putJson($this->uri)->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertEquals([
            'exception_message' => $exception->getMessage(),
            'request_uri'       => "/{$this->uri}",
            'response_status'   => Response::HTTP_BAD_REQUEST,
        ], $this->actualPayload);
    }
}
