<?php

namespace PHPico\Core;

use function PHPico\app;

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
        if (!file_exists($template)) {
            $template = $this->buildTemplatePath($template);
        }
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

    public function block(string $name)
    {
        $this->variables[$name] = 'initial';
        ob_start();
    }

    public function endblock(string $name)
    {
        if ($this->variables[$name] !== 'initial') {
            throw new \Exception("Variable $name does not exist");
        }
        $this->variables[$name] = ob_get_clean();
    }

    public function renderBlock(string $name, string $default = '')
    {
        return $this->variables[$name] ?? $default;
    }

    private function getViewsRoot()
    {
        return app()->getBasePath() . '/app/views';
    }

    private function buildTemplatePath(string $templateName)
    {
        return sprintf('%s/%s.phtml', $this->getViewsRoot(), $templateName);
    }

}