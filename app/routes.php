<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

# ===============================
# 1. PUBLIC ROUTES (No Auth Needed)
# ===============================

// Home page 
$app->get('/', 'ProductController:getProducts')->setName('product');

// Product detail page (PUBLIC)
$app->get('/product/{id}', 'ProductController:getProductDetail')->setName('product.detail');

// Auth pages
$app->group('', function () {
    $this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
    $this->post('/auth/signin', 'AuthController:postSignIn');
})->add(new GuestMiddleware($container));


# ===============================
# 2. PROTECTED USER ROUTES (Need Login)
# ===============================
$app->group('', function () {
    // Order Routes
    $this->get('/order/{productId}', 'OrderController:showOrderSummary')->setName('order.summary');
    $this->post('/order/{productId}/create', 'OrderController:createOrder')->setName('order.create');
    
    // Orders Management
    $this->get('/orders', 'OrderController:index')->setName('orders');
    $this->get('/orders/{id}', 'OrderController:showOrderDetails')->setName('order.detail');
    
    // Payment Routes
    $this->get('/payment/instructions/{order_id}', 'PaymentController:instructions')->setName('payment.instructions');
    $this->post('/payment/process/{order_id}', 'PaymentController:processPayment')->setName('payment.process');
    $this->get('/payment/history', 'PaymentController:history')->setName('payment.history');
    
    // Dashboard
    $this->get('/dashboard', 'DashboardController:index')->setName('dashboard');
    
    // Logout
    $this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');
    
})->add(new AuthMiddleware($container));


# ===============================
# 3. MOCK PAYMENT API ROUTES (Public)
# ===============================
$app->group('/api/v1/mock-payments', function () {
    $this->post('', 'MockPaymentController:createPayment');
    $this->get('/{external_id}/status', 'MockPaymentController:getPaymentStatus');
    $this->post('/webhook', 'MockPaymentController:webhook');
});


# ===============================
# 4. PUBLIC PAYMENT PAGES (No Login Needed)
# ===============================
// Payment checkout page
$app->get('/payment/mock-checkout/{external_id}', 'MockPaymentController:mockCheckout');

// Process payment dari checkout page
$app->post('/payment/mock-process/{external_id}', 'MockPaymentController:processMockPayment');

// Payment success page (public)
$app->get('/payment/success/{external_id}', 'MockPaymentController:paymentSuccess');