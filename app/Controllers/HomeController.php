<?php

namespace App\Controllers;

use \App\Repositories\UserRepository;

class HomeController extends Controller
{
    private $userRepo;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->userRepo = new UserRepository($container->db);
    }

    public function index($request, $response)
    {
        return $this->view->render($response, 'home.twig');
    }
}