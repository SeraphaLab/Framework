<?php
namespace Serapha\Routing;

use carry0987\Utils\Utils;

final class Response
{
    private int $status = 200;
    private string $body = '';

    /**
     * Set the HTTP status code.
     *
     * @param int $status
     * @return self
     */
    public function setStatus(int $status) : self
    {
        $this->status = $status;
        if (headers_sent()) {
            return $this;
        }
        http_response_code($this->status);

        return $this;
    }

    /**
     * Add a header to the response.
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addHeader(string $name, string $value): self
    {
        Utils::setHeader([$name => $value]);

        return $this;
    }

    /**
     * Set the body of the response.
     *
     * @param string $body
     * @return self
     */
    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Send the response.
     *
     * @return void
     */
    public function send(): void
    {
        echo $this->body;
    }

    /**
     * Redirect to a given URL considering the current directory of the site.
     *
     * @param string $url
     * @param int $status
     * @return void
     */
    public function redirect(string $url, ?int $status = null): void
    {
        // Construct the query parameter part
        $queryUrl = '?query=/' . $url;
        // Get the base directory of the script
        $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        // Construct the full URL with the query parameter
        $redirectUrl = Utils::trimPath($baseDir . '/' . $queryUrl);
        // Perform the redirection
        Utils::redirectURL($redirectUrl, $status ?? 303);
    }
}
