<?php

namespace PHPico\Http;

use PHPico\Support\Collection;

class Response
{
    protected string $body = '';
    protected int $status = 200;
    protected Collection $headers;

    public function __construct(mixed $content = '')
    {
        $this->headers = new Collection();
        $this->setContent($content);
    }

    public function setContent(mixed $content): static
    {
        if (is_array($content) || is_object($content)) {
            $this->body = json_encode($content, JSON_PRETTY_PRINT);
            $this->headers->add('Content-Type', 'application/json');
        } else {
            $this->body = (string) $content;
            $this->headers->add('Content-Type', 'text/html');
        }

        $this->headers->add('Content-Length', strlen($this->body));

        return $this;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function headers(): Collection
    {
        return $this->headers;
    }

    public function send(): void
    {
        if (ob_get_length() > 0) ob_end_clean();
        http_response_code($this->status);

        foreach ($this->headers->all() as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header("$key: $v", false);
                }
            } else {
                header("$key: $value");
            }
        }

        echo $this->body;
    }

    public function __toString(): string
    {
        return $this->body;
    }
}
