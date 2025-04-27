<?php

namespace PHPico\Core;

use function PHPico\abort;
use function PHPico\app;
use function PHPico\guard;
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
        extract($this->variables);
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
        if (!isset($this->variables[$name])) {
            ob_end_clean();
            throw new \Exception("Variable $name does not exist");
        }
        $this->variables[$name] = ob_get_clean();
    }

    public function renderBlock(string $name, string $default = ''): string
    {
        return $this->variables[$name] ?? $default;
    }

    private function buildTemplatePath(string $templateName): string
    {
        $templateName = guard()->safePath($templateName);

        $paths = [
            app()->getBasePath() . '/app/views', // App views
            ...array_filter(array_map('realpath', plugin()->getMeta('viewPaths'))), // Plugin views
            __DIR__ . '/../Resources/views', // Framework views
        ];

        foreach ($paths as $path) {
            $fullPath = rtrim($path, '/') . '/' . str_replace('.', '/', $templateName) . '.phtml';
            if (is_file($fullPath)) return $fullPath;
        }

        throw new \RuntimeException("View [$templateName] not found in any known paths.");
    }

}