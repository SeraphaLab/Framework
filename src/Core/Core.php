<?php
declare(strict_types=1);

namespace Serapha\Core;

use Serapha\Service\ServiceLocator;
use Serapha\Model\ModelLocator;
use Serapha\Template\Template;
use Serapha\Routing\Router;
use Serapha\Utils\Utils;
use Serapha\Exception\InitializationException;
use carry0987\Sanite\Sanite;
use carry0987\I18n\I18n;
use carry0987\Redis\RedisTool;
use carry0987\SessionManager\SessionManager;

final class Core
{
    private Container $container;

    public function __construct(array $coreConfig = [])
    {
        // Get configurations
        [$configFile, $routePath, $langPath, $cachePath] = self::setConfig($coreConfig);

        // Load configuration
        $config = new Config(Utils::trimPath('/'.$configFile));

        // Initialize container
        $this->container = new Container();

        // Register dependencies in the container
        $this->container->singleton(Config::class, fn() => $config);
        $this->container->singleton(Sanite::class, fn() => new Sanite([
            'host' => $config->get('DB_HOST'),
            'database' => $config->get('DB_NAME'),
            'username' => $config->get('DB_USER'),
            'password' => $config->get('DB_PASSWORD'),
            'port' => $config->get('DB_PORT')
        ]));
        $this->container->singleton(RedisTool::class, fn() => self::setRedis($config));
        $this->container->singleton(Template::class, fn($container) => new Template($container));
        $this->container->singleton(I18n::class, fn() => new I18n([
            'langFilePath' => Utils::trimPath('/'.$langPath),
            'cachePath' => Utils::trimPath('/'.$cachePath.'/lang'),
            'useAutoDetect' => true,
            'cookie' => [
                'name' => 'lang',
                'expire' => time()+86400,
                'path' => Utils::trimPath(dirname($_SERVER['PHP_SELF'], 2).'/'),
                'domain' => '',
                'httponly' => true
            ]
        ]));
        $this->container->singleton(SessionManager::class, fn() => new SessionManager(Utils::xxHash($_SERVER['PHP_SELF']), [
            'path' => Utils::trimPath(dirname($_SERVER['PHP_SELF'], 2).'/'),
            'secure' => Utils::checkHttps(),
            'samesite' => 'Strict'
        ]));
        $this->container->singleton(Router::class, fn($container) => new Router($container, $routePath));

        // Register the container for loactor
        ServiceLocator::setContainer($this->container);
        ModelLocator::setContainer($this->container);
    }

    /**
     * Run the application.
     * @return void 
     * @throws \InitializationException 
     * @throws \IOException 
     */
    public function run(string $query = '/'): void
    {
        // Get the router instance and dispatch the query
        /** @var Router */
        $router = $this->container->get(Router::class);
        // Dispatch the query
        $router->handleRequest($query);
    }

    /**
     * Get the dependency injection Container.
     * 
     * This method returns the dependency injection container which holds all the
     * registered services and their instances. The container is responsible for
     * automatically resolving dependencies when instantiating objects.
     * 
     * Usage:
     * $container = $core->getContainer();
     * $router = $container->get(\Serapha\Routing\Router::class);
     * 
     * This allows accessing any registered service or class without the need
     * to manually pass dependencies around.
     * 
     * @return Container The dependency injection container.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    private static function setConfig(array $coreConfig): array
    {
        // Initialize variables
        $configFile = dirname(__DIR__, 5).'/';
        $routePath = dirname(__DIR__, 5).'/';
        $langPath = dirname(__DIR__, 5).'/';
        $cachePath = dirname(__DIR__, 5).'/';

        // Check coreConfig first, then fallback to $_ENV, and finally use default values
        if (isset($coreConfig['configFile'])) {
            $configFile = $coreConfig['configFile'];
        } else {
            $configFile .= $_ENV['CONFIG_FILE'] ?? '/config/config.inc.php';
            $configFile = Utils::trimPath($configFile);
        }

        if (isset($coreConfig['routePath'])) {
            $routePath = $coreConfig['routePath'];
        } else {
            $routePath .= $_ENV['ROUTE_PATH'] ?? '/app/Route';
            $routePath = Utils::trimPath($routePath . '/*.php');
        }

        if (isset($coreConfig['langPath'])) {
            $langPath = $coreConfig['langPath'];
        } else {
            $langPath .= $_ENV['LANG_PATH'] ?? '/lang';
            $langPath = Utils::trimPath($langPath);
        }

        if (isset($coreConfig['cachePath'])) {
            $cachePath = $coreConfig['cachePath'];
        } else {
            $cachePath .= $_ENV['CACHE_PATH'] ?? '/storage/cache';
            $cachePath = Utils::trimPath($cachePath);
        }

        return [$configFile, $routePath, $langPath, $cachePath];
    }

    private static function setRedis(Config $config): RedisTool
    {
        // Redis configuration
        try {
            return new RedisTool([
                'host' => $config->get('REDIS_HOST'),
                'port' => $config->get('REDIS_PORT'),
                'password' => $config->get('REDIS_PASSWORD'),
                'database' => $config->get('REDIS_DATABASE')
            ]);
        } catch (\Exception $e) {
            throw new InitializationException('Failed to connect to Redis server.', 0, $e);
        }
    }
}
