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

    // ORIGINAL METHOD (update dengan payment_url)
    public function createPayment($orderId, $amount, $paymentMethod)
    {
        $paymentId = 'PAY-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $externalId = 'MOCK-' . time() . '-' . rand(1000, 9999);
        $paymentUrl = '/payment/mock-checkout/' . $externalId;

        // Untuk callback/webhook (sesuaikan dengan base URL aplikasi Anda)
        $callbackUrl = 'http://localhost:8888/api/v1/mock-payments/webhook';

        $this->db->insert($this->table, [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'external_id' => $externalId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_url' => $paymentUrl,
            'callback_url' => $callbackUrl,
            'status' => 'pending',
            'expiry_time' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'merchant_id' => 'MOCK_MERCHANT_001'
        ]);

        // Return array dengan semua data yang diperlukan
        return [
            'payment_id' => $paymentId,
            'external_id' => $externalId,
            'payment_url' => $paymentUrl
        ];
    }

    // ALTERNATIVE: Method khusus untuk Mock API (lebih baik)
    public function createMockPayment($orderId, $amount, $paymentMethod, $appUrl = 'http://localhost:8888')
    {
        $paymentId = 'PAY-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $externalId = 'MOCK-' . time() . '-' . rand(1000, 9999);

        // Payment URL untuk user redirect
        $paymentUrl = '/payment/mock-checkout/' . $externalId;

        // Webhook URL untuk payment gateway callback
        $callbackUrl = $appUrl . '/api/v1/mock-payments/webhook';

        $this->db->insert($this->table, [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'external_id' => $externalId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_url' => $paymentUrl,
            'callback_url' => $callbackUrl,
            'status' => 'pending',
            'expiry_time' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'merchant_id' => 'MOCK_MERCHANT_001',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'payment_id' => $paymentId,
            'external_id' => $externalId,
            'payment_url' => $appUrl . $paymentUrl, // Full URL untuk response API
            'callback_url' => $callbackUrl
        ];
    }

    // Get payment by external_id (untuk webhook dan status check)     
    public function getPaymentByExternalId($externalId)
    {
        ("QUERY: SELECT * FROM payments WHERE external_id = '" . $externalId . "'");
        return $this->db->get($this->table, '*', [
            'external_id[~]' => $externalId
        ]); 
        ("QUERY RESULT: " . print_r($result, true));
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

    public function updateStatusByExternalId($externalId, $status)
    {
        return $this->db->update($this->table, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], [
            'external_id' => $externalId
        ]);
    }

    public function getPaymentById($paymentId)
    {
        return $this->db->get($this->table, '*', ['payment_id' => $paymentId]);
    }

    public function getPaymentByOrderId($orderId)
    {
        return $this->db->get($this->table, '*', ['order_id' => $orderId]);
    }
}