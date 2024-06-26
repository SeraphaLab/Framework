<?php
namespace Serapha\Model;

use Serapha\Database\DB;

abstract class Model
{
    protected DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }
}
