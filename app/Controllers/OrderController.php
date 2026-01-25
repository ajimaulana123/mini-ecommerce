<?php
namespace App\Controllers;

class OrderController extends Controller
{
    private $orderService;
    
    public function __construct($container)
    {
        parent::__construct($container);
        $this->orderService = new \App\Services\OrderService(
            $container->orderRepo,
            $container->productRepo,
            $container->baseRepo,
            $container->logger
        );
    }
    
    public function showOrderSummary($request, $response, $args)
    {
        try {
            $productId = $args['productId'];
            $quantity = (int) $request->getQueryParam('quantity', 1);
            
            // BUSINESS LOGIC DI SERVICE!
            $summary = $this->orderService->calculateOrderSummary($productId, $quantity);
            
            if ($summary['is_stock_limited']) {
                $this->flash->addMessage('warning', 'Stok terbatas, quantity disesuaikan');
            }
            
            return $this->view->render($response, 'order.twig', [
                'product' => $summary['product'],
                'quantity' => $summary['quantity'],
                'total_price' => $summary['total_price'],
                'create_order_url' => $this->router->pathFor('order.create', [
                    'productId' => $productId
                ])
            ]);
            
        } catch (\Exception $e) {
            $this->flash->addMessage('error', $e->getMessage());
            return $response->withRedirect('/');
        }
    }
    
    public function createOrder($request, $response, $args)
    {
        try {
            $productId = $args['productId'];
            $userId = (int) $_SESSION['user'];
            $quantity = (int) ($request->getParsedBody()['quantity'] ?? 1);
            
            // SEMUA BUSINESS LOGIC DI SERVICE!
            $orderId = $this->orderService->createOrder($productId, $quantity, $userId);
            
            $this->flash->addMessage('success', 'Order berhasil dibuat');
            return $response->withRedirect('/payment/instructions/' . $orderId);
            
        } catch (\Exception $e) {
            $this->flash->addMessage('error', 'Gagal membuat order: ' . $e->getMessage());
            return $response->withRedirect(
                $this->router->pathFor('order.summary', ['productId' => $productId])
            );
        }
    }
    
    public function showOrderDetails($request, $response, $args)
    {
        try {
            $orderId = (int) $args['id'];
            $userId = (int) $_SESSION['user'];
            
            // BUSINESS LOGIC DI SERVICE!
            $data = $this->orderService->getOrderForUser($orderId, $userId);
            
            // Payment tetap pakai repo langsung (tidak perlu service)
            $payment = $this->container->paymentRepo->getPaymentByOrderId($orderId);
            
            return $this->view->render($response, 'order_detail.twig', [
                'order' => $data['order'],
                'product' => $data['product'],
                'payment' => $payment
            ]);
            
        } catch (\Exception $e) {
            $this->flash->addMessage('error', $e->getMessage());
            return $response->withRedirect('/orders');
        }
    }
    
    public function index($request, $response, $args)
    {
        try {
            $userId = (int) $_SESSION['user'];
            
            // BUSINESS LOGIC DI SERVICE!
            $orders = $this->orderService->getUserOrders($userId);
            
            return $this->view->render($response, 'order.twig', [
                'orders' => $orders
            ]);
            
        } catch (\Exception $e) {
            $this->flash->addMessage('error', 'Terjadi kesalahan: ' . $e->getMessage());
            return $response->withRedirect('/');
        }
    }
}