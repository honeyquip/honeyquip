<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;

class CommandBusConfiguration extends Configuration
{
    protected static $defaultConfig = [
        'transports' => [],
        'subscriptions' => []
    ];

    public function apply(Injector $injector)
    {
        $injector->execute(function (array $commandBusConfig = []) use ($injector) {
            $commandBusConfig = ($this->builder) ? $this->builder->build($commandBusConfig) : $commandBusConfig;
            $injector->defineParam('commandBusConfig', $commandBusConfig);
        });
    }
}
