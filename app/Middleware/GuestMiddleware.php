<?php
namespace App\Middleware;

class GuestMiddleware extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        // Jika SUDAH login, jangan boleh akses halaman guest (signin/register)
        if ($this->container->auth->check()) {
            return $response->withRedirect(
                $this->container->router->pathFor('product')
            );
        }

        return $next($request, $response);
    }
}
