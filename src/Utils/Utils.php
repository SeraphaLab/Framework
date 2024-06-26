<?php
namespace Serapha\Utils;

use carry0987\Utils\Utils as BaseUtils;

class Utils extends BaseUtils
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
     * Redirect to a given URL.
     *
     * @param string $url
     * @return void
     */
    public static function redirect(string $url): void
    {
        parent::redirectURL($url);
    }
}
