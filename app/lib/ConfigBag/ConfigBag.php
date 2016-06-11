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
        } while (!empty($pathParts));

        return $value;
    }
}
