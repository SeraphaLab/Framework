<?php
declare(strict_types=1);

namespace Serapha\Tests\Unit\Routing;

use PHPUnit\Framework\TestCase;
use Serapha\Routing\Response;
use Serapha\Routing\ResponseEmitter;
use Serapha\Tests\Support\FakeHttpOutput;

final class ResponseEmitterTest extends TestCase
{
    public function testEmitSendsStatusHeadersAndBody(): void
    {
        $output = new FakeHttpOutput();
        $response = new Response(201, ['X-Test' => ['one', 'two']]);
        $response->getBody()->write('created');
        $emitter = new ResponseEmitter($output);

        $emitter->emit($response);

        self::assertSame(201, $output->statusCode);
        self::assertSame([['X-Test: one', false], ['X-Test: two', false]], $output->headers);
        self::assertSame('created', $output->body);
    }

    public function testEmitFailsWhenHeadersWereAlreadySent(): void
    {
        $output = new FakeHttpOutput();
        $output->headersAlreadySent = true;
        $emitter = new ResponseEmitter($output);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Cannot emit response: headers have already been sent.');

        $emitter->emit(new Response());
    }
}
