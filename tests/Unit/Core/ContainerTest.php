<?php
declare(strict_types=1);

namespace Serapha\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Serapha\Core\Container;
use Serapha\Exception\ContainerException;

final class ContainerTest extends TestCase
{
    public function testSingletonReturnsSameInstance(): void
    {
        $container = new Container();
        $factoryCalls = 0;

        $container->singleton(ContainerTestDependency::class, function (Container $receivedContainer) use ($container, &$factoryCalls) {
            $factoryCalls++;
            TestCase::assertSame($container, $receivedContainer);

            return new ContainerTestDependency();
        });

        $first = $container->get(ContainerTestDependency::class);
        $second = $container->get(ContainerTestDependency::class);

        self::assertSame($first, $second);
        self::assertSame(1, $factoryCalls);
    }

    public function testBindClosureReceivesContainer(): void
    {
        $container = new Container();
        $captured = null;

        $container->bind('demo.binding', function (Container $receivedContainer) use (&$captured) {
            $captured = $receivedContainer;

            return new \stdClass();
        });

        $resolved = $container->get('demo.binding');

        self::assertInstanceOf(\stdClass::class, $resolved);
        self::assertSame($container, $captured);
    }

    public function testResolveSupportsUnionTypedDependencies(): void
    {
        $container = new Container();

        $resolved = $container->get(ContainerUnionConsumer::class);

        self::assertInstanceOf(ContainerUnionConsumer::class, $resolved);
        self::assertInstanceOf(ContainerTestDependency::class, $resolved->dependency);
    }

    public function testGetThrowsContainerExceptionForUnresolvableAbstract(): void
    {
        $this->expectException(ContainerException::class);

        (new Container())->get(ContainerAbstractDependency::class);
    }
}

final class ContainerTestDependency
{
}

final class ContainerUnionConsumer
{
    public function __construct(public ContainerTestDependency|string $dependency)
    {
    }
}

abstract class ContainerAbstractDependency
{
}