<?php
namespace Serapha\Database;

use carry0987\Sanite\Sanite;

final class DB
{
    private \PDO $dbConnection;

    public function __construct(Sanite|\PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection instanceof Sanite ? $dbConnection->getConnection() : $dbConnection;
    }

    // Create Operations
    public function create(string $query, string $bindTypes, array $data)
    {
        $dataCreate = new DataCreate($this->dbConnection);

        return $dataCreate->createSingle(['query' => $query, 'bind' => $bindTypes], $data);
    }

    // Read Operations
    public function read(string $query, string $bindTypes, array $conditions = [])
    {
        $dataRead = new DataRead($this->dbConnection);

        return $dataRead->readSingle(['query' => $query, 'bind' => $bindTypes], $conditions);
    }

    // Update Operations
    public function update(string $query, string $bindTypes, array $data)
    {
        $dataUpdate = new DataUpdate($this->dbConnection);

        return $dataUpdate->updateSingle(['query' => $query, 'bind' => $bindTypes], $data);
    }

    // Delete Operations
    public function delete(string $query, string $bindTypes, array $conditions)
    {
        $dataDelete = new DataDelete($this->dbConnection);

        return $dataDelete->deleteSingle(['query' => $query, 'bind' => $bindTypes], $conditions);
    }
}
