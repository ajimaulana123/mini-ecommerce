<?php

namespace App\Auth;

class Auth
{
    private $userRepo;

    public function __construct($container)
    {
        $this->userRepo  = $container->userRepo;
    }

    public function user() 
    {
        $userId = $_SESSION['user'] ?? '';

        return $this->userRepo->where('id', $userId);
    }

    public function check() 
    {
        return isset($_SESSION['user']);
    }

    public function attempt($email, $password)
    {
        $user = $this->userRepo->where('email', $email);

        if (!$user) {
            return false;
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['id']; // karena $user itu array, bukan object
            return true;
        }

        return false;
    }

    public function logout()
    {
        unset($_SESSION['user']);
    }
}
