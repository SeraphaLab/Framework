<?php
declare(strict_types=1);

namespace Serapha\Tests\Support;

use Serapha\Routing\HttpOutput;

final class FakeHttpOutput implements HttpOutput
{
    public ?int $statusCode = null;
    public array $headers = [];
    public string $body = '';
    public bool $headersAlreadySent = false;

    public function headersSent(): bool
    {
        return $this->headersAlreadySent;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function sendHeader(string $header, bool $replace): void
    {
        $this->headers[] = [$header, $replace];
    }

    public function write(string $body): void
    {
        $this->body .= $body;
    }
}
