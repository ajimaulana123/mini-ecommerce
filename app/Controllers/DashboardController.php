<?php

namespace App\Controllers;

class DashboardController extends Controller
{
    private $orderRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->orderRepo = $container->orderRepo;
    }

    public function index($request, $response)
    {
        $userId = $_SESSION['user']; // asumsikan session menyimpan info user

        $orders = $this->orderRepo->getOrdersByUser($userId);
        $totalOrders = $this->orderRepo->countOrders($userId);
        $totalPaid = $this->orderRepo->countOrdersByStatus($userId, 'success');
        $totalUnpaid = $this->orderRepo->countOrdersByStatus($userId, 'failed');

        return $this->view->render($response, 'dashboard.twig', [
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'totalPaid' => $totalPaid,
            'totalUnpaid' => $totalUnpaid
        ]);
    }
}