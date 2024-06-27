<?php
namespace Serapha\Database;

use carry0987\Sanite\Models\DataUpdateModel;

final class DataUpdate extends DataUpdateModel
{
    public function updateSingle(array $queryArray, array $dataArray)
    {
        return $this->updateSingleData($queryArray, $dataArray);
    }

    public function updateMultiple(array $queryArray, array $dataArray)
    {
        return $this->updateMultipleData($queryArray, $dataArray);
    }
}
