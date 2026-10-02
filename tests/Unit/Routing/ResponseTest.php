<?php
declare(strict_types=1);

namespace Serapha\Tests\Unit\Routing;

use PHPUnit\Framework\TestCase;
use Serapha\Routing\Response;

final class ResponseTest extends TestCase
{
    public function testWithRedirectCreatesImmutableRedirectResponse(): void
    {
        $response = new Response();

        $redirect = $response->withRedirect('/login');

        self::assertNotSame($response, $redirect);
        self::assertSame(200, $response->getStatusCode());
        self::assertFalse($response->hasHeader('Location'));
        self::assertSame(302, $redirect->getStatusCode());
        self::assertSame('/login', $redirect->getHeaderLine('Location'));
    }

    public function testWithRedirectSupportsOtherRedirectStatuses(): void
    {
        $redirect = (new Response())->withRedirect('/saved', 303);

        self::assertSame(303, $redirect->getStatusCode());
        self::assertSame('/saved', $redirect->getHeaderLine('Location'));
    }

    public function testWithRedirectRejectsNonRedirectStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('A redirect status code must be in the 3xx range.');

        (new Response())->withRedirect('/login', 200);
    }
}
