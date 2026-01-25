<?php

namespace App\Controllers;

use App\Controllers\Controller;

class AuthController extends Controller
{
    private $userRepo;
    private $validator;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->userRepo  = $container->userRepo;
        $this->validator = $container->validator;
    }

    public function getSignOut($request, $response)
    {
        $this->auth->logout();

        return $response->withRedirect(
            $this->router->pathFor('product')
        );
    }

    public function getSignIn($request, $response)
    {
        return $this->view->render($response, 'auth/signin.twig');
    }

    public function postSignIn($request, $response)
    {
        $data = $request->getParams();

        $rules = [
            'email'    => 'required|email',
            'password' => 'required'
        ];

        $messages = [
            'email:required'    => 'Email wajib diisi',
            'email:email'       => 'Format email tidak valid, contoh valid example@gmail.com',
            'password:required' => 'Password wajib diisi'
        ];

        if (!$this->validator->validate($data, $rules, $messages)) {
            $_SESSION['old'] = [
                'email' => $data['email'] ?? ''
            ];

            return $response->withRedirect(
                $this->router->pathFor('auth.signin')
            );
        }

        $auth = $this->auth->attempt(
            $data['email'],
            $data['password']
        );

        if (!$auth) {
            $this->flash->addMessage('error', 'Login Gagal!, email atau password salah');

            $_SESSION['error'] = 'Email atau password salah';
            $_SESSION['old'] = [
                'email' => $data['email']
            ];

            return $response->withRedirect(
                $this->router->pathFor('auth.signin')
            );
        }

        $this->flash->addMessage('info', 'Login berhasil!');

        return $response->withRedirect(
            $this->router->pathFor('product')
        );
    }
}