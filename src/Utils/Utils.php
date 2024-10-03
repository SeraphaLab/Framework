<?php
declare(strict_types=1);

namespace Serapha\Utils;

use carry0987\Utils\Utils as BaseUtils;

final class Utils extends BaseUtils
{
    /**
     * Generate a random string.
     *
     * @param int $length
     * @return string
     */
    public static function randomString(int $length = 16): string
    {
        return parent::generateRandom($length);
    }

    /**
     * Sanitize input to prevent XSS.
     *
     * @param string $input
     * @return string
     */
    public static function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if the request is HTTPS.
     *
     * @return bool
     */
    public static function checkHttps(): bool
    {
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') {
            return true;
        } elseif (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $visitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
            if ($visitor->scheme === 'https') return true;
        }

        return false;
    }

    /**
     * Redirect to a given URL.
     *
     * @param string $url
     * @return void
     */
    public static function redirect(string $url): void
    {
        parent::redirectURL($url);
    }

    /**
     * Get the base path of the application.
     *
     * @param int $levels
     * @return string
     */
    public static function getBasePath(int $levels = 2): string
    {
        if ($levels === 0) return $_SERVER['PHP_SELF'];

        return dirname($_SERVER['PHP_SELF'], $levels);
    }

    /**
     * Determine if URL rewriting is enabled.
     *
     * @return bool
     */
    public static function isRewriteEnabled(): bool
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptPath = str_replace('index.php', '', $scriptName);
        $scriptPath = rtrim($scriptPath, '/');

        // Check if the script name is in the request
        $checkPublic = substr($scriptPath, strrpos($scriptPath, '/') + 1);
        if ($scriptPath === rtrim($requestUri, '/') && $checkPublic === 'public') {
            return false;
        }

        // Remove the query string from REQUEST_URI if it exists
        $requestPath = explode('?', $requestUri, 2)[0];
        $indexInRequest = strpos($requestPath, basename($scriptName)) !== false;
        $directIndexUsage = preg_match('/\/(?:\/|\?)\//', $requestUri, $matches);

        return !$indexInRequest && !$directIndexUsage;
    }

    /**
     * Generate URL based on rewrite setting.
     *
     * @param string $path The path to append to the base URL.
     * @return string The full URL.
     */
    public static function generateUrl(string $path = '/'): string
    {
        // Base URL path without scheme and host
        $basePath = self::getBasePath();
        $basePath = rtrim($basePath, '/');
        $path = ltrim($path, '/');

        // Check if rewrite is enabled
        if (self::isRewriteEnabled()) {
            return self::getBaseHost() . $basePath . '/' . $path;
        } else {
            return self::getBaseHost() . $basePath . '/public/?/' . $path;
        }
    }

    /**
     * Get Base Host URL.
     *
     * @return string
     */
    public static function getBaseHost(): string
    {
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host;
    }
}
