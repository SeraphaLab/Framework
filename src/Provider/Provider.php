<?php
declare(strict_types=1);

namespace Serapha\Provider;

use Serapha\Core\Container;

abstract class Provider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    abstract public function register(): void;

    abstract public function boot(): void;
}
