<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;

class AggregateRootTypeMapConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector
            ->share(AggregateRootTypeMap::class)
            ->prepare(AggregateRootTypeMap::class, function (AggregateRootTypeMap $map) use ($injector) {
                $injector->execute(function (array $aggregateRootTypes = []) use ($map) {
                    foreach ($aggregateRootTypes as $prefix => $aggregateRootType) {
                        $map->setItem($prefix, $aggregateRootType);
                    }
                });
            });
    }
}
