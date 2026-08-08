<?php
declare(strict_types=1);

namespace Serapha\Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;
use Serapha\Core\Container;
use Serapha\Database\DB;
use Serapha\Model\Model;
use Serapha\Model\ModelLocator;
use Serapha\Service\Service;
use Serapha\Service\ServiceLocator;

final class LocatorResolutionTest extends TestCase
{
    protected function setUp(): void
    {
        LocatorTestModel::$constructorCalls = 0;
        LocatorTestService::$constructorCalls = 0;

        $container = new Container();
        $container->singleton(DB::class, fn() => new DB(new PDO('sqlite::memory:')));

        ModelLocator::setContainer($container);
        ServiceLocator::setContainer($container);
    }

    public function testModelLocatorResolvesFullyInitializedModel(): void
    {
        $model = ModelLocator::get(LocatorTestModel::class);

        self::assertInstanceOf(LocatorTestModel::class, $model);
        self::assertTrue($model->hasDatabaseConnection());
        self::assertTrue($model->hasChildDependency());
    }

    public function testServiceLocatorResolvesServiceAndNestedModel(): void
    {
        $service = ServiceLocator::get(LocatorTestService::class);

        self::assertInstanceOf(LocatorTestService::class, $service);
        self::assertTrue($service->hasDatabaseConnection());
        self::assertTrue($service->hasChildDependency());
        self::assertTrue($service->hasResolvedModel());
        self::assertTrue($service->resolvedModelHasDatabaseConnection());
    }

    public function testLocatorsReturnFreshInstancesOnRepeatedResolution(): void
    {
        $firstModel = ModelLocator::get(LocatorTestModel::class);
        $secondModel = ModelLocator::get(LocatorTestModel::class);
        $firstService = ServiceLocator::get(LocatorTestService::class);
        $secondService = ServiceLocator::get(LocatorTestService::class);

        self::assertNotSame($firstModel, $secondModel);
        self::assertNotSame($firstService, $secondService);
        self::assertSame(4, LocatorTestModel::$constructorCalls);
        self::assertSame(2, LocatorTestService::$constructorCalls);
    }
}

final class LocatorModelDependency
{
}

final class LocatorServiceDependency
{
}

class LocatorModelBase extends Model
{
    public function hasDatabaseConnection(): bool
    {
        return $this->db instanceof DB;
    }
}

final class LocatorTestModel extends LocatorModelBase
{
    public static int $constructorCalls = 0;
    private bool $childDependencyReady = false;

    public function __construct(LocatorModelDependency $dependency)
    {
        self::$constructorCalls++;
        $this->childDependencyReady = $this->db instanceof DB && $dependency instanceof LocatorModelDependency;
    }

    public function hasChildDependency(): bool
    {
        return $this->childDependencyReady;
    }
}

class LocatorServiceBase extends Service
{
    public function hasDatabaseConnection(): bool
    {
        return $this->db instanceof DB;
    }
}

final class LocatorTestService extends LocatorServiceBase
{
    public static int $constructorCalls = 0;
    private bool $childDependencyReady = false;
    private LocatorTestModel $model;

    public function __construct(LocatorServiceDependency $dependency)
    {
        self::$constructorCalls++;
        $this->childDependencyReady = $this->db instanceof DB && $dependency instanceof LocatorServiceDependency;
        $this->model = ModelLocator::get(LocatorTestModel::class);
    }

    public function hasChildDependency(): bool
    {
        return $this->childDependencyReady;
    }

    public function hasResolvedModel(): bool
    {
        return $this->model instanceof LocatorTestModel;
    }

    public function resolvedModelHasDatabaseConnection(): bool
    {
        return $this->model->hasDatabaseConnection();
    }
}