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

    public function mockPayment($request, $response, $args)
    {
        $productId = $args['productId'];
        $postData = $request->getParsedBody();
        $quantity = isset($postData['quantity']) ? (int)$postData['quantity'] : 1;
        $paymentMethod = $postData['payment_method'] ?? 'COD';

        $product = $this->productRepo->where('id', $productId);
        if (!$product) {
            return $response->withStatus(404)->write('Produk tidak ditemukan');
        }

        if ($quantity > $product['qty']) {
            $quantity = $product['qty'];
        }

        $totalPrice = $product['price'] * $quantity;

        // --- MULAI TRANSACTION ---
        $this->baseRepo->beginTransaction();

        try {
            // 1. Insert order
            $orderId = $this->orderRepo->insertOrder(
                $productId,
                $quantity,
                $totalPrice,
                $product['name'],
                $_SESSION['user']
            );

            // 2. Create mock payment
            $paymentId = $this->paymentRepo->createPayment($orderId, $totalPrice, $paymentMethod);

            // 3. Tentukan status payment
            $status = ($paymentMethod === 'COD') ? 'success' : (rand(0, 1) ? 'success' : 'failed');

            // 4. Update status
            $this->paymentRepo->updateStatus($paymentId, $status);
            $this->orderRepo->updateStatus($orderId, $status, $paymentMethod);

            // 5. Reduce stock jika success
            if ($status === 'success') {
                $this->productRepo->reduceStock($productId, $quantity);
            }

            // --- COMMIT jika semua sukses ---
            $this->baseRepo->commit();
        } catch (\Exception $e) {
            $this->baseRepo->rollBack(); // rollback semua perubahan
            return $response->withStatus(500)->write("Transaksi gagal: " . $e->getMessage());
        }

        return $this->view->render($response, 'payment_result.twig', [
            'order' => $this->orderRepo->getOrderById($orderId),
            'payment' => $this->paymentRepo->getPaymentById($paymentId),
            'product' => $product
        ]);
    }
}
