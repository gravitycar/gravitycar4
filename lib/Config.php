<?php

namespace Gravitycar\lib;

class Config
{
    private array $settings = [];
    private string $configFile;

    public function __construct(string $configFile = 'config.php')
    {
        $this->configFile = $configFile;
        $this->loadConfig();
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->settings[$name] ?? $default;
    }

    public function set(string $name, mixed $value): void
    {
        $this->settings[$name] = $value;
    }

    public function save(): bool
    {
        $configData = "<?php\n\nreturn " . var_export($this->settings, true) . ";\n";

        return file_put_contents($this->configFile, $configData) !== false;
    }

    private function loadConfig(): void
    {
        if (file_exists($this->configFile)) {
            $loadedSettings = include $this->configFile;
            if (is_array($loadedSettings)) {
                $this->settings = $loadedSettings;
            }
        }
    }

    public function getAll(): array
    {
        return $this->settings;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->settings);
    }
}