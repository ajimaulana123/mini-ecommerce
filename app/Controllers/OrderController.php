<?php

namespace App\Controllers;

class OrderController extends Controller
{
    private $productRepo;
    private $orderRepo;
    private $baseRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->productRepo = $container->productRepo;
        $this->orderRepo = $container->orderRepo;
        $this->baseRepo = $container->baseRepo;
    }

    // 1. TAMPILKAN ORDER SUMMARY (GET request)
    // OrderController.php
    public function showOrderSummary($request, $response, $args)
    {
        $productId = $args['productId'];
        $product = $this->productRepo->where('id', $productId);

        if (!$product) {
            $this->flash->addMessage('error', 'Produk tidak ditemukan');
            return $response->withRedirect('/');
        }

        // AMBIL DARI QUERY PARAM (GET) BUKAN POST BODY
        $quantity = $request->getQueryParam('quantity', 1);

        // Validasi stok
        if ($quantity > $product['qty']) {
            $quantity = $product['qty'];
            $this->flash->addMessage('warning', 'Stok terbatas, quantity disesuaikan');
        }

        $totalPrice = $product['price'] * $quantity;

        return $this->view->render($response, 'order.twig', [
            'product' => $product,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'create_order_url' => $this->router->pathFor('order.create', ['productId' => $productId])
        ]);
    }

    public function createOrder($request, $response, $args)
    {
        $productId = $args['productId'];

        try {
            $postData = $request->getParsedBody();

            $quantity = isset($postData['quantity']) ? (int)$postData['quantity'] : 1;
            $product = $this->productRepo->where('id', $productId);

            if (!$product) {
                $this->flash->addMessage('error', 'Product not found');
                return $response->withRedirect('/');
            }

            if ($quantity > $product['qty']) {
                $quantity = $product['qty'];
            }

            $totalPrice = $product['price'] * $quantity;
            ("Creating order: product={$product['name']}, qty={$quantity}, price={$totalPrice}");

            // Buat order dulu
            $this->baseRepo->beginTransaction();
            ("Transaction started");

            // 1. Insert order dengan status 'pending'
            $orderId = $this->orderRepo->insertOrder(
                $productId,
                $quantity,
                $totalPrice,
                $product['name'],
                $_SESSION['user']
            );

            ("Order created with ID: " . $orderId);

            $this->baseRepo->commit();
            ("Transaction committed");

            // 2. Redirect ke payment instructions page
            $redirectUrl = '/payment/instructions/' . $orderId;
            ("Redirecting to: " . $redirectUrl);

            return $response->withRedirect($redirectUrl);
        } catch (\Exception $e) {
            ("EXCEPTION: " . $e->getMessage());
            ("TRACE: " . $e->getTraceAsString());

            if ($this->baseRepo->db->inTransaction()) {
                $this->baseRepo->rollBack();
                ("Transaction rolled back");
            }

            $this->flash->addMessage('error', 'Failed to create order: ' . $e->getMessage());
            ("Redirecting to /orders due to error");
            return $response->withRedirect('/orders');
        } finally {
            ("=== DEBUG createOrder END ===");
        }
    }

    // 3. TAMPILKAN ORDER DETAILS (existing)
    public function showOrderDetails($request, $response, $args)
    {
        $orderId = $args['id'];
        $userId = $_SESSION['user'];

        $order = $this->orderRepo->getOrderById($orderId);

        // Security check
        if (!$order || $order['user_id'] != $userId) {
            $this->flash->addMessage('error', 'Order tidak ditemukan');
            return $response->withRedirect('/orders');
        }

        $product = $this->productRepo->where('id', $order['product_id']);

        // Get payment info jika ada
        $payment = $this->container->paymentRepo->getPaymentByOrderId($orderId);

        return $this->view->render($response, 'order_detail.twig', [
            'order' => $order,
            'product' => $product,
            'payment' => $payment
        ]);
    }

    // 4. LIST ORDERS (existing)
    public function index($request, $response, $args)
    {
        $userId = $_SESSION['user'];
        $orders = $this->orderRepo->getOrdersByUser($userId);

        return $this->view->render($response, 'order.twig', [
            'orders' => $orders
        ]);
    }
}