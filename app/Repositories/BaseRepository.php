<?php

namespace App\Repositories;

class BaseRepository
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function beginTransaction()
    {
        $this->db->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->db->pdo->commit();
    }

    public function rollBack()
    {
        $this->db->pdo->rollBack();
    }
}