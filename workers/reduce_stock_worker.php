<?php
require __DIR__ . '/../bootstrapt/app.php'; // ini koneksi DB + Medoo

$db = $container->get('db');
$orderRepo = $container->get('orderRepo');

// Ambil semua job pending
$jobs = $db->select('jobs', '*', [
    'type' => 'reduce_stock',
    'status' => 'pending'
]);

foreach ($jobs as $job) {
    $orderId = $job['order_id'];

    $order = $orderRepo->getOrderById($orderId);
    if (!$order) continue;

    // Kurangi stok
    $db->update('products', [
        'stock[-]' => $order['qty']
    ], [
        'id' => $order['product_id']
    ]);

    // Tandai job selesai
    $db->update('jobs', [
        'status' => 'done'
    ], [
        'id' => $job['id']
    ]);
}
