<?php

namespace App\Middleware;

class OldInputMiddleware extends Middleware
{
   public function __invoke($request, $response, $next)
    {
        $errors = $_SESSION['old'] ?? [];

        $this->container->view->getEnvironment()->addGlobal('old', $errors);

        if (isset($_SESSION['old'])) {
            unset($_SESSION['old']);
        }

        $response = $next($request, $response);
        return $response;
    }
}