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
     * Determine if URL rewriting is enabled.
     *
     * @return bool
     */
    public static function isRewriteEnabled(): bool
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Remove the query string from REQUEST_URI if it exists
        $requestPath = explode('?', $requestUri, 2)[0];
        $indexInRequest = strpos($requestPath, basename($scriptName)) !== false;
        $directIndexUsage = preg_match('/\/(?:\/|\?)/', $requestUri);

        return !$indexInRequest && !$directIndexUsage;
    }
}
