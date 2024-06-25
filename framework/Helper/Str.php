<?php
namespace Serapha\Helper;

class Str
{
    /**
     * Convert a string to camel case.
     *
     * @param string $string
     * @return string
     */
    public static function camelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
    }

    /**
     * Convert a string to snake case.
     *
     * @param string $string
     * @return string
     */
    public static function snakeCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param string $string
     * @param int $limit
     * @param string $end
     * @return string
     */
    public static function limit(string $string, int $limit, string $end = '...'): string
    {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }

        return mb_substr($string, 0, $limit) . $end;
    }
}
