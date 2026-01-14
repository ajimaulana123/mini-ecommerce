<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

# ===============================
# ROUTE PRODUK (Guest & Login)
# ===============================

// Bisa diakses semua orang
$app->get('/', 'ProductController:getProducts')->setName('product');
$app->get('/product/{id}', 'ProductController:getProductDetail')->setName('product.detail');

# ===============================
# ROUTE AUTH UNTUK GUEST
# ===============================
$app->group('', function () {
    $this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
    $this->post('/auth/signin', 'AuthController:postSignIn');
})->add(new GuestMiddleware($container));

# ===============================
# ROUTE AUTH & CART UNTUK USER LOGIN
# ===============================
$app->group('', function () {
    $this->post('/order/{id}', 'OrderController:getOrder')->setName('order');
    $this->post('/payment/mock/{productId}/pay', 'PaymentController:mockPayment')->setName('payment.mock');
    $this->get('/dashboard', 'DashboardController:index')->setName('dashboard');
    $this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');
})->add(new AuthMiddleware($container));
