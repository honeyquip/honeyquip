<?php

namespace Honeybee\FrameworkBinding\Equip\ConfigBag;

use Honeybee\Infrastructure\Config\Settings;

class ConfigBag implements ConfigBagInterface
{
    public function __construct(array $config = [])
    {
        $this->config = new Settings($config);
    }

    public function withConfig(array $config)
    {
        return new static($config);
    }

    public function get($setting, $default = null)
    {
        $pathParts = explode('.', $setting);
        $value = $this->config;
        do {
            $key = array_shift($pathParts);
            $value = $value->get($key);
        } while ($value && !empty($pathParts));

        return ($value instanceof Settings) ? (array)$value : ($value ?: $default);
    }

    protected function resolveValue($setting)
    {
        $pathParts = explode('.', $setting);
        $value = $this->config;
        do {
            $key = array_shift($pathParts);
            $value = $value->resolveValue($key);
        } while ($value instanceof Settings && !empty($pathParts));

        if (!empty($pathParts)) {
            return null;
        }

        return $value;
    }
}
