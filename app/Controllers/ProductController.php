<?php

namespace App\Controllers;

class ProductController extends Controller
{
    private $productRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->productRepo  = $container->productRepo;
    }

    public function getProducts($request, $response)
    {
        // Ambil semua produk
        $products = $this->productRepo->all();

        // Kirim data ke view
        return $this->view->render($response, 'product.twig', [
            'products' => $products
        ]);
    }

    public function getProductDetail($request, $response, $args)
    {
        $id = $args['id']; // ambil ID dari URL
        $product = $this->productRepo->where('id', $id);

        if (!$product) {
            // bisa redirect ke halaman 404 atau kembali ke daftar produk
            return $response->withStatus(404)->write('Produk tidak ditemukan');
        }

        return $this->view->render($response, 'product_detail.twig', [
            'product' => $product
        ]);
    }
}
