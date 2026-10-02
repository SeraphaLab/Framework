<?php
declare(strict_types=1);

namespace Serapha\Routing;

final class ResponseEmitter
{
    private HttpOutput $output;

    public function __construct(?HttpOutput $output = null)
    {
        $this->output = $output ?? new NativeHttpOutput();
    }

    public function emit(Response $response): void
    {
        if ($this->output->headersSent()) {
            throw new \RuntimeException('Cannot emit response: headers have already been sent.');
        }

        $this->output->setStatusCode($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $this->output->sendHeader($name . ': ' . $value, false);
            }
        }

        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        $this->output->write((string) $body);
    }
}
