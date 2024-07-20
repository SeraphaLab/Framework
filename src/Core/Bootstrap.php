<?php
declare(strict_types=1);

namespace Serapha\Core;

use Dotenv\Dotenv;

final class Bootstrap
{
    public static function init(string $envPath): void
    {
        // Set error reporting
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('log_errors', 1);

        // Load environment variables
        $dotenv = Dotenv::createImmutable($envPath);
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_PORT']);

        // Check if debug mode is enabled
        if (!isset($_ENV['DEBUG']) || $_ENV['DEBUG'] !== 'true') {
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
        }

        // Set the default timezone
        date_default_timezone_set('UTC');
    }
}
