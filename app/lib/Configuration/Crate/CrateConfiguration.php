<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\FrameworkBinding\Equip\Configuration\ConfigBuilderInterface;
use Honeybee\FrameworkBinding\Equip\Configuration\Configuration;
use Honeybee\FrameworkBinding\Equip\Crate\CrateInterface;

abstract class CrateConfiguration extends Configuration
{
    protected $crate;

    public function __construct(CrateInterface $crate, ConfigBuilderInterface $builder = null)
    {
        parent::__construct($builder);

        $this->crate = $crate;
    }

    public static function customize()
    {
        $args = func_get_args();
        if (count($args) !== 2) {
            throw new RuntimeError('Unexpected number of arguments given. Expecting two: crate and callback');
        }
        $crate = $args[0];
        $builderCallback = $args[1];
        $configurationClass = static::class;

        return new $configurationClass($crate, $builderCallback(new ConfigBuilder));
    }
}
