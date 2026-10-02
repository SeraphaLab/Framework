<?php
declare(strict_types=1);

namespace Serapha\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Serapha\Core\Container;
use Serapha\Middleware\Middleware;
use Serapha\Routing\Handler;
use Serapha\Routing\Request;
use Serapha\Routing\Response;
use Serapha\Routing\ResponseEmitter;
use Serapha\Routing\Route;
use Serapha\Tests\Support\FakeHttpOutput;

final class ResponsePipelineTest extends TestCase
{
    public function testRedirectingMiddlewareSkipsDownstreamHandler(): void
    {
        $middleware = new RedirectingMiddleware();
        $downstreamCalled = false;
        $handler = new Handler(static function (Request $request, Response $response) use (&$downstreamCalled): Response {
            $downstreamCalled = true;

            return $response;
        });

        $response = $middleware->process(new Request(), new Response(), $handler);

        self::assertFalse($downstreamCalled);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeaderLine('Location'));
    }

    public function testEmitterReceivesControllerStyleRedirectResponse(): void
    {
        $output = new FakeHttpOutput();
        $response = (new Response())->withRedirect('/dashboard', 303);
        $emitter = new ResponseEmitter($output);

        $emitter->emit($response);

        self::assertSame(303, $output->statusCode);
        self::assertSame([['Location: /dashboard', false]], $output->headers);
    }

    public function testRouteDispatchEmitsControllerRedirectResponse(): void
    {
        $output = new FakeHttpOutput();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/response-pipeline-controller';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        Route::get('/response-pipeline-controller', static fn(): Response => (new Response())->withRedirect('/dashboard'));
        $emitter = new ResponseEmitter($output);

        Route::dispatch(new Container(), $emitter);

        self::assertSame(302, $output->statusCode);
        self::assertSame([['Location: /dashboard', false]], $output->headers);
    }

    public function testRouteDispatchEmitsBadRequestResponseWithoutTerminating(): void
    {
        $output = new FakeHttpOutput();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/invalid?one?two';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $emitter = new ResponseEmitter($output);

        Route::dispatch(new Container(), $emitter);

        self::assertSame(400, $output->statusCode);
        self::assertSame([['X-Powered-By: Serapha', false]], $output->headers);
        self::assertSame('URL contains multiple question marks', $output->body);
    }
}

final class RedirectingMiddleware extends Middleware
{
    public function process(Request $request, Response $response, Handler $handler): Response
    {
        return $response->withRedirect('/login');
    }
}
