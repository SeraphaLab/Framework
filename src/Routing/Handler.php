<?php
declare(strict_types=1);

namespace Serapha\Routing;

class Handler
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function handle(Request $request): Response
    {
        $result = call_user_func($this->callback, $request);

        if ($result instanceof Response) {
            return $result;
        }

        return new Response();
    }
}
