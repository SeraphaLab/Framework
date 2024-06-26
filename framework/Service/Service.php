<?php
namespace Serapha\Service;

use Serapha\Database\DB;
use carry0987\Sanite\Sanite;

abstract class Service
{
    protected DB $db;

    public function __construct(Sanite $sanite)
    {
        $this->db = new DB($sanite);
    }
}
