<?php
namespace Serapha\Helper;

class Arr
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array $array, string $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(array &$array, string $key, $value): void
    {
        if (is_null($key)) {
            $array = $value;
            return;
        }

        $keys = explode('.', $key);
        foreach ($keys as $i => $k) {
            if (count($keys) === 1) {
                $array[$k] = $value;
            } else {
                unset($keys[$i]);
                if (!isset($array[$k]) || !is_array($array[$k])) {
                    $array[$k] = [];
                }
                $array = &$array[$k];
            }
        }
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    public static function forget(array &$array, $keys): void
    {
        $original =& $array;
        $keys = (array) $keys;
        if (count($keys) === 0) {
            return;
        }
        foreach ($keys as $key) {
            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array =& $array[$part];
                } else {
                    continue 2;
                }
            }
            unset($array[array_shift($parts)]);
            $array =& $original;
        }
    }
}
