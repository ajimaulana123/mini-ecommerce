<?php

namespace App\Repositories;

use App\Helpers\Functions;

class UserRepository
{
    private $db;
    protected $table = 'users';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function all()
    {
        return $this->db->select($this->table, '*');
    }

    public function where(string $field, $value, $columns = '*')
    {
        return $this->db->get($this->table, $columns, [
            $field => $value
        ]);
    }

    public function insert(array $data)
    {
        $data['id'] = Functions::generateRandomId(12);

        return $this->db->insert($this->table, $data);
    }
}
