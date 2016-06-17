<?php

namespace Honeybee\FrameworkBinding\Equip\Configuration\Crate;

use Auryn\Injector;

class ResourceTypeConfiguration extends CrateConfiguration
{
    public function apply(Injector $injector)
    {
        $injector->execute(function (array $aggregateRootTypes = []) use ($injector) {
            foreach ($this->crate->getAggregateRootTypes() as $prefix => $aggregateRootType) {
                $injector->share($aggregateRootType);
                $aggregateRootTypes[$prefix] = $aggregateRootType;
            }

            $injector->defineParam('aggregateRootTypes', $aggregateRootTypes);
        });

        $injector->execute(function (array $projectionTypes = []) use ($injector) {
            foreach ($this->crate->getProjectionTypes() as $prefix => $projectionType) {
                $injector->share($projectionType);
                $projectionTypes[$prefix] = $projectionType;
            }

            $injector->defineParam('projectionTypes', $projectionTypes);
        });
    }
}
