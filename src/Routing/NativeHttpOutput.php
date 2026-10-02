<?php
declare(strict_types=1);

namespace Serapha\Routing;

final class NativeHttpOutput implements HttpOutput
{
    public function headersSent(): bool
    {
        return headers_sent();
    }

    public function setStatusCode(int $statusCode): void
    {
        http_response_code($statusCode);
    }

    public function sendHeader(string $header, bool $replace): void
    {
        header($header, $replace);
    }

    public function write(string $body): void
    {
        echo $body;
    }
}
