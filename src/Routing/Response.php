<?php
declare(strict_types=1);

namespace Serapha\Routing;

use HttpSoft\Message\Response as BaseResponse;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    private BaseResponse $response;

    public function __construct(int $status = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = '')
    {
        $this->response = new BaseResponse($status, $headers, $body, $version, $reason);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): self
    {
        return new self($code, [], null, '1.1', $reasonPhrase);
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        $newInstance = clone $this;
        $newInstance->response = $this->response->withProtocolVersion($version);

        return $newInstance;
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        $newInstance = clone $this;
        $newInstance->response = $this->response->withHeader($name, $value);

        return $newInstance;
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $newInstance = clone $this;
        $newInstance->response = $this->response->withAddedHeader($name, $value);

        return $newInstance;
    }

    public function withoutHeader(string $name): MessageInterface
    {
        $newInstance = clone $this;
        $newInstance->response = $this->response->withoutHeader($name);

        return $newInstance;
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $newInstance = clone $this;
        $newInstance->response = $this->response->withBody($body);

        return $newInstance;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): self
    {
        $newInstance = clone $this;
        $newInstance->response = $this->response->withStatus($code, $reasonPhrase);

        return $newInstance;
    }

    public function withRedirect(string $uri, int $statusCode = 302): self
    {
        if ($statusCode < 300 || $statusCode >= 400) {
            throw new \InvalidArgumentException('A redirect status code must be in the 3xx range.');
        }

        $newInstance = clone $this;
        $newInstance->response = $this->response
            ->withStatus($statusCode)
            ->withHeader('Location', $uri);

        return $newInstance;
    }
}
