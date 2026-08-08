<?php
declare(strict_types=1);

namespace Serapha\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Serapha\Core\Container;
use Serapha\Core\Dispatcher;

final class DispatcherTest extends TestCase
{
    protected function setUp(): void
    {
        DispatcherLifecycleBase::$constructorCalls = 0;
        DispatcherLifecycleChild::$constructorCalls = 0;
    }

    public function testResolveInitializesParentAndChildConstructorsOnSameInstance(): void
    {
        $dispatcher = new Dispatcher(new Container());

        $resolved = $dispatcher->resolve(DispatcherLifecycleChild::class);

        self::assertTrue($resolved->parentReady);
        self::assertTrue($resolved->childReady);
        self::assertSame(['parent', 'child'], $resolved->events);
    }

    public function testResolveCreatesFreshInitializedInstancesOnSubsequentCalls(): void
    {
        $dispatcher = new Dispatcher(new Container());

        $first = $dispatcher->resolve(DispatcherLifecycleChild::class);
        $second = $dispatcher->resolve(DispatcherLifecycleChild::class);

        self::assertNotSame($first, $second);
        self::assertSame(2, DispatcherLifecycleBase::$constructorCalls);
        self::assertSame(2, DispatcherLifecycleChild::$constructorCalls);
    }

    public function testResolveSupportsUnionTypedDependencies(): void
    {
        $dispatcher = new Dispatcher(new Container());

        $resolved = $dispatcher->resolve(DispatcherUnionConsumer::class);

        self::assertInstanceOf(DispatcherUnionConsumer::class, $resolved);
        self::assertInstanceOf(DispatcherTestDependency::class, $resolved->dependency);
    }
}

class DispatcherLifecycleBase
{
    public static int $constructorCalls = 0;
    public bool $parentReady = false;
    public array $events = [];

    public function __construct(DispatcherTestDependency $dependency)
    {
        self::$constructorCalls++;
        $this->parentReady = $dependency instanceof DispatcherTestDependency;
        $this->events[] = 'parent';
    }
}

final class DispatcherLifecycleChild extends DispatcherLifecycleBase
{
    public static int $constructorCalls = 0;
    public bool $childReady = false;

    public function __construct(DispatcherChildDependency $dependency)
    {
        self::$constructorCalls++;
        $this->childReady = $this->parentReady && $dependency instanceof DispatcherChildDependency;
        $this->events[] = 'child';
    }
}

final class DispatcherUnionConsumer
{
    public function __construct(public DispatcherTestDependency|string $dependency)
    {
    }
}

final class DispatcherTestDependency
{
}

final class DispatcherChildDependency
{
}