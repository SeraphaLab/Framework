<?php
declare(strict_types=1);

namespace Serapha\Model;

use Serapha\Database\DB;

abstract class Model
{
    protected DB $db;
    protected static $param = [];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public static function setParam(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            self::$param = array_merge($key, self::$param);
            return;
        }

        self::$param[$key] = $value;
    }

    public static function getParam(string $key = null)
    {
        return $key ? self::$param[$key] : self::$param;
    }
}
