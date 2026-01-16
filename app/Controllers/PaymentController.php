<?php

namespace App\Controllers;

class PaymentController extends Controller
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
     * Instructions page - TAMPILKAN pilihan payment method
     */
    public function instructions($request, $response, $args)
    {
        $orderId = $args['order_id'];

        // Verify order belongs to user
        $order = $this->orderRepo->getOrderById($orderId);
        if (!$order || $order['user_id'] != $_SESSION['user']) {
            $this->flash->addMessage('error', 'Order not found');
            return $response->withRedirect('/orders');
        }

        // Jika order sudah paid, redirect ke detail
        if ($order['status'] === 'success') {
            return $response->withRedirect('/orders/' . $orderId);
        }

        $product = $this->productRepo->where('id', $order['product_id']);

        return $this->view->render($response, 'payment_instructions.twig', [
            'order' => $order,
            'product' => $product,
            'payment_methods' => [
                'mock_bank_transfer' => 'Bank Transfer (Mock)',
                'mock_credit_card' => 'Credit Card (Mock)',
                'mock_gopay' => 'GoPay (Mock)',
                'cod' => 'Cash on Delivery'
            ]
        ]);
    }

    public function processPayment($request, $response, $args)
    {
        $orderId = $args['order_id'];
        $postData = $request->getParsedBody();
        $paymentMethod = $postData['payment_method'] ?? 'mock_bank_transfer';

        // Verify order belongs to current user
        $order = $this->orderRepo->getOrderById($orderId);
        if (!$order) {
            $this->flash->addMessage('error', 'Order not found');
            return $response->withRedirect('/orders');
        }

        // Check user authorization
        $currentUserId = $_SESSION['user'];

        if ($order['user_id'] != $currentUserId) {
            $this->flash->addMessage('error', 'You are not authorized to access this order');
            return $response->withRedirect('/orders');
        }

        // Jika order sudah paid
        if ($order['status'] === 'success' || $order['status'] === 'paid') {
            $this->flash->addMessage('info', 'This order has already been paid');
            return $response->withRedirect('/orders/' . $orderId);
        }

        // 1. HANDLE CASH ON DELIVERY (COD)
        if ($paymentMethod === 'cod') {
            $this->baseRepo->beginTransaction();
            try {
                // Generate payment record
                $paymentData = $this->paymentRepo->createMockPayment(
                    $orderId,
                    $order['total_price'],
                    'cod',
                    $this->container->get('settings')['app_url'] ?? 'http://localhost:8888'
                );

                // Update payment status
                $this->paymentRepo->updateStatusByExternalId($paymentData['external_id'], 'success');

                // Update order status DAN payment_method sekaligus
                $this->orderRepo->updateStatus($orderId, 'success', 'cod');

                // Reduce product stock
                $this->productRepo->reduceStock($order['product_id'], $order['qty']);

                $this->baseRepo->commit();

                $this->flash->addMessage('success', 'COD order created successfully! Please prepare cash when your order arrives.');
                return $response->withRedirect('/orders/' . $orderId);
            } catch (\Exception $e) {
                $this->baseRepo->rollBack();
                $this->flash->addMessage('error', 'Failed to process COD payment: ' . $e->getMessage());
                ('COD Payment Error: ' . $e->getMessage());
                return $response->withRedirect('/payment/instructions/' . $orderId);
            }
        }

        // 2. HANDLE MOCK PAYMENT
        try {
            // Tentukan metode untuk API
            $methodForApi = str_replace('mock_', '', $paymentMethod);

            // Create mock payment
            $paymentData = $this->paymentRepo->createMockPayment(
                $orderId,
                $order['total_price'],
                $methodForApi,
                $this->container->get('settings')['app_url'] ?? 'http://localhost:8888'
            );

            // Update order status DAN payment_method sekaligus
            $this->orderRepo->updateStatus($orderId, 'pending', $paymentMethod);

            // Redirect ke mock checkout page
            return $response->withRedirect($paymentData['payment_url']);
        } catch (\Exception $e) {
            $this->flash->addMessage('error', 'Failed to process payment: ' . $e->getMessage());
            ('Payment Processing Error: ' . $e->getMessage());
            return $response->withRedirect('/payment/instructions/' . $orderId);
        }
    }
}