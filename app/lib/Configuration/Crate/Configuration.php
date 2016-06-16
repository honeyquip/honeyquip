<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\FrameworkBinding\Equip\Crate\CrateInterface;

abstract class Configuration implements ConfigurationInterface
{
    protected $crate;

    protected $builder;

    public function __construct(CrateInterface $crate)
    {
        $this->crate = $crate;
    }

    public function extend()
    {
        if ($this->builder) {
            $this->builder->rewind();
        } else {
            $this->builder = new ConfigBuilder;
        }

        return $this->builder;
    }

    public static function forCrate(CrateInterface $crate)
    {
        $configClass = static::class;
        return new $configClass($crate);
    }
}
