<?php

namespace App\Controllers;

class CartController extends Controller
{
    private $productRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->productRepo = $container->productRepo;
    }

    public function getCart($request, $response)
    {
        $cart = $_SESSION['cart'] ?? [];

        return $this->view->render($response, 'cart.twig', [
            'cart' => $cart
        ]);
    }

    public function addToCart($request, $response, $args)
    {
        $productId = $args['id'];
        $postData = $request->getParsedBody();
        $qty = isset($postData['quantity']) ? (int)$postData['quantity'] : 1;

        // Ambil data produk dari repository
        $product = $this->productRepo->where('id', $productId);
        if (!$product) {
            return $response->withStatus(404)->write('Produk tidak ditemukan');
        }

        // Validasi qty
        if ($qty < 1) {
            $qty = 1;
        } elseif ($qty > $product['qty']) {
            $qty = $product['qty'];
        }

        // Kurangi stok di database
        $reduce = $this->productRepo->reduceStock($productId, $qty);
        if (!$reduce) {
            return $response->withStatus(400)->write('Stok tidak cukup');
        }

        $totalPrice = $product['price'] * $qty;

        // Simpan ke session cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Jika produk sudah ada di cart, update qty dan total_price
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $qty;
            $_SESSION['cart'][$productId]['total_price'] += $totalPrice;
        } else {
            $_SESSION['cart'][$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $qty,
                'total_price' => $totalPrice,
            ];
        }

        // Redirect ke halaman cart
        return $response->withHeader('Location', '/cart')->withStatus(302);
    }
}
