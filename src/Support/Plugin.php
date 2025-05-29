<?php
namespace NixPHP\Support;

class Plugin
{
    protected array $meta = [];

    public function addMeta(string $package, string $section, string $value): void
    {
        if (!isset($this->meta[$package])) {
            $this->meta[$package] = [];
        }

        if (!isset($this->meta[$package][$section])) {
            $this->meta[$package][$section] = [];
        }

        if (!in_array($value, $this->meta[$package][$section], true)) {
            $this->meta[$package][$section][] = $value;
        }
    }

    public function getMeta(string $package): array
    {
        return $this->meta[$package] ?? [];
    }

    public function getSection(string $section): array
    {
        $result = [];

        foreach ($this->meta as $package => $sections) {
            if (!empty($sections[$section])) {
                $result = array_merge($result, $sections[$section]);
            }
        }

        return $result;
    }

    public function all(): array
    {
        return $this->meta;
    }
}
