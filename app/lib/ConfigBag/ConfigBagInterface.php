<?php

namespace Honeybee\FrameworkBinding\Equip\ConfigBag;

interface ConfigBagInterface
{
    public function withConfig(array $config);

    public function get($setting, $default = null);
}