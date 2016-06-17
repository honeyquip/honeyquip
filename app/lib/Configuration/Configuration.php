<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Closure;
use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;

abstract class Configuration implements ConfigurationInterface
{
    protected $builder;

    public function __construct(ConfigBuilderInterface $builder = null)
    {
        $this->builder = $builder ?: new ConfigBuilder;
        $this->builder->rewind();
    }

    public function extend()
    {
        $this->builder->rewind();

        return $this->builder;
    }

    public static function forCrate(CrateInterface $crate)
    {
        $configClass = static::class;
        return new $configClass($crate);
    }

    public static function customize()
    {
        $args = func_get_args();
        if (count($args) !== 1) {
            throw new RuntimeError('Unexpected number of arguments given. Expecting one: callback');
        }
        $builderCallback = $args[0];
        $configurationClass = static::class;

        return new $configurationClass($builderCallback(new ConfigBuilder));
    }
}
