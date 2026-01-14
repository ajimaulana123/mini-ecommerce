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

    //tambah method untuk order ---
    public function reduceStock(string $productId, int $qty)
    {
        // Ambil stok sekarang
        $product = $this->where('id', $productId);
        if (!$product) {
            return false;
        }

        $currentStock = $product['qty'];

        // Validasi stok cukup
        if ($qty > $currentStock) {
            return false; // stok tidak cukup
        }

        // Kurangi stok di database
        return $this->db->update($this->table, [
            'qty' => $currentStock - $qty
        ], [
            'id' => $productId
        ]);
    }
}
