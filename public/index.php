<?php

require_once __DIR__ . '/../bootstrapt/app.php';

$app->run();


// require __DIR__ . '/../vendor/autoload.php';

// // Slim
// use Slim\App;
// use Psr\Http\Message\ServerRequestInterface as Request;
// use Psr\Http\Message\ResponseInterface as Response;

// // Database
// use Mini\Ecommerce\Config\Database;

// // Monolog
// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;

// /**
//  * Slim config
//  */
// $config = [
//     'settings' => [
//         'displayErrorDetails' => true
//     ]
// ];

// $app = new App($config);

// /**
//  * Medoo (shared in container)
//  */
// $container = $app->getContainer();

// $container['db'] = function () {
//     return Database::getConnection();
// };

// $container['logger'] = function($c) {
//     $logger = new Logger('my_logger');
//     $file_handler = new StreamHandler('../logs/app.log');
//     $logger->pushHandler($file_handler);
//     $this->logger->addInfo('Something interesting happened');
  
//     return $logger;
// };
// $app->get('/', function (Request $request, Response $response) {
//     return $response->write("Slim 3 + Medoo OK");
// });

// $app->get('/api/products', function (Request $request, Response $response) {
//     $db = $this->db;

//     $data = $db->select("account", [
//         "id",
//         "name"
//     ]);

//     return $response->withJson($data);
// });

// $app->run();
