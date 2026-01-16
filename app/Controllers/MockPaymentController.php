<?php

namespace App\Controllers;

class MockPaymentController extends Controller
{
    private $orderRepo;
    private $paymentRepo;
    private $productRepo;
    private $baseRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->orderRepo = $container->orderRepo;
        $this->paymentRepo = $container->paymentRepo;
        $this->productRepo = $container->productRepo;
        $this->baseRepo = $container->baseRepo;
    }

    /**
     * INITIATE PAYMENT - MOCK Midtrans Snap API
     * Endpoint: POST /api/v1/mock-payments
     */
    public function createPayment($request, $response, $args)
    {
        $postData = $request->getParsedBody();

        // Validasi required fields
        $required = ['order_id', 'gross_amount', 'customer_details'];
        foreach ($required as $field) {
            if (empty($postData[$field])) {
                return $response->withJson([
                    'status_code' => '400',
                    'status_message' => "Field $field is required",
                    'validation_messages' => ["$field can not be empty"]
                ], 400);
            }
        }

        $orderId = $postData['order_id'];
        $grossAmount = $postData['gross_amount'];
        $paymentMethod = $postData['payment_method'] ?? 'bank_transfer';
        $appUrl = $this->container->get('settings')['app_url'] ?? 'http://localhost:8888';

        // Get order untuk validasi
        $order = $this->orderRepo->getOrderById($orderId);
        if (!$order) {
            return $response->withJson([
                'status_code' => '404',
                'status_message' => 'Order not found'
            ], 404);
        }

        // Create payment di database
        $paymentData = $this->paymentRepo->createMockPayment(
            $orderId,
            $grossAmount,
            $paymentMethod,
            $appUrl
        );

        // Pastikan payment_url menggunakan route yang PUBLIC
        $paymentUrl = $appUrl . '/payment/mock-checkout/' . $paymentData['external_id'];


        $mockResponse = [
            'status_code' => '201',
            'status_message' => 'Success, Mock Payment transaction is created',
            'transaction_id' => $paymentData['external_id'],
            'order_id' => $orderId,
            'gross_amount' => number_format($grossAmount, 2, '.', ''),
            'payment_type' => $paymentMethod,
            'transaction_time' => date('Y-m-d H:i:s'),
            'transaction_status' => 'pending',
            'fraud_status' => 'accept',
            'merchant_id' => 'MOCK_MERCHANT_001',
            'payment_url' => $paymentUrl, // ← PUBLIC URL
            'redirect_url' => $appUrl . '/payment/redirect/' . $paymentData['external_id'], // ← PUBLIC URL
        ];

        return $response->withJson($mockResponse, 201);
    }

    /**
     * GET PAYMENT STATUS - MOCK Midtrans Status API
     * Endpoint: GET /api/v1/mock-payments/{external_id}/status
     */
    public function getPaymentStatus($request, $response, $args)
    {
        $externalId = $args['external_id'];

        $payment = $this->paymentRepo->getPaymentByExternalId($externalId);
        if (!$payment) {
            return $response->withJson([
                'status_code' => '404',
                'status_message' => 'Transaction not found'
            ], 404);
        }

        $order = $this->orderRepo->getOrderById($payment['order_id']);

        // Format response seperti Midtrans
        $responseData = [
            'status_code' => '200',
            'status_message' => 'Success, transaction found',
            'transaction_id' => $externalId,
            'order_id' => (string)$payment['order_id'],
            'gross_amount' => number_format($payment['amount'], 2, '.', ''),
            'payment_type' => $payment['payment_method'],
            'transaction_time' => $payment['created_at'],
            'transaction_status' => $payment['status'],
            'fraud_status' => 'accept',
            'merchant_id' => $payment['merchant_id'] ?? 'MOCK_MERCHANT_001',
            'payment_amounts' => [[
                'paid_at' => $payment['status'] === 'success' ? $payment['updated_at'] : null,
                'amount' => $payment['amount']
            ]],
            'customer_details' => [
                'customer_id' => (string)$order['user_id'],
                'email' => 'customer@mock.com' // Bisa diambil dari user table
            ]
        ];

        return $response->withJson($responseData);
    }

    /**
     * WEBHOOK/CALLBACK ENDPOINT - MOCK Midtrans Notification
     * Endpoint: POST /api/v1/mock-payments/webhook
     */
    public function webhook($request, $response, $args)
    {
        $postData = $request->getParsedBody();

        // Log webhook untuk debugging
        $this->logWebhook($postData);

        // Validasi signature (mock version)
        $serverKey = $this->container->get('settings')['mock_payment']['server_key'] ?? 'SB-Mock-server-key';

        // Generate expected signature
        $orderId = $postData['order_id'] ?? '';
        $statusCode = $postData['status_code'] ?? '';
        $grossAmount = $postData['gross_amount'] ?? '';
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $signatureKey = $request->getHeaderLine('X-Mock-Signature') ?? '';

        if (!hash_equals($expectedSignature, $signatureKey)) {
            $this->logWebhook(['error' => 'Invalid signature', 'data' => $postData]);
            return $response->withJson([
                'status' => 'error',
                'message' => 'Invalid signature'
            ], 401);
        }

        // Process webhook
        $externalId = $postData['transaction_id'] ?? null;
        $transactionStatus = $postData['transaction_status'] ?? null;
        $postData['fraud_status'] ?? 'accept';

        if (!$externalId || !$transactionStatus) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Missing required fields'
            ], 400);
        }

        // Get payment
        $payment = $this->paymentRepo->getPaymentByExternalId($externalId);
        if (!$payment) {
            return $response->withJson([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }

        // Jika status sudah sama, tidak perlu update
        if ($payment['status'] === $transactionStatus) {
            return $response->withJson([
                'status' => 'success',
                'message' => 'No update needed'
            ]);
        }

        // Start transaction
        $this->baseRepo->beginTransaction();

        try {
            // Update payment status
            $this->paymentRepo->updateStatusByExternalId($externalId, $transactionStatus);

            // Update order status
            $this->orderRepo->updateStatus(
                $payment['order_id'],
                $transactionStatus,
                $payment['payment_method']
            );

            // Reduce stock if payment success
            if ($transactionStatus === 'success') {
                $order = $this->orderRepo->getOrderById($payment['order_id']);
                $this->productRepo->reduceStock($order['product_id'], $order['qty']);
            }

            $this->baseRepo->commit();

            // Log success
            $this->logWebhook([
                'status' => 'success',
                'payment_id' => $payment['id'],
                'order_id' => $payment['order_id'],
                'old_status' => $payment['status'],
                'new_status' => $transactionStatus
            ]);

            // Return success response (Midtrans expects empty response with 200)
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $this->baseRepo->rollBack();

            $this->logWebhook([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $response->withJson([
                'status' => 'error',
                'message' => 'Failed to process webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * MOCK CHECKOUT PAGE (UI untuk simulasi pembayaran)
     * Endpoint: GET /payment/mock-checkout/{external_id}
     */
    public function mockCheckout($request, $response, $args)
    {
        $externalId = $args['external_id'];

        // DEBUG OUTPUT - Tampilkan di browser
        $debug = "<h3>Debug Information:</h3>";
        $debug .= "<p>External ID: " . htmlspecialchars($externalId) . "</p>";

        $payment = $this->paymentRepo->getPaymentByExternalId($externalId);

        if ($payment) {
            $debug .= "<p>Payment Found: YES</p>";
            $debug .= "<pre>" . htmlspecialchars(print_r($payment, true)) . "</pre>";

            $order = $this->orderRepo->getOrderById($payment['order_id']);
            if ($order) {
                $debug .= "<p>Order Found: YES</p>";
                $debug .= "<pre>" . htmlspecialchars(print_r($order, true)) . "</pre>";

                // Jika semua OK, tampilkan halaman checkout
                $product = $this->productRepo->where('id', $order['product_id']);

                return $this->view->render($response, 'payment/mock_checkout.twig', [
                    'payment' => $payment,
                    'order' => $order,
                    'product' => $product,
                    'app_url' => $this->container->get('settings')['app_url']
                ]);
            } else {
                $debug .= "<p>Order Found: NO for order_id: " . $payment['order_id'] . "</p>";
            }
        } else {
            $debug .= "<p>Payment Found: NO</p>";

            // Cek semua payments di database
            $allPayments = $this->paymentRepo->db->select('payments', '*');
            $debug .= "<p>All Payments in DB:</p>";
            $debug .= "<pre>" . htmlspecialchars(print_r($allPayments, true)) . "</pre>";
        }

        // Jika ada masalah, tampilkan debug info
        return $response->write($debug);
    }

    /**
     * PROCESS MOCK PAYMENT (Dari UI checkout)
     * Endpoint: POST /payment/mock-process/{external_id}
     */
    public function processMockPayment($request, $response, $args)
    {
        $externalId = $args['external_id'];
        $postData = $request->getParsedBody();

        // Status dari UI (success/failed)
        $transactionStatus = $postData['status'] ?? 'failed';
        $paymentMethod = $postData['payment_method'] ?? 'bank_transfer';

        $payment = $this->paymentRepo->getPaymentByExternalId($externalId);
        if (!$payment) {
            return $response->withJson([
                'status_code' => '404',
                'status_message' => 'Transaction not found'
            ], 404);
        }

        $this->baseRepo->beginTransaction();

        try {
            // Update payment
            $this->paymentRepo->updateStatusByExternalId($externalId, $transactionStatus);

            // Update order
            $this->orderRepo->updateStatus($payment['order_id'], $transactionStatus, $paymentMethod);

            // Update stock jika success
            if ($transactionStatus === 'success') {
                $order = $this->orderRepo->getOrderById($payment['order_id']);
                $this->productRepo->reduceStock($order['product_id'], $order['qty']);
            }

            $this->baseRepo->commit();

            // Trigger webhook (simulasi payment gateway mengirim notifikasi)
            $this->triggerMockWebhook($externalId, $transactionStatus);

            // Redirect ke order detail
            return $response->withJson([
                'status_code' => '200',
                'status_message' => 'Payment processed successfully',
                'redirect_url' => '/orders/' . $payment['order_id']
            ]);
        } catch (\Exception $e) {
            $this->baseRepo->rollBack();
            return $response->withJson([
                'status_code' => '500',
                'status_message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * TRIGGER MOCK WEBHOOK (Simulasi payment gateway mengirim notifikasi)
     */
    private function triggerMockWebhook($externalId, $status)
    {
        $payment = $this->paymentRepo->getPaymentByExternalId($externalId);
        if (!$payment) {
            return false;
        }

        $order = $this->orderRepo->getOrderById($payment['order_id']);

        // Data yang dikirim ke webhook (format Midtrans-like)
        $webhookData = [
            'transaction_id' => $externalId,
            'order_id' => (string)$order['id'],
            'gross_amount' => (string)$payment['amount'],
            'payment_type' => $payment['payment_method'],
            'transaction_time' => $payment['updated_at'] ?? date('Y-m-d H:i:s'),
            'transaction_status' => $status,
            'fraud_status' => 'accept',
            'status_code' => $status === 'success' ? '200' : ($status === 'pending' ? '201' : '400'),
            'status_message' => $status === 'success' ? 'Success, transaction is successful' : ($status === 'pending' ? 'Success, transaction is pending' : 'Transaction failed'),
            'merchant_id' => $payment['merchant_id'] ?? 'MOCK_MERCHANT_001',
            'signature_key' => $this->generateMockSignature($externalId, $status, $payment['amount'])
        ];

        // Kirim webhook ke callback_url jika ada
        if (!empty($payment['callback_url'])) {
            $this->sendWebhook($payment['callback_url'], $webhookData);
        }

        // Juga kirim ke webhook default
        $defaultWebhookUrl = $this->container->get('settings')['app_url'] . '/api/v1/mock-payments/webhook';
        $this->sendWebhook($defaultWebhookUrl, $webhookData);

        return true;
    }

    private function sendWebhook($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Mock-Signature: ' . $data['signature_key']
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        // Log pengiriman webhook
        $this->logWebhook([
            'webhook_sent' => true,
            'url' => $url,
            'data' => $data,
            'response' => $result
        ]);

        return $result;
    }

    private function generateMockSignature($externalId, $status, $amount)
    {
        $serverKey = $this->container->get('settings')['mock_payment']['server_key'] ?? 'SB-Mock-server-key';
        return hash('sha512', $externalId . $status . $amount . $serverKey);
    }

    private function logWebhook($data)
    {
        $logDir = __DIR__ . '/../../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'webhook_' . date('Y-m-d') . '.log';
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];

        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    }
}
