<?php
declare(strict_types=1);

namespace Serapha\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Serapha\Controller\ControllerDispatcher;
use Serapha\Core\Container;
use Serapha\Routing\Response;

final class ControllerDispatcherTest extends TestCase
{
    public function testDispatchInvokesResolvedControllerMethod(): void
    {
        $dispatcher = new ControllerDispatcher(new Container());

        $result = $dispatcher->dispatch(ControllerDispatchChild::class, 'show', ['ok']);

        self::assertSame('base:ready-child:ready-ok', (string) $result->getBody());
    }
}

class ControllerDispatchBase
{
    protected bool $baseReady = false;

    public function __construct(ControllerDispatchBaseDependency $dependency)
    {
        $this->baseReady = $dependency instanceof ControllerDispatchBaseDependency;
    }
}

final class ControllerDispatchChild extends ControllerDispatchBase
{
    private bool $childReady = false;

    public function __construct(ControllerDispatchChildDependency $dependency)
    {
        $this->childReady = $this->baseReady && $dependency instanceof ControllerDispatchChildDependency;
    }

    public function show(string $value): Response
    {
        $response = new Response();
        $response->getBody()->write(sprintf(
            'base:%s-child:%s-%s',
            $this->baseReady ? 'ready' : 'missing',
            $this->childReady ? 'ready' : 'missing',
            $value
        ));

        return $response;
    }
}

final class ControllerDispatchBaseDependency
{
}

final class ControllerDispatchChildDependency
{
}
