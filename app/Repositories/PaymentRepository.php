<?php

namespace App\Repositories;

class PaymentRepository
{
    private $db;
    protected $table = 'payments';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createPayment($orderId, $amount, $paymentMethod)
    {
        $paymentId = 'PAY-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $this->db->insert($this->table, [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => 'pending'
        ]);

        return $paymentId;
    }

    public function updateStatus($paymentId, $status)
    {
        return $this->db->update($this->table, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'payment_id' => $paymentId
        ]);
    }

    public function getPaymentById($paymentId)
    {
        return $this->db->get($this->table, '*', ['payment_id' => $paymentId]);
    }
}
