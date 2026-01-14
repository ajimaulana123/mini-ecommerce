<?php
namespace App\Middleware;

class AuthMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        // Kalau BELUM login, redirect ke signin
        if (!$this->container->auth->check()) {
            $this->container->flash->addMessage('error', 'Mohon login terlebih dahulu');

            return $response->withRedirect(
                $this->container->router->pathFor('auth.signin')
            );
        }

        return $next($request, $response);
    }
}