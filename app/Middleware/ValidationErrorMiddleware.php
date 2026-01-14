<?php

namespace App\Middleware;

class ValidationErrorMiddleware extends Middleware
{
   public function __invoke($request, $response, $next)
    {
        $errors = $_SESSION['errors'] ?? [];

        $this->container->view->getEnvironment()->addGlobal('errors', $errors);

        if (isset($_SESSION['errors'])) {
            unset($_SESSION['errors']);
        }

        $response = $next($request, $response);
        return $response;
    }
}