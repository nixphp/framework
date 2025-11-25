<?php

declare(strict_types=1);

namespace NixPHP\Support;

class Plugin
{
    protected string $name;
    protected string $path;
    protected array $configPaths = [];
    protected array $viewPaths   = [];
    protected array $routeFiles  = [];
    protected array $functionsFiles   = [];
    protected array $viewHelpersFiles = [];
    protected ?string $bootstrap = null;
    private bool $booted = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addConfigPath(string $path): void
    {
        $this->configPaths[] = $path;
    }

    public function addViewPath(string $path): void
    {
        $this->viewPaths[] = $path;
    }

    public function addRouteFile(string $path): void
    {
        $this->routeFiles[] = $path;
    }

    public function addFunctionFile(string $path): void
    {
        $this->functionsFiles[] = $path;
    }

    public function addViewHelperFile(string $path): void
    {
        $this->viewHelpersFiles[] = $path;
    }

    public function setBootstrapFile(string $path): void
    {
        $this->bootstrap = $path;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->configPaths as $configPath) {
            if (!file_exists($configPath)) continue;
            require_once $configPath;
        }

        foreach ($this->routeFiles as $routeFile) {
            if (!file_exists($routeFile)) continue;
            require_once $routeFile;
        }

        foreach ($this->functionsFiles as $functionFile) {
            if (!file_exists($functionFile)) continue;
            require_once $functionFile;
        }

        foreach ($this->viewHelpersFiles as $viewHelperFile) {
            if (!file_exists($viewHelperFile)) continue;
            require_once $viewHelperFile;
        }

        if (file_exists($this->bootstrap)) {
            require_once $this->bootstrap;
        }

        $this->booted = true;
    }

    public function getConfigPaths(): array
    {
        return $this->configPaths;
    }

    public function getViewPaths(): array
    {
        return $this->viewPaths;
    }

    public function getRouteFiles(): array
    {
        return $this->routeFiles;
    }

    public function getFunctionsFiles(): array
    {
        return $this->functionsFiles;
    }

    public function getViewHelpersFiles(): array
    {
        return $this->viewHelpersFiles;
    }
}
