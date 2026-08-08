<?php
declare(strict_types=1);

namespace Serapha\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Serapha\Controller\ControllerDispatcher;
use Serapha\Core\Container;

final class ControllerDispatcherTest extends TestCase
{
    public function testDispatchInvokesResolvedControllerMethod(): void
    {
        $dispatcher = new ControllerDispatcher(new Container());

        $result = $dispatcher->dispatch(ControllerDispatchChild::class, 'show', ['ok']);

        self::assertSame('base:ready-child:ready-ok', $result);
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

    public function show(string $value): string
    {
        return sprintf(
            'base:%s-child:%s-%s',
            $this->baseReady ? 'ready' : 'missing',
            $this->childReady ? 'ready' : 'missing',
            $value
        );
    }
}

final class ControllerDispatchBaseDependency
{
}

final class ControllerDispatchChildDependency
{
}