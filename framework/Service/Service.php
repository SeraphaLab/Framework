<?php
namespace Serapha\Service;

use Serapha\Database\DB;
use carry0987\Sanite\Sanite;

abstract class Service
{
    protected DB $db;

    public function __construct()
    {
        $sanite = ServiceDispatcher::resolve(Sanite::class);
        $this->db = new DB($sanite);
    }
}
