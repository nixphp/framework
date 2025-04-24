<?php

namespace PHPico\Core;

use function PHPico\app;
use function PHPico\asset;
use function PHPico\plugin;

class View
{
    private View|null $layout = null;
    private array $variables = [];
    private string|null $template = null;

    public function setLayout(string $template): View
    {
        $this->layout = new View();
        $this->layout->setTemplate($template);
        return $this;
    }

    public function setTemplate(string $template): View
    {
        $template = $this->buildTemplatePath($template);
        $this->template = $template;
        return $this;
    }

    public function setVariable($key, $value): View
    {
        $this->variables[$key] = $value;
        return $this;
    }

    public function setVariables(array $variables): View
    {
        $this->variables = $variables;
        return $this;
    }

    public function render(): string
    {
        ob_start();
        extract($this->variables, EXTR_OVERWRITE);
        include $this->template;
        $content = ob_get_clean();

        if ($this->layout instanceof View) {
            return $this->layout->setVariables($this->variables)->render();
        }

        return $content;
    }

    public function block(string $name): void
    {
        $this->variables[$name] = 'initial';
        ob_start();
    }

    public function endblock(string $name): void
    {
        if ($this->variables[$name] !== 'initial') {
            throw new \Exception("Variable $name does not exist");
        }
        $this->variables[$name] = ob_get_clean();
    }

    public function renderBlock(string $name, string $default = ''): string
    {
        return $this->variables[$name] ?? $default;
    }

    private function getViewsRoot(): string
    {
        return app()->getBasePath() . '/app/views';
    }

    private function buildTemplatePath(string $templateName): string
    {
        $paths = [
            $this->getViewsRoot(),                  // App views
            ...plugin()->getMeta('viewPaths'), // Plugin views
            __DIR__ . '/../Resources/views',        // Framework views
        ];

        foreach ($paths as $path) {
            $fullPath = rtrim($path, '/') . '/' . str_replace('.', '/', $templateName) . '.phtml';
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        throw new \RuntimeException("View [$templateName] not found in any known paths.");
    }

}