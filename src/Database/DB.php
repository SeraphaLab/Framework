<?php
namespace Serapha\Database;

use carry0987\Sanite\Sanite;

class DB
{
    private Sanite $sanite;

    public function __construct(Sanite $sanite)
    {
        $this->sanite = $sanite;
    }

    // Create Operations
    public function create(string $query, string $bindTypes, array $data)
    {
        $dataCreate = new DataCreate($this->sanite);

        return $dataCreate->createSingle(['query' => $query, 'bind' => $bindTypes], $data);
    }

    // Read Operations
    public function read(string $query, string $bindTypes, array $conditions = [])
    {
        $dataRead = new DataRead($this->sanite);

        return $dataRead->readSingle(['query' => $query, 'bind' => $bindTypes], $conditions);
    }

    // Update Operations
    public function update(string $query, string $bindTypes, array $data)
    {
        $dataUpdate = new DataUpdate($this->sanite);

        return $dataUpdate->updateSingle(['query' => $query, 'bind' => $bindTypes], $data);
    }

    // Delete Operations
    public function delete(string $query, string $bindTypes, array $conditions)
    {
        $dataDelete = new DataDelete($this->sanite);

        return $dataDelete->deleteSingle(['query' => $query, 'bind' => $bindTypes], $conditions);
    }
}
