<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\BaseRepository;
use App\Contracts\LoggerInterface;

class OrderService
{
    private $orderRepo;
    private $productRepo;
    private $baseRepo;
    private $logger;
    
    public function __construct(
        OrderRepository $orderRepo,
        ProductRepository $productRepo,
        BaseRepository $baseRepo,
        LoggerInterface $logger
    ) {
        $this->orderRepo = $orderRepo;
        $this->productRepo = $productRepo;
        $this->baseRepo = $baseRepo;
        $this->logger = $logger;
    }
    
    public function calculateOrderSummary(string $productId, int $quantity): array
    {
        $this->logger->debug("Calculating order summary", [
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
        
        $product = $this->productRepo->where('id', $productId);
        
        if (!$product) {
            $this->logger->error("Product not found", ['product_id' => $productId]);
            throw new \Exception('Product tidak ditemukan');
        }
        
        $adjustedQuantity = min($quantity, $product['qty']);
        $totalPrice = $product['price'] * $adjustedQuantity;
        
        if ($quantity > $product['qty']) {
            $this->logger->warning("Stock limited", [
                'product_id' => $productId,
                'requested' => $quantity,
                'available' => $product['qty']
            ]);
        }
        
        return [
            'product' => $product,
            'quantity' => $adjustedQuantity,
            'total_price' => $totalPrice,
            'is_stock_limited' => $quantity > $product['qty']
        ];
    }
    
    public function createOrder(string $productId, int $quantity, int $userId): int
    {
        $this->logger->order("Starting order creation", [
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
        
        try {
            $summary = $this->calculateOrderSummary($productId, $quantity);
            
            $this->baseRepo->beginTransaction();
            
            $orderId = $this->orderRepo->insertOrder(
                $productId,
                $summary['quantity'],
                $summary['total_price'],
                $summary['product']['name'],
                $userId
            );
            
            $this->baseRepo->commit();
            
            $this->logger->order("Order created successfully", [
                'order_id' => $orderId,
                'user_id' => $userId,
                'total_price' => $summary['total_price']
            ]);
            
            return $orderId;
            
        } catch (\Exception $e) {
            $this->logger->error("Order creation failed", [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            
            $this->baseRepo->rollBack();
            throw $e;
        }
    }
    
    public function getOrderForUser(int $orderId, int $userId): array
    {
        $this->logger->debug("Getting order {$orderId} for user {$userId}");
        
        $order = $this->orderRepo->getOrderById($orderId);
        
        if (!$order) {
            $this->logger->warning("Order not found: {$orderId}");
            throw new \Exception('Order tidak ditemukan');
        }
        
        if ($order['user_id'] != $userId) {
            $this->logger->warning("Unauthorized access to order {$orderId} by user {$userId}");
            throw new \Exception('Akses ditolak');
        }
        
        $product = $this->productRepo->where('id', $order['product_id']);
        
        $this->logger->debug("Order retrieved successfully: {$orderId}");
        
        return [
            'order' => $order,
            'product' => $product
        ];
    }
    
    public function getUserOrders(int $userId): array
    {
        $this->logger->debug("Getting orders for user {$userId}");
        
        $orders = $this->orderRepo->getOrdersByUser($userId);
        
        $this->logger->debug("Retrieved " . count($orders) . " orders for user {$userId}");
        
        return $orders;
    }
}