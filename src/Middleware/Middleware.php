<?php
namespace Serapha\Middleware;

use Serapha\Routing\Response;
use Serapha\Routing\Request;
use Serapha\Routing\Handler;

abstract class Middleware
{
    /**
     * Process the request and response.
     *
     * @param Request $request
     * @param Response $response
     * @param Handler $handler
     * @return Response
     */
    abstract public function process(Request $request, Response $response, Handler $handler): Response;

    /**
     * Create a new response with the given status code and body.
     *
     * @param int $statusCode
     * @param string $body
     * @return Response
     */
    protected function createResponse(int $statusCode = 200, string $body = ''): Response
    {
        $responseFactory = new Response();
        $response = $responseFactory->createResponse($statusCode);
        $response->getBody()->write($body);

        return $response;
    }

    /**
     * Create a new redirect response to the given URL.
     *
     * @param string $url
     * @param int $statusCode
     * @return Response
     */
    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return $this->createResponse($statusCode)->withHeader('Location', $url);
    }

    /**
     * Get a specific header value from the request.
     *
     * @param Request $request
     * @param string $header
     * @return string|null
     */
    protected function getHeader(Request $request, string $header): ?string
    {
        return $request->getHeaderLine($header) ?: null;
    }

    /**
     * Get a query parameter value from the request.
     *
     * @param Request $request
     * @param string $param
     * @return string|null
     */
    protected function getQueryParam(Request $request, string $param): ?string
    {
        $queryParams = $request->getQueryParams();
        return $queryParams[$param] ?? null;
    }
}
