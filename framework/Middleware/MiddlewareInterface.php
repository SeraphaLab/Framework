<?php
namespace Serapha\Middleware;

use Serapha\Routing\Request;
use Serapha\Routing\Response;

interface MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, Response $response, callable $next): Response;
}
