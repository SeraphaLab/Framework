<?php
namespace Serapha\Routing;

class Request
{
    private array $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function getParam(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    // More attributes and methods can be extended, such as headers, body, etc.
}
