<?php
declare(strict_types=1);

namespace Serapha\Routing;

interface HttpOutput
{
    public function headersSent(): bool;

    public function setStatusCode(int $statusCode): void;

    public function sendHeader(string $header, bool $replace): void;

    public function write(string $body): void;
}
