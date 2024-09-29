<?php
declare(strict_types=1);

namespace Serapha\Database;

use carry0987\Sanite\Sanite;

final class DB
{
    private \PDO $dbConnection;

    public function __construct(Sanite|\PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection instanceof Sanite ? $dbConnection->getConnection() : $dbConnection;
    }

    // Get the PDO connection
    public function getConnection(): \PDO
    {
        return $this->dbConnection;
    }

    // Create Operations
    public function create(string $query, string $bindTypes, array $data, bool $getAutoIncrement = false)
    {
        $dataCreate = new DataCreate($this->dbConnection);

        return $dataCreate->createSingle(['query' => $query, 'bind' => $bindTypes], $data, $getAutoIncrement);
    }

    // Create multiple rows
    public function createAll(string $query, string $bindTypes, array $data)
    {
        $dataCreate = new DataCreate($this->dbConnection);

        return $dataCreate->createMultiple(['query' => $query, 'bind' => $bindTypes], $data);
    }

    // Read Operations
    public function read(string $query, string|null $bindTypes, ?array $conditions = null)
    {
        $dataRead = new DataRead($this->dbConnection);

        return $dataRead->readSingle(['query' => $query, 'bind' => $bindTypes], $conditions);
    }

    // Read multiple rows
    public function readAll(string $query, string|null $bindTypes, ?array $conditions = null)
    {
        $dataRead = new DataRead($this->dbConnection);

        return $dataRead->readMultiple(['query' => $query, 'bind' => $bindTypes], $conditions);
    }

    // Count rows
    public function count(string $query, string|null $bindTypes, ?array $conditions = null)
    {
        $dataRead = new DataRead($this->dbConnection);

        return $dataRead->countData(['query' => $query, 'bind' => $bindTypes], $conditions);
    }

    // Update Operations
    public function update(string $query, string $bindTypes, array $data)
    {
        $dataUpdate = new DataUpdate($this->dbConnection);

        return $dataUpdate->updateSingle(['query' => $query, 'bind' => $bindTypes], $data);
    }

    // Update multiple rows
    public function updateAll(string $query, string $bindTypes, array $data)
    {
        $dataUpdate = new DataUpdate($this->dbConnection);

        return $dataUpdate->updateMultiple(['query' => $query, 'bind' => $bindTypes], $data);
    }

    // Delete Operations
    public function delete(string $query, string $bindTypes, array $conditions)
    {
        $dataDelete = new DataDelete($this->dbConnection);

        return $dataDelete->deleteSingle(['query' => $query, 'bind' => $bindTypes], $conditions);
    }

    // Delete multiple rows
    public function deleteAll(string $query, string $bindTypes, array $conditions)
    {
        $dataDelete = new DataDelete($this->dbConnection);

        return $dataDelete->deleteMultiple(['query' => $query, 'bind' => $bindTypes], $conditions);
    }
}
