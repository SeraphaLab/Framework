<?php
namespace Serapha\Database;

use carry0987\Sanite\Models\DataReadModel;

final class DataRead extends DataReadModel
{
    public function readSingle(array $queryArray, ?array $dataArray = null)
    {
        return $this->getSingleData($queryArray, $dataArray);
    }

    public function readMultiple(array $queryArray, ?array $dataArray = null)
    {
        return $this->getMultipleData($queryArray, $dataArray);
    }
}
