<?php

namespace App\Controllers;

class OrderController extends Controller
{
    private $productRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->productRepo = $container->productRepo;
    }

    // Menampilkan halaman order / ringkasan
    public function getOrder($request, $response, $args)
    {
        $productId = $args['id'];
        $product = $this->productRepo->where('id', $productId);

        if (!$product) {
            return $response->withStatus(404)->write('Produk tidak ditemukan');
        }

        // Ambil quantity dari form POST di product detail
        $postData = $request->getParsedBody();
        $qty = isset($postData['quantity']) ? (int)$postData['quantity'] : 1;

        // Validasi stok
        if ($qty > $product['qty']) {
            $qty = $product['qty'];
        }

        $totalPrice = $product['price'] * $qty;

        return $this->view->render($response, 'order.twig', [
            'product' => $product,
            'quantity' => $qty,
            'total_price' => $totalPrice
        ]);
    }
}