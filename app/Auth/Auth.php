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
         // Cek apakah ada user di session DAN tidak empty
        if (!$this->check()) {
            return null;
        }
        
        $userId = $_SESSION['user'];
        
        // Validasi userId harus numeric dan tidak empty
        if (empty($userId) || !is_numeric($userId)) {
            return null;
        }

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
            $_SESSION['user'] = $user['id'];
            return true;
        }

        return false;
    }

    public function logout()
    {
        unset($_SESSION['user']);
    }
}