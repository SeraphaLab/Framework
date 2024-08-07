<?php
declare(strict_types=1);

namespace Serapha\Service;

use Serapha\Database\DB;

abstract class Service
{
    protected DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }
}
