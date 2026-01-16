<?php

namespace App\Repositories;

class ProductRepository
{
    private $db;
    protected $table = 'products';

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

    public function reduceStock(string $productId, int $qty)
    {
        // get stock
        $product = $this->where('id', $productId);
        if (!$product) {
            return false;
        }

        $currentStock = $product['qty'];

        // validation stock
        if ($qty > $currentStock) {
            return false;
        }

        //  reduce stock on db
        return $this->db->update($this->table, [
            'qty' => $currentStock - $qty
        ], [
            'id' => $productId
        ]);
    }
}