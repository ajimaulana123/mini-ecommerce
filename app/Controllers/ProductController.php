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
        $products = $this->productRepo->all();

        return $this->view->render($response, 'product.twig', [
            'products' => $products
        ]);
    }

    public function getProductDetail($request, $response, $args)
    {
        $id = $args['id'];
        $product = $this->productRepo->where('id', $id);

        if (!$product) {
            return $response->withStatus(404)->write('Produk tidak ditemukan');
        }

        return $this->view->render($response, 'product_detail.twig', [
            'product' => $product
        ]);
    }
}
