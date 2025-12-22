<?php
declare(strict_types=1);

namespace Serapha\Database;

use carry0987\Sanite\Models\DataCreateModel;

final class DataCreate extends DataCreateModel
{
    public function createSingle(array $queryArray, array $dataArray, bool $getAutoIncrement = false, ?string $sequenceName = null)
    {
        return $this->createSingleData($queryArray, $dataArray, $getAutoIncrement, $sequenceName);
    }

    public function createMultiple(array $queryArray, array $dataArray)
    {
        return $this->createMultipleData($queryArray, $dataArray);
    }
}
