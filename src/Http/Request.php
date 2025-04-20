<?php

namespace PHPico\Http;

use PHPico\Support\Collection;

class Request
{

    private string $method;
    private string $uri;
    private Collection $query;
    private Collection $body;
    private Collection $cookies;
    private Collection $files;
    private Collection $headers;

    public function __construct()
    {
        $this->method  = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri     = $_SERVER['REQUEST_URI'] ?? '/';
        $this->query   = new Collection($_GET);
        $this->body    = new Collection($_POST);
        $this->cookies = new Collection($_COOKIE);
        $this->files   = new Collection($_FILES);
        $this->headers = new Collection($this->parseHeaders());
    }

    protected function parseHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return parse_url($this->uri, PHP_URL_PATH) ?? '/';
    }

    public function isAjax(): bool
    {
        return strtolower($this->headers->get('X-Requested-With', '')) === 'xmlhttprequest';
    }

    public function query(): Collection
    {
        return $this->query;
    }

    public function body(): Collection
    {
        return $this->body;
    }

    public function cookies(): Collection
    {
        return $this->cookies;
    }

    public function files(): Collection
    {
        return $this->files;
    }

    public function headers(): Collection
    {
        return $this->headers;
    }

}
