<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Old module
require __DIR__.'/../vendor/adbario/slim-secure-session-middleware/src/SecureSessionHandler.php';

use Adbar\SecureSessionHandler;

$handler = new SecureSessionHandler('my_super_secret_key_123');
session_set_save_handler($handler, true);
session_start();

use Medoo\Medoo;

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

$container = $app->getContainer();

$container['db'] = function () {
    return new \Medoo\Medoo([
        'database_type' => 'mysql',
        'database_name' => 'ecommerce',
        'server' => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ]);
};

$container['auth'] = function ($container) {
    return new \App\Auth\Auth($container);
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user()
    ]);

    $view->getEnvironment()->addGlobal('flash', $container->flash);
    
    return $view; // jangan lupa return object Twig
};

// Repositories
$container['userRepo'] = function ($container) {
    return new \App\Repositories\UserRepository($container->db);
};
$container['productRepo'] = function ($container) {
    return new \App\Repositories\ProductRepository($container->db);
};
$container['orderRepo'] = function ($container) {
    return new \App\Repositories\OrderRepository($container->db);
};
$container['paymentRepo'] = function ($container) {
    return new \App\Repositories\PaymentRepository($container->db);
};
$container['baseRepo'] = function ($container) {
    return new \App\Repositories\BaseRepository($container->db);
};


$container['validator'] = function ($container) {
    return new \App\Validation\Validator($container->userRepo);
};

// Controllers
$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};
$container['AuthController'] = function ($container) {
    return new \App\Controllers\Auth\AuthController($container);
};
$container['ProductController'] = function ($container) {
    return new \App\Controllers\ProductController($container);
};
$container['OrderController'] = function ($container) {
    return new \App\Controllers\OrderController($container);
};
$container['PaymentController'] = function ($container) {
    return new \App\Controllers\PaymentController($container);
};
$container['DashboardController'] = function ($container) {
    return new \App\Controllers\DashboardController($container);
};

// Middleware
$app->add(new \App\Middleware\ValidationErrorMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));

require_once __DIR__ . '/../app/routes.php';