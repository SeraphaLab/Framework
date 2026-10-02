<?php
declare(strict_types=1);

namespace Serapha\Routing;

class Handler
{
    /**
     * @var callable(Request, Response): Response
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function handle(Request $request, Response $response): Response
    {
        return call_user_func($this->callback, $request, $response);
    }
}
