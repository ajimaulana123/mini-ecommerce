<?php

namespace App\Controllers;

class PaymentController extends Controller
{
    private $orderRepo;
    private $paymentRepo;
    private $productRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->orderRepo = $container->orderRepo;
        $this->paymentRepo = $container->paymentRepo;
        $this->productRepo = $container->productRepo;
    }

    // POST /payment/mock/{productId}/pay
    public function mockPayment($request, $response, $args)
    {
        $productId = $args['productId'];
        $postData = $request->getParsedBody();
        $quantity = isset($postData['quantity']) ? (int)$postData['quantity'] : 1;
        $paymentMethod = $postData['payment_method'] ?? 'COD';

        // Ambil data produk
        $product = $this->productRepo->where('id', $productId);
        if (!$product) {
            return $response->withStatus(404)->write('Produk tidak ditemukan');
        }

        // Validasi stok
        if ($quantity > $product['qty']) {
            $quantity = $product['qty'];
        }

        $totalPrice = $product['price'] * $quantity;

        // --- 1. Insert order ---
        $orderId = $this->orderRepo->insertOrder(
            $productId, 
            $quantity, 
            $totalPrice, 
            $product['name'],
            $_SESSION['user']
        );
        
        // --- 2. Create mock payment ---
        $paymentId = $this->paymentRepo->createPayment($orderId, $totalPrice, $paymentMethod);
       
        // --- 3. Tentukan status payment (mock) ---
        if ($paymentMethod === 'COD') {
            $status = 'success'; // COD langsung success
        } else {
            // Random success / failed untuk VA transfer mock
            $status = rand(0,1) ? 'success' : 'failed';
        }

        // --- 4. Update status payment & order ---
        $this->paymentRepo->updateStatus($paymentId, $status);
        $this->orderRepo->updateStatus($orderId, $status, $paymentMethod);

        // --- 5. Kurangi stok jika success ---
        if ($status === 'success') {
            $this->productRepo->reduceStock($productId, $quantity);
        }

        // --- 6. Redirect / render halaman result ---
        return $this->view->render($response, 'payment_result.twig', [
            'order' => $this->orderRepo->getOrderById($orderId),
            'payment' => $this->paymentRepo->getPaymentById($paymentId),
            'product' => $product
        ]);
    }
}
