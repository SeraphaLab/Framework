<?php
namespace Serapha\Database;

use carry0987\Sanite\Models\DataDeleteModel;

class DataDelete extends DataDeleteModel
{
    public function deleteSingle(array $queryArray, array $dataArray)
    {
        return $this->deleteSingleData($queryArray, $dataArray);
    }

    public function deleteMultiple(array $queryArray, array $dataArray)
    {
        return $this->deleteMultipleData($queryArray, $dataArray);
    }
}
