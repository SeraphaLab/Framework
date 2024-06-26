<?php
namespace Serapha\Middleware;

use Serapha\Routing\Request;
use Serapha\Routing\Response;

class GlobalMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): Response
    {
        // Global processing
        return $next($request, $response);
    }
}
