<?php

namespace App\Repositories;

class OrderRepository
{
    private $db;
    protected $table = 'orders';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function insertOrder($productId, $qty, $totalPrice, $productName, $userId)
    {
        $this->db->insert($this->table, [
            'product_id' => $productId,
            'product_name' => $productName,
            'user_id' => $userId,
            'qty' => $qty,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->id();
    }

    public function updateStatus($orderId, $status, $paymentMethod)
    {
        return $this->db->update($this->table, [
            'status' => $status,
            'payment_method' => $paymentMethod,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'id' => $orderId
        ]);
    }

    public function getOrderById($orderId)
    {
        return $this->db->get($this->table, '*', ['id' => $orderId]);
    }

    public function getOrdersByUser($userId)
    {
        return $this->db->select($this->table, '*', [
            'user_id' => $userId,
            'ORDER' => ['created_at' => 'DESC']
        ]);
    }

    public function countOrders($userId)
    {
        return $this->db->count($this->table, '*', ['user_id' => $userId]);
    }

    // Hitung total order paid / unpaid
    public function countOrdersByStatus($userId, $status)
    {
        return $this->db->count($this->table, '*', [
            'user_id' => $userId,
            'status' => $status
        ]);
    }
}
