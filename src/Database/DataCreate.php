<?php
namespace Serapha\Database;

use carry0987\Sanite\Models\DataCreateModel;

final class DataCreate extends DataCreateModel
{
    public function createSingle(array $queryArray, array $dataArray, bool $getAutoIncrement = false)
    {
        return $this->createSingleData($queryArray, $dataArray, $getAutoIncrement);
    }

    public function createMultiple(array $queryArray, array $dataArray)
    {
        return $this->createMultipleData($queryArray, $dataArray);
    }
}
