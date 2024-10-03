<?php
declare(strict_types=1);

namespace Serapha\Routing;

use Serapha\Utils\Utils;
use HttpSoft\ServerRequest\ServerRequestCreator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class Request implements ServerRequestInterface
{
    private ServerRequestInterface $request;

    public function __construct()
    {
        $creator = new ServerRequestCreator();
        $this->request = $creator->createFromGlobals();
    }

    // Get query parameters handling rewrite or non-rewrite case
    public function getEffectiveQueryParams(): array
    {
        $queryParams = [];
        parse_str($this->request->getUri()->getQuery(), $queryParams);

        if (!Utils::isRewriteEnabled()) {
            // Remove first parameter which is part of the path in non-rewrite mode
            $firstKey = null;
            foreach ($queryParams as $key => $value) {
                $firstKey = $key;
                break;
            }

            if ($firstKey !== null && strpos($queryParams[$firstKey], '/') === 0) {
                unset($queryParams[$firstKey]);
            }
        }

        return $queryParams;
    }

    // Get the root URL for the request
    public function root(): string
    {
        return rtrim($this->request->getUri()->getScheme().'://'.$this->request->getUri()->getHost(), '/');
    }

    // Get the full URL with query string
    public function fullUrl(): string
    {
        $uri = $this->request->getUri();

        return (string) $uri;
    }

    // Get the URL without query string
    public function url(): string
    {
        $uri = $this->request->getUri();

        return $uri->getScheme().'://'.$uri->getHost().$uri->getPath();
    }

    // Check if the request is AJAX
    public function isAjax(): bool
    {
        return $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    // Check if the request is secure (HTTPS)
    public function isSecure(): bool
    {
        return $this->request->getUri()->getScheme() === 'https';
    }

    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): ServerRequestInterface
    {
        return $this->request->withProtocolVersion($version);
    }

    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->request->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->request->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->request->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): MessageInterface
    {
        return $this->request->withHeader($name, $value);
    }

    public function withAddedHeader(string $name, $value): MessageInterface
    {
        return $this->request->withAddedHeader($name, $value);
    }

    public function withoutHeader(string $name): MessageInterface
    {
        return $this->request->withoutHeader($name);
    }

    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->request->withBody($body);
    }

    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        return $this->request->withRequestTarget($requestTarget);
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    public function withMethod(string $method): RequestInterface
    {
        return $this->request->withMethod($method);
    }

    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): ServerRequestInterface
    {
        return $this->request->withUri($uri, $preserveHost);
    }

    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }

    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return $this->request->withCookieParams($cookies);
    }

    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        return $this->request->withQueryParams($query);
    }

    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return $this->request->withUploadedFiles($uploadedFiles);
    }

    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        return $this->request->withParsedBody($data);
    }

    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }

    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        return $this->request->withAttribute($name, $value);
    }

    public function withoutAttribute(string $name): ServerRequestInterface
    {
        return $this->request->withoutAttribute($name);
    }
}
