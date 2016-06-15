<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Honeybee\Projection\ProjectionTypeMap;

class ProjectionTypeMapConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector
            ->share(ProjectionTypeMap::class)
            ->prepare(ProjectionTypeMap::class, function (ProjectionTypeMap $map) use ($injector) {
                $injector->execute(function (array $projectionTypes = []) use ($map) {
                    foreach ($projectionTypes as $prefix => $projectionType) {
                        $map->setItem($prefix, $projectionType);
                    }
                });
            });
    }
}
