<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;

class CommandBusConfiguration extends CrateConfiguration
{
    protected static $defaultConfig = [
        'transports' => [],
        'subscriptions' => []
    ];

    public function apply(Injector $injector)
    {
        $injector->execute(function (array $commandBusConfig = []) use ($injector) {
            $commandBusConfig = $this->builder->build(array_merge_recursive(self::$defaultConfig, $commandBusConfig));
            $injector->defineParam('commandBusConfig', $commandBusConfig);
        });
    }
}
